<?php
/**
 * Add / Edit Product Form
 * Harsha Gypsum Management Suite
 */

require_once __DIR__ . '/layout.php';

$pdo = getDbConnection();

$id = intval($_GET['id'] ?? 0);
$isEdit = ($id > 0);
$pageTitle = $isEdit ? 'Edit Produk' : 'Tambah Produk Baru';

$error = '';
$success = '';

// Load existing data if editing
$product = [
    'id' => 0,
    'code' => '',
    'name' => '',
    'category_slug' => '',
    'width' => '',
    'width_number' => 0,
    'length' => '2.10 meter',
    'length_meter' => 2.1,
    'price' => '',
    'price_bulk' => '',
    'min_bulk_qty' => 50,
    'image' => 'assets/images/list-minimalis.jpg',
    'description' => '',
    'specs' => '',
    'is_best_seller' => 0,
    'is_popular' => 0,
    'is_active' => 1
];

if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) {
        $product = $found;
        // Format specs for textarea
        if (!empty($product['specs'])) {
            $decoded = json_decode($product['specs'], true);
            if (is_array($decoded)) {
                $lines = [];
                foreach ($decoded as $s) {
                    $lines[] = ($s['label'] ?? '') . ': ' . ($s['value'] ?? '');
                }
                $product['specs_text'] = implode("\n", $lines);
            }
        }
    } else {
        header("Location: products.php");
        exit;
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $category_slug = trim($_POST['category_slug'] ?? '');
    $width = trim($_POST['width'] ?? '');
    $width_number = floatval($_POST['width_number'] ?? 0);
    $length = trim($_POST['length'] ?? '');
    $length_meter = floatval($_POST['length_meter'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $price_bulk = floatval($_POST['price_bulk'] ?? 0);
    $min_bulk_qty = intval($_POST['min_bulk_qty'] ?? 10);
    $description = trim($_POST['description'] ?? '');
    $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $current_image = trim($_POST['current_image'] ?? 'assets/images/list-minimalis.jpg');

    // Parse specs lines
    $specsText = trim($_POST['specs_text'] ?? '');
    $specsArray = [];
    if (!empty($specsText)) {
        foreach (explode("\n", $specsText) as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $specsArray[] = [
                        'label' => trim($parts[0]),
                        'value' => trim($parts[1])
                    ];
                } else {
                    $specsArray[] = [
                        'label' => 'Spesifikasi',
                        'value' => $line
                    ];
                }
            }
        }
    }
    $specsJson = json_encode($specsArray, JSON_UNESCAPED_UNICODE);

    // Validation
    if (empty($name)) {
        $error = 'Nama produk wajib diisi!';
    } elseif (empty($category_slug)) {
        $error = 'Kategori produk wajib dipilih!';
    } elseif ($price <= 0) {
        $error = 'Harga eceran harus lebih besar dari 0!';
    } else {
        // Auto-code if empty
        if (empty($code)) {
            $prefix = strtoupper(substr($category_slug, 0, 2));
            $code = 'HG-' . $prefix . rand(100, 999);
        }

        // Handle Image Upload
        $finalImage = $current_image;
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['product_image'];
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (isset($allowed[$mime])) {
                $ext = $allowed[$mime];
                $uploadDir = __DIR__ . '/../assets/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $cleanName = strtolower(preg_replace('/[^A-Za-z0-9-_]/', '-', pathinfo($file['name'], PATHINFO_FILENAME)));
                $fileName = 'prod_' . date('Ymd_His') . '_' . substr($cleanName, 0, 20) . '.' . $ext;
                $target = $uploadDir . $fileName;

                if (move_uploaded_file($file['tmp_name'], $target)) {
                    $finalImage = 'assets/uploads/' . $fileName;
                } else {
                    $error = 'Gagal menyimpan file gambar yang diunggah.';
                }
            } else {
                $error = 'Format file gambar harus JPG, PNG, atau WebP.';
            }
        }

        if (empty($error)) {
            if ($isEdit) {
                // Update
                $stmt = $pdo->prepare("
                    UPDATE products SET 
                        code = ?, name = ?, category_slug = ?, width = ?, width_number = ?,
                        length = ?, length_meter = ?, price = ?, price_bulk = ?, min_bulk_qty = ?,
                        image = ?, description = ?, specs = ?, is_best_seller = ?, is_popular = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $code, $name, $category_slug, $width, $width_number,
                    $length, $length_meter, $price, $price_bulk, $min_bulk_qty,
                    $finalImage, $description, $specsJson, $is_best_seller, $is_popular, $is_active,
                    $id
                ]);
            } else {
                // Insert
                $stmt = $pdo->prepare("
                    INSERT INTO products (
                        code, name, category_slug, width, width_number,
                        length, length_meter, price, price_bulk, min_bulk_qty,
                        image, description, specs, is_best_seller, is_popular, is_active, rating, sold
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 5.0, 0)
                ");
                $stmt->execute([
                    $code, $name, $category_slug, $width, $width_number,
                    $length, $length_meter, $price, $price_bulk, $min_bulk_qty,
                    $finalImage, $description, $specsJson, $is_best_seller, $is_popular, $is_active
                ]);
            }

            header("Location: products.php?msg=saved");
            exit;
        }
    }
}

// Fetch categories for select
$categories = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC")->fetchAll();

renderAdminHeader($pageTitle, $isEdit ? 'products' : 'product-new');
?>

<div style="max-width: 900px; margin: 0 auto;">
  
  <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
    <a href="products.php" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> Kembali ke Daftar Produk
    </a>
    <span style="color: var(--text-muted); font-size: 0.85rem;">
      <?= $isEdit ? 'ID Produk: #' . $id : 'Menambahkan item baru' ?>
    </span>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger">
      <i class="fas fa-exclamation-circle"></i>
      <span><?= htmlspecialchars($error) ?></span>
    </div>
  <?php endif; ?>

  <form method="POST" action="" enctype="multipart/form-data" class="card">
    <input type="hidden" name="current_image" id="current_image" value="<?= htmlspecialchars($product['image']) ?>">

    <div class="card-header">
      <h3 class="card-title">
        <i class="fas <?= $isEdit ? 'fa-edit' : 'fa-plus-circle' ?>"></i> <?= $pageTitle ?>
      </h3>
    </div>

    <!-- Informasi Dasar -->
    <div class="form-row">
      <div class="form-group" style="flex: 2;">
        <label class="form-label">Nama Produk / Motif <span class="req">*</span></label>
        <input type="text" name="name" class="form-control" placeholder="Contoh: List Profil Minimalis Step 10cm" required value="<?= htmlspecialchars($product['name']) ?>">
      </div>

      <div class="form-group" style="flex: 1;">
        <label class="form-label">Kode Produk / Profil</label>
        <input type="text" name="code" class="form-control" placeholder="Contoh: HG-101" value="<?= htmlspecialchars($product['code']) ?>">
        <div class="form-hint">Otomatis jika dikosongkan</div>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Kategori <span class="req">*</span></label>
        <select name="category_slug" class="form-control" required>
          <option value="">-- Pilih Kategori --</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= $product['category_slug'] === $cat['slug'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Lebar / Dimensi</label>
        <input type="text" name="width" class="form-control" placeholder="Contoh: 10 cm atau 3x3 cm" value="<?= htmlspecialchars($product['width']) ?>">
      </div>

      <div class="form-group">
        <label class="form-label">Panjang Batang</label>
        <input type="text" name="length" class="form-control" placeholder="Contoh: 2.10 meter" value="<?= htmlspecialchars($product['length']) ?>">
      </div>
    </div>

    <!-- Harga & Grosir -->
    <div style="background: rgba(212, 175, 55, 0.04); border: 1px dashed rgba(212, 175, 55, 0.2); border-radius: var(--radius-md); padding: 18px; margin-bottom: 20px;">
      <h4 style="font-size: 0.95rem; color: var(--gold-light); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-tag"></i> Penentuan Harga (Ecer & Grosir Proyek)
      </h4>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Harga Eceran (Rp/Batang) <span class="req">*</span></label>
          <input type="number" name="price" class="form-control" placeholder="17500" required value="<?= htmlspecialchars($product['price']) ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Harga Grosir (Rp/Batang)</label>
          <input type="number" name="price_bulk" class="form-control" placeholder="14500" value="<?= htmlspecialchars($product['price_bulk']) ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Minimal Pembelian Grosir (Batang)</label>
          <input type="number" name="min_bulk_qty" class="form-control" placeholder="50" value="<?= htmlspecialchars($product['min_bulk_qty']) ?>">
        </div>
      </div>
    </div>

    <!-- Upload Gambar Produk -->
    <div class="form-group">
      <label class="form-label"><i class="fas fa-image"></i> Foto Produk Gypsum</label>
      
      <div class="upload-dropzone" id="dropzone">
        <input type="file" name="product_image" id="imageInput" accept="image/jpeg,image/png,image/webp">
        <i class="fas fa-cloud-arrow-up upload-icon"></i>
        <div style="font-weight: 600; font-size: 1rem; color: #fff; margin-bottom: 4px;">
          Pilih file foto atau seret foto ke sini
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">
          Format didukung: JPG, PNG, WebP (Maks. 5 MB)
        </div>
      </div>

      <!-- Preview Image -->
      <div class="preview-container" id="previewContainer">
        <img src="../<?= htmlspecialchars($product['image']) ?>" alt="Preview" class="preview-img" id="previewImg" onerror="this.src='../assets/images/list-minimalis.jpg'">
        <div style="flex: 1;">
          <div style="font-weight: 600; font-size: 0.85rem; color: #fff;">File Gambar Saat Ini:</div>
          <div style="font-size: 0.78rem; font-family: monospace; color: var(--gold-light);" id="imagePathLabel">
            <?= htmlspecialchars($product['image']) ?>
          </div>
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
            Jika Anda mengunggah file baru di atas, gambar akan otomatis diganti.
          </div>
        </div>
      </div>
    </div>

    <!-- Deskripsi -->
    <div class="form-group">
      <label class="form-label">Deskripsi Produk</label>
      <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan keunggulan motif, kecocokan ruangan, dan kerapihan cetakan..."><?= htmlspecialchars($product['description']) ?></textarea>
    </div>

    <!-- Spesifikasi Detail -->
    <div class="form-group">
      <label class="form-label">
        Spesifikasi Teknis (Satu per baris dengan format: <code>Label: Nilai</code>)
      </label>
      <textarea name="specs_text" class="form-control" rows="5" placeholder="Bahan: Tepung Casting A-Grade + Serat Roving
Panjang Efektif: 2.10 Meter / Batang
Lebar Muka: 10 cm
Ketebalan: 14 mm
Finishing: Halus Bebas Pori Siap Cat"><?= htmlspecialchars($product['specs_text'] ?? "Bahan: Casting Gypsum High Density + Roving\nPanjang Efektif: 2.10 Meter\nFinishing: Halus Siap Cat") ?></textarea>
      <div class="form-hint">Pisahkan antara label dan nilai dengan tanda titik dua (:). Contoh: <code>Ketebalan: 14 mm</code></div>
    </div>

    <!-- Pilihan Status -->
    <div style="display: flex; gap: 30px; margin-top: 10px; margin-bottom: 24px; padding-top: 16px; border-top: 1px solid var(--border-color-light);">
      <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
        <input type="checkbox" name="is_best_seller" value="1" <?= !empty($product['is_best_seller']) ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--gold-primary);">
        <span style="font-size: 0.9rem; font-weight: 600; color: #fff;">
          <i class="fas fa-star" style="color: var(--gold-primary);"></i> Jadikan Produk Best Seller (Terlaris)
        </span>
      </label>

      <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
        <input type="checkbox" name="is_active" value="1" <?= !empty($product['is_active']) ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--color-success);">
        <span style="font-size: 0.9rem; font-weight: 600; color: #fff;">
          <i class="fas fa-check-circle" style="color: var(--color-success);"></i> Tampilkan di Katalog Toko (Aktif)
        </span>
      </label>
    </div>

    <!-- Submit Button -->
    <div style="display: flex; gap: 12px;">
      <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
        <i class="fas fa-save"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Terbitkan Produk Baru' ?>
      </button>
      <a href="products.php" class="btn btn-secondary">Batal</a>
    </div>

  </form>
</div>

<script>
  // Live Image Preview on File Selection
  const imageInput = document.getElementById('imageInput');
  const previewImg = document.getElementById('previewImg');
  const imagePathLabel = document.getElementById('imagePathLabel');

  if (imageInput) {
    imageInput.addEventListener('change', function(e) {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
          previewImg.src = event.target.result;
          imagePathLabel.textContent = file.name + " (" + (file.size / 1024).toFixed(1) + " KB) - Siap disimpan";
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // Drag and drop feedback
  const dropzone = document.getElementById('dropzone');
  if (dropzone) {
    ['dragenter', 'dragover'].forEach(eventName => {
      dropzone.addEventListener(eventName, () => dropzone.classList.add('dragover'), false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
      dropzone.addEventListener(eventName, () => dropzone.classList.remove('dragover'), false);
    });
  }
</script>

<?php renderAdminFooter(); ?>
