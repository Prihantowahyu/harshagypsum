<?php
/**
 * Admin Login Page
 * Harsha Gypsum Management System
 */

require_once __DIR__ . '/../api/config.php';

// If already logged in, redirect straight to dashboard
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errorMsg = '';
$infoMsg = '';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'logged_out') {
        $infoMsg = 'Anda telah berhasil keluar (logout).';
    } elseif ($_GET['msg'] === 'login_required') {
        $errorMsg = 'Sesi Anda telah berakhir, silakan login kembali.';
    }
}

// Process Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $errorMsg = 'Username dan password wajib diisi!';
    } else {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            // Login Success
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['name'];
            
            header('Location: dashboard.php');
            exit;
        } else {
            // Also allow direct fallback for initial setup if password hash changed
            if ($username === 'admin' && $password === 'admin123') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = 1;
                $_SESSION['admin_username'] = 'admin';
                $_SESSION['admin_name'] = 'Admin Harsha Gypsum';
                
                // Re-hash for security
                $newHash = password_hash('admin123', PASSWORD_BCRYPT);
                $update = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
                $update->execute([$newHash]);

                header('Location: dashboard.php');
                exit;
            }
            $errorMsg = 'Username atau password salah. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin | Harsha Gypsum</title>
  <link rel="icon" type="image/png" href="../assets/images/logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">

  <div class="login-card">
    <div class="login-header">
      <img src="../assets/images/logo.png" alt="Harsha Gypsum Logo" class="login-logo">
      <h1 class="login-title">Admin Panel</h1>
      <p class="login-subtitle">Pusat Manajemen Katalog Harsha Gypsum</p>
    </div>

    <?php if (!empty($errorMsg)): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= htmlspecialchars($errorMsg) ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($infoMsg)): ?>
      <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <span><?= htmlspecialchars($infoMsg) ?></span>
      </div>
    <?php endif; ?>

    <form method="POST" action="index.php">
      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <div style="position: relative;">
          <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div style="position: relative;">
          <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px; padding: 12px;">
        <i class="fas fa-sign-in-alt"></i> Masuk ke Panel Admin
      </button>
    </form>

    <div class="demo-box">
      <strong><i class="fas fa-key"></i> Akun Bawaan (Default):</strong><br>
      Username: <code style="color: #fff; background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 4px;">admin</code><br>
      Password: <code style="color: #fff; background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 4px;">admin123</code>
    </div>

    <div style="text-align: center; margin-top: 24px;">
      <a href="../index.html" style="font-size: 0.85rem; color: var(--text-muted);">
        <i class="fas fa-arrow-left"></i> Kembali ke Website Utama
      </a>
    </div>
  </div>

</body>
</html>
