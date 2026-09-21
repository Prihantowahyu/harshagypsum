<?php
/**
 * Category Management Page
 * Harsha Gypsum Management Suite
 */

require_once __DIR__ . '/layout.php';

$pdo = getDbConnection();

$msg = '';
$error = '';

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $catId = intval($_GET['id'] ?? 0);
    if ($catId > 0) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_slug = (SELECT slug FROM categories WHERE id = ?)");
        $check->execute([$catId]);
        $prodCount = $check->fetchColumn();

        if ($prodCount > 0) {
            $error = "Kategori tidak dapat dihapus karena masih digunakan oleh {$prodCount} produk.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$catId]);
            $msg = 'Kategori berhasil dihapus.';
        }
    }
}

// Handle Add / Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fas fa-th-large');
    $description = trim($_POST['description'] ?? '');
    $displayOrder = intval($_POST['display_order'] ?? 0);

    if (empty($name)) {
        $error = 'Nama kategori wajib diisi!';
    } else {
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        }

        if ($id > 0) {
            // Update
            $stmt = $pdo->prepare("
                UPDATE categories 
                SET name = ?, slug = ?, icon = ?, description = ?, display_order = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $slug, $icon, $description, $displayOrder, $id]);
            $msg = 'Kategori berhasil diperbarui!';
        } else {
            // Check unique slug
            $check = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
            $check->execute([$slug]);
            if ($check->fetchColumn() > 0) {
                $error = 'Slug atau kode kategori ini sudah digunakan. Silakan gunakan slug lain.';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO categories (name, slug, icon, description, display_order)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $slug, $icon, $description, $displayOrder]);
                $msg = 'Kategori baru berhasil ditambahkan!';
            }
        }
    }
}

// Edit query
$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$editId]);
    $editCategory = $stmt->fetch();
}

// Fetch all categories with product count
$sql = "
    SELECT c.*, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON c.slug = p.category_slug AND p.is_active = 1
    GROUP BY c.id
    ORDER BY c.display_order ASC, c.id ASC
";
$categories = $pdo->query($sql)->fetchAll();

renderAdminHeader('Kelola Kategori Produk', 'categories');
?>

<?php if (!empty($msg)): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <span><?= htmlspecialchars($msg) ?></span>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <span><?= htmlspecialchars($error) ?></span>
  </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
  
  <!-- Form Tambah / Edit -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas <?= $editCategory ? 'fa-edit' : 'fa-folder-plus' ?>"></i>
        <?= $editCategory ? 'Edit Kategori' : 'Tambah Kategori Baru' ?>
      </h3>
    </div>

    <form method="POST" action="categories.php">
      <input type="hidden" name="id" value="<?= $editCategory ? $editCategory['id'] : 0 ?>">

      <div class="form-group">
        <label class="form-label">Nama Kategori <span class="req">*</span></label>
        <input type="text" name="name" class="form-control" placeholder="Contoh: Kubah & Profil Masjid" required value="<?= htmlspecialchars($editCategory['name'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label class="form-label">Slug / ID URL</label>
        <input type="text" name="slug" class="form-control" placeholder="Contoh: kubah-masjid" value="<?= htmlspecialchars($editCategory['slug'] ?? '') ?>">
        <div class="form-hint">Dikosongkan jika ingin dibuat otomatis dari nama</div>
      </div>

      <div class="form-group">
        <label class="form-label">Icon FontAwesome</label>
        <input type="text" name="icon" class="form-control" placeholder="fas fa-mosque" value="<?= htmlspecialchars($editCategory['icon'] ?? 'fas fa-th-large') ?>">
        <div class="form-hint">Contoh: fas fa-border-style, fas fa-crown, fas fa-tools</div>
      </div>

      <div class="form-group">
        <label class="form-label">Deskripsi Singkat</label>
        <textarea name="description" class="form-control" rows="3" placeholder="Keterangan singkat kategori..."><?= htmlspecialchars($editCategory['description'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">Urutan Tampil (Display Order)</label>
        <input type="number" name="display_order" class="form-control" value="<?= htmlspecialchars($editCategory['display_order'] ?? count($categories) + 1) ?>">
      </div>

      <div style="display: flex; gap: 10px; margin-top: 10px;">
        <button type="submit" class="btn btn-primary" style="flex: 1;">
          <i class="fas fa-save"></i> <?= $editCategory ? 'Simpan Perubahan' : 'Tambah Kategori' ?>
        </button>
        <?php if ($editCategory): ?>
          <a href="categories.php" class="btn btn-secondary">Batal</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- List Kategori -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-tags"></i> Daftar Kategori Tersedia</h3>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 50px;">Icon</th>
            <th>Nama Kategori & Slug</th>
            <th>Deskripsi</th>
            <th style="text-align: center;">Jumlah Produk</th>
            <th style="text-align: right; width: 120px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
            <tr>
              <td style="text-align: center;">
                <div style="width: 38px; height: 38px; background: rgba(212, 175, 55, 0.1); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: var(--gold-primary); font-size: 1.1rem;">
                  <i class="<?= htmlspecialchars($cat['icon']) ?>"></i>
                </div>
              </td>
              <td>
                <strong style="color: #fff; font-size: 0.95rem;"><?= htmlspecialchars($cat['name']) ?></strong>
                <div style="font-size: 0.75rem; font-family: monospace; color: var(--gold-light);">
                  <?= htmlspecialchars($cat['slug']) ?>
                </div>
              </td>
              <td>
                <div style="font-size: 0.8rem; color: var(--text-muted); max-width: 250px;">
                  <?= htmlspecialchars($cat['description'] ?: '-') ?>
                </div>
              </td>
              <td style="text-align: center;">
                <span class="badge badge-gold"><?= $cat['product_count'] ?> Produk</span>
              </td>
              <td style="text-align: right;">
                <div style="display: inline-flex; gap: 6px;">
                  <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn btn-secondary btn-sm" title="Edit Kategori">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="categories.php?action=delete&id=<?= $cat['id'] ?>" class="btn btn-danger btn-sm" title="Hapus Kategori" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori <?= addslashes($cat['name']) ?>?')">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php renderAdminFooter(); ?>
