<?php
/**
 * Shared Admin Navigation Layout
 */

require_once __DIR__ . '/auth_check.php';

function renderAdminHeader($pageTitle, $activeNav = 'dashboard') {
    $adminName = $_SESSION['admin_name'] ?? 'Admin Harsha';
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?= htmlspecialchars($pageTitle) ?> | Harsha Gypsum Admin</title>
      <link rel="icon" type="image/png" href="../assets/images/logo.png">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
      <link rel="stylesheet" href="style.css">
    </head>
    <body>
      <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
          <div class="sidebar-header">
            <img src="../assets/images/logo.png" alt="Logo" class="sidebar-logo-img">
            <div>
              <span class="sidebar-brand-name">Harsha Gypsum</span>
              <span class="sidebar-brand-sub">Management Suite</span>
            </div>
          </div>

          <nav class="sidebar-nav">
            <span class="nav-label">Menu Utama</span>
            <a href="dashboard.php" class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
              <i class="fas fa-chart-line"></i>
              <span>Dashboard</span>
            </a>

            <span class="nav-label">Katalog Produk</span>
            <a href="products.php" class="nav-item <?= $activeNav === 'products' ? 'active' : '' ?>">
              <i class="fas fa-boxes-stacked"></i>
              <span>Daftar Produk</span>
            </a>
            <a href="product-edit.php" class="nav-item <?= $activeNav === 'product-new' ? 'active' : '' ?>">
              <i class="fas fa-plus-circle"></i>
              <span>Tambah Produk Baru</span>
            </a>
            <a href="categories.php" class="nav-item <?= $activeNav === 'categories' ? 'active' : '' ?>">
              <i class="fas fa-tags"></i>
              <span>Kelola Kategori</span>
            </a>

            <span class="nav-label">Toko & Pintasan</span>
            <a href="../index.html" target="_blank" class="nav-item">
              <i class="fas fa-globe"></i>
              <span>Lihat Website Toko</span>
            </a>
            <a href="https://wa.me/6287775600462" target="_blank" class="nav-item">
              <i class="fab fa-whatsapp"></i>
              <span>WhatsApp Admin</span>
            </a>
          </nav>

          <div class="sidebar-footer">
            <div class="admin-user-info">
              <div class="admin-avatar">
                <?= strtoupper(substr($adminName, 0, 1)) ?>
              </div>
              <div>
                <div class="admin-name"><?= htmlspecialchars($adminName) ?></div>
                <div class="admin-role">Super Admin</div>
              </div>
            </div>
            <a href="logout.php" class="logout-btn" title="Keluar / Logout" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
              <i class="fas fa-sign-out-alt"></i>
            </a>
          </div>
        </aside>

        <!-- Main Wrapper -->
        <main class="admin-main">
          <!-- Topbar -->
          <header class="admin-topbar">
            <div class="topbar-left">
              <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle Menu">
                <i class="fas fa-bars"></i>
              </button>
              <h2 class="page-title"><?= htmlspecialchars($pageTitle) ?></h2>
            </div>
            <div class="topbar-right">
              <a href="product-edit.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Produk
              </a>
              <a href="../index.html" target="_blank" class="btn-view-site">
                <i class="fas fa-external-link-alt"></i> Buka Website
              </a>
            </div>
          </header>

          <!-- Content Body -->
          <div class="admin-content">
    <?php
}

function renderAdminFooter() {
    ?>
          </div><!-- /.admin-content -->
        </main>
      </div><!-- /.admin-wrapper -->

      <script>
        // Mobile Sidebar Toggle
        const mobileToggle = document.getElementById('mobileToggle');
        const adminSidebar = document.getElementById('adminSidebar');
        if (mobileToggle && adminSidebar) {
          mobileToggle.addEventListener('click', () => {
            adminSidebar.classList.toggle('open');
          });
        }
      </script>
    </body>
    </html>
    <?php
}
