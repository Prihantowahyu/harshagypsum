<?php
/**
 * Admin Products Management List
 * Harsha Gypsum Management Suite
 */

require_once __DIR__ . '/layout.php';

$pdo = getDbConnection();

// Handle Quick Actions (Delete / Toggle)
$message = '';
$messageType = 'success';

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $prodId = intval($_GET['id'] ?? 0);

    if ($action === 'delete' && $prodId > 0) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$prodId]);
        header("Location: products.php?msg=deleted");
        exit;
    }

    if ($action === 'toggle_bestseller' && $prodId > 0) {
        $stmt = $pdo->prepare("UPDATE products SET is_best_seller = CASE WHEN is_best_seller = 1 THEN 0 ELSE 1 END WHERE id = ?");
        $stmt->execute([$prodId]);
        header("Location: products.php?msg=updated");
        exit;
    }

    if ($action === 'toggle_active' && $prodId > 0) {
        $stmt = $pdo->prepare("UPDATE products SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?");
        $stmt->execute([$prodId]);
        header("Location: products.php?msg=updated");
        exit;
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') $message = 'Produk berhasil dihapus!';
    if ($_GET['msg'] === 'saved') $message = 'Produk berhasil disimpan!';
    if ($_GET['msg'] === 'updated') $message = 'Status produk berhasil diperbarui!';
}

// Filters
$categoryFilter = $_GET['category'] ?? '';
$searchQuery = trim($_GET['search'] ?? '');

$where = ["1=1"];
$params = [];

if (!empty($categoryFilter)) {
    $where[] = "p.category_slug = ?";
    $params[] = $categoryFilter;
}

if (!empty($searchQuery)) {
    $where[] = "(p.name LIKE ? OR p.code LIKE ? OR p.description LIKE ?)";
    $wild = "%" . $searchQuery . "%";
    $params[] = $wild;
    $params[] = $wild;
    $params[] = $wild;
}

$whereSql = implode(" AND ", $where);
$sql = "
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_slug = c.slug 
    WHERE {$whereSql} 
    ORDER BY p.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch categories for filter dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC")->fetchAll();

renderAdminHeader('Manajemen Produk', 'products');
?>

<?php if (!empty($message)): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <span><?= htmlspecialchars($message) ?></span>
  </div>
<?php endif; ?>

<div class="card">
  <!-- Filter & Search Toolbar -->
  <div style="display: flex; flex-wrap: wrap; gap: 16px; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <form method="GET" action="products.php" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; flex: 1;">
      <div style="min-width: 240px; flex: 1;">
        <input type="text" name="search" class="form-control" placeholder="Cari nama, kode (misal GP-101)..." value="<?= htmlspecialchars($searchQuery) ?>">
      </div>
      
      <div style="width: 220px;">
        <select name="category" class="form-control" onchange="this.form.submit()">
          <option value="">Semua Kategori (<?= count($products) ?>)</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= $categoryFilter === $cat['slug'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" class="btn btn-secondary">
        <i class="fas fa-search"></i> Filter
      </button>

      <?php if (!empty($searchQuery) || !empty($categoryFilter)): ?>
        <a href="products.php" class="btn btn-secondary" title="Reset Filter">
          <i class="fas fa-times"></i> Reset
        </a>
      <?php endif; ?>
    </form>

    <div>
      <a href="product-edit.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Produk Baru
      </a>
    </div>
  </div>

  <!-- Table List -->
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th style="width: 70px;">Gambar</th>
          <th>Kode & Nama Produk</th>
          <th>Kategori</th>
          <th>Dimensi</th>
          <th>Harga Ecer / Grosir</th>
          <th style="text-align: center;">Best Seller</th>
          <th style="text-align: center;">Status</th>
          <th style="text-align: right; width: 140px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($products)): ?>
          <tr>
            <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px;">
              <i class="fas fa-box-open" style="font-size: 2.5rem; margin-bottom: 12px; display: block; color: var(--text-dim);"></i>
              Tidak ada produk yang cocok dengan pencarian atau filter Anda.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($products as $p): ?>
            <tr>
              <td>
                <img src="../<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="table-img" onerror="this.src='../assets/images/list-minimalis.jpg'">
              </td>
              <td>
                <div style="font-weight: 700; color: #fff; font-size: 0.95rem; margin-bottom: 2px;">
                  <?= htmlspecialchars($p['name']) ?>
                </div>
                <div style="font-size: 0.78rem; font-family: monospace; color: var(--gold-light);">
                  <?= htmlspecialchars($p['code']) ?>
                </div>
              </td>
              <td>
                <span class="badge badge-cat"><?= htmlspecialchars($p['category_name'] ?? $p['category_slug']) ?></span>
              </td>
              <td>
                <div style="font-size: 0.85rem; color: var(--text-main);">
                  L: <?= htmlspecialchars($p['width']) ?: '-' ?>
                </div>
                <div style="font-size: 0.78rem; color: var(--text-muted);">
                  P: <?= htmlspecialchars($p['length']) ?: '-' ?>
                </div>
              </td>
              <td>
                <div style="color: var(--gold-primary); font-weight: 700;">
                  Rp <?= number_format($p['price'], 0, ',', '.') ?>
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">
                  Grosir: Rp <?= number_format($p['price_bulk'], 0, ',', '.') ?> (min <?= $p['min_bulk_qty'] ?>)
                </div>
              </td>
              <td style="text-align: center;">
                <a href="products.php?action=toggle_bestseller&id=<?= $p['id'] ?>" title="Klik untuk ubah status Best Seller">
                  <?php if ($p['is_best_seller']): ?>
                    <span class="badge badge-gold"><i class="fas fa-star"></i> Best Seller</span>
                  <?php else: ?>
                    <span class="badge badge-muted"><i class="far fa-star"></i> Standar</span>
                  <?php endif; ?>
                </a>
              </td>
              <td style="text-align: center;">
                <a href="products.php?action=toggle_active&id=<?= $p['id'] ?>" title="Klik untuk sembunyikan/tampilkan produk">
                  <?php if ($p['is_active']): ?>
                    <span class="badge badge-success"><i class="fas fa-check"></i> Aktif</span>
                  <?php else: ?>
                    <span class="badge badge-muted" style="color: var(--color-danger);"><i class="fas fa-eye-slash"></i> Nonaktif</span>
                  <?php endif; ?>
                </a>
              </td>
              <td style="text-align: right;">
                <div style="display: inline-flex; gap: 8px;">
                  <a href="product-edit.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm" title="Edit Produk">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="products.php?action=delete&id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" title="Hapus Produk" onclick="return confirm('Apakah Anda yakin ingin menghapus produk <?= addslashes($p['name']) ?>?')">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php renderAdminFooter(); ?>
