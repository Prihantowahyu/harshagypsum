<?php
/**
 * Admin Dashboard
 * Harsha Gypsum Management Suite
 */

require_once __DIR__ . '/layout.php';

$pdo = getDbConnection();

// Fetch statistics
$totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$totalCategories = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalBestSellers = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_best_seller = 1 AND is_active = 1")->fetchColumn();
$totalSold = (int)$pdo->query("SELECT SUM(sold) FROM products")->fetchColumn();

// Fetch recent 6 products
$recentStmt = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_slug = c.slug 
    ORDER BY p.id DESC 
    LIMIT 6
");
$recentProducts = $recentStmt->fetchAll();

// Fetch categories with counts
$catStmt = $pdo->query("
    SELECT c.name, COUNT(p.id) as count 
    FROM categories c 
    LEFT JOIN products p ON c.slug = p.category_slug 
    GROUP BY c.id 
    ORDER BY count DESC
");
$catStats = $catStmt->fetchAll();

renderAdminHeader('Dashboard Ringkasan', 'dashboard');
?>

<!-- Statistics Grid -->
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-icon gold">
      <i class="fas fa-boxes-stacked"></i>
    </div>
    <div>
      <div class="stat-number"><?= $totalProducts ?></div>
      <div class="stat-label">Total Produk Aktif</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon blue">
      <i class="fas fa-layer-group"></i>
    </div>
    <div>
      <div class="stat-number"><?= $totalCategories ?></div>
      <div class="stat-label">Kategori Tersedia</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon purple">
      <i class="fas fa-award"></i>
    </div>
    <div>
      <div class="stat-number"><?= $totalBestSellers ?></div>
      <div class="stat-label">Produk Terlaris (Best Seller)</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon green">
      <i class="fas fa-chart-pie"></i>
    </div>
    <div>
      <div class="stat-number"><?= number_format($totalSold, 0, ',', '.') ?></div>
      <div class="stat-label">Total Batang Terjual</div>
    </div>
  </div>
</div>

<!-- Layout: 2 Columns for Recent Products & Quick Actions -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
  
  <!-- Left: Recent Products -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-clock"></i> Produk Terbaru Ditambahkan</h3>
      <a href="products.php" class="btn btn-secondary btn-sm">Lihat Semua</a>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Gambar</th>
            <th>Nama & Kode</th>
            <th>Kategori</th>
            <th>Harga Ecer</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentProducts)): ?>
            <tr>
              <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">
                Belum ada produk tersimpan di database.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($recentProducts as $prod): ?>
              <tr>
                <td>
                  <img src="../<?= htmlspecialchars($prod['image']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" class="table-img" onerror="this.src='../assets/images/list-minimalis.jpg'">
                </td>
                <td>
                  <strong style="color: #fff; font-size: 0.92rem;"><?= htmlspecialchars($prod['name']) ?></strong>
                  <div style="font-size: 0.75rem; color: var(--gold-light); font-family: monospace;"><?= htmlspecialchars($prod['code']) ?></div>
                </td>
                <td>
                  <span class="badge badge-cat"><?= htmlspecialchars($prod['category_name'] ?? $prod['category_slug']) ?></span>
                </td>
                <td>
                  <span style="color: var(--gold-primary); font-weight: 700;">Rp <?= number_format($prod['price'], 0, ',', '.') ?></span>
                  <div style="font-size: 0.72rem; color: var(--text-muted);">Grosir: Rp <?= number_format($prod['price_bulk'], 0, ',', '.') ?></div>
                </td>
                <td>
                  <a href="product-edit.php?id=<?= $prod['id'] ?>" class="btn btn-secondary btn-sm" title="Edit Produk">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Right: Quick Actions & Category breakdown -->
  <div style="display: flex; flex-direction: column; gap: 24px;">
    
    <!-- Quick Actions Card -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-bolt"></i> Aksi Cepat</h3>
      </div>
      <div style="display: flex; flex-direction: column; gap: 12px;">
        <a href="product-edit.php" class="btn btn-primary" style="width: 100%; justify-content: flex-start;">
          <i class="fas fa-plus-circle"></i> Tambah Produk Baru
        </a>
        <a href="categories.php" class="btn btn-secondary" style="width: 100%; justify-content: flex-start;">
          <i class="fas fa-folder-plus"></i> Kelola Kategori
        </a>
        <a href="../index.html" target="_blank" class="btn btn-secondary" style="width: 100%; justify-content: flex-start;">
          <i class="fas fa-store"></i> Tinjau Tampilan Toko
        </a>
      </div>
    </div>

    <!-- Category Breakdown Card -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-pie"></i> Jumlah per Kategori</h3>
      </div>
      <div style="display: flex; flex-direction: column; gap: 12px;">
        <?php foreach ($catStats as $cs): ?>
          <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px dashed var(--border-color-light);">
            <span style="font-size: 0.85rem; color: var(--text-main);"><?= htmlspecialchars($cs['name']) ?></span>
            <span class="badge badge-gold"><?= $cs['count'] ?> Item</span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>

</div>

<?php renderAdminFooter(); ?>
