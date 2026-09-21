<?php
/**
 * Authentication Gate for Admin Pages
 */

require_once __DIR__ . '/../api/config.php';

if (!isAdminLoggedIn()) {
    header('Location: index.php?msg=login_required');
    exit;
}
