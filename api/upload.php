<?php
/**
 * Image Upload API for Harsha Gypsum
 * Handles uploading new product photos, validating mime types, and returning URL.
 */

require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'OPTIONS') {
    jsonResponse(['status' => 'ok']);
}

requireAdmin();

if ($method === 'POST') {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errorCode = $_FILES['image']['error'] ?? 'no_file';
        jsonResponse([
            'success' => false,
            'message' => 'Tidak ada file gambar yang diunggah atau terjadi kesalahan server (kode: ' . $errorCode . ').'
        ], 400);
    }

    $file = $_FILES['image'];
    $maxSize = 5 * 1024 * 1024; // 5 MB
    if ($file['size'] > $maxSize) {
        jsonResponse(['success' => false, 'message' => 'Ukuran file terlalu besar! Maksimal 5 MB.'], 400);
    }

    // Validate MIME type
    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/jpg'  => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif'
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!array_key_exists($mime, $allowedMimes)) {
        jsonResponse(['success' => false, 'message' => 'Format file tidak didukung! Gunakan format JPG, PNG, atau WebP.'], 400);
    }

    $ext = $allowedMimes[$mime];
    $uploadDir = __DIR__ . '/../assets/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate clean unique filename
    $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
    $cleanBaseName = strtolower(preg_replace('/[^A-Za-z0-9-_]/', '-', $baseName));
    $cleanBaseName = substr($cleanBaseName, 0, 30);
    $fileName = 'prod_' . date('Ymd_His') . '_' . $cleanBaseName . '.' . $ext;
    $targetPath = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        jsonResponse(['success' => false, 'message' => 'Gagal menyimpan file ke direktori server.'], 500);
    }

    $publicUrl = 'assets/uploads/' . $fileName;

    jsonResponse([
        'success' => true,
        'message' => 'Foto berhasil diunggah!',
        'url' => $publicUrl,
        'filename' => $fileName
    ], 201);
}

// GET: List existing uploaded images for media picker
if ($method === 'GET') {
    $uploadDir = __DIR__ . '/../assets/uploads/';
    $imagesDir = __DIR__ . '/../assets/images/';
    
    $filesList = [];

    // Check uploads
    if (is_dir($uploadDir)) {
        foreach (scandir($uploadDir) as $f) {
            if ($f !== '.' && $f !== '..' && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $f)) {
                $filesList[] = [
                    'url' => 'assets/uploads/' . $f,
                    'name' => $f,
                    'type' => 'upload'
                ];
            }
        }
    }

    // Check default images
    if (is_dir($imagesDir)) {
        foreach (scandir($imagesDir) as $f) {
            if ($f !== '.' && $f !== '..' && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $f)) {
                $filesList[] = [
                    'url' => 'assets/images/' . $f,
                    'name' => $f,
                    'type' => 'default'
                ];
            }
        }
    }

    jsonResponse([
        'success' => true,
        'data' => $filesList
    ]);
}
