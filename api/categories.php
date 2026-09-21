<?php
/**
 * Categories REST API
 * List, create, update, and delete product categories.
 */

require_once __DIR__ . '/config.php';

$pdo = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Handle CORS Preflight
if ($method === 'OPTIONS') {
    jsonResponse(['status' => 'ok']);
}

function getRequestPayload() {
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $json = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return $json;
        }
    }
    return $_POST;
}

// -------------------------------------------------------------
// GET: List all categories with product counts
// -------------------------------------------------------------
if ($method === 'GET') {
    $sql = "
        SELECT c.*, COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON c.slug = p.category_slug AND p.is_active = 1
        GROUP BY c.id
        ORDER BY c.display_order ASC, c.id ASC
    ";
    $stmt = $pdo->query($sql);
    $categories = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'count' => count($categories),
        'data' => array_map(function($cat) {
            return [
                'id' => (int)$cat['id'],
                'slug' => $cat['slug'],
                'name' => $cat['name'],
                'icon' => $cat['icon'] ?: 'fas fa-th-large',
                'description' => $cat['description'] ?: '',
                'displayOrder' => (int)$cat['display_order'],
                'productCount' => (int)$cat['product_count']
            ];
        }, $categories)
    ]);
}

// -------------------------------------------------------------
// POST: Create or Update Category (Admin Only)
// -------------------------------------------------------------
if ($method === 'POST') {
    requireAdmin();
    $data = getRequestPayload();
    $action = $_GET['action'] ?? ($data['action'] ?? 'save');

    if ($action === 'delete') {
        $id = intval($data['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID kategori tidak valid.'], 400);
        }

        // Check if products still use this category
        $check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_slug = (SELECT slug FROM categories WHERE id = ?)");
        $check->execute([$id]);
        $count = $check->fetchColumn();
        if ($count > 0) {
            jsonResponse([
                'success' => false,
                'message' => "Kategori tidak dapat dihapus karena masih digunakan oleh {$count} produk."
            ], 400);
        }

        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['success' => true, 'message' => 'Kategori berhasil dihapus.']);
    }

    $id = intval($data['id'] ?? 0);
    $name = trim($data['name'] ?? '');
    $slug = trim($data['slug'] ?? '');
    $icon = trim($data['icon'] ?? 'fas fa-th-large');
    $description = trim($data['description'] ?? '');
    $displayOrder = intval($data['display_order'] ?? ($data['displayOrder'] ?? 0));

    if (empty($name)) {
        jsonResponse(['success' => false, 'message' => 'Nama kategori wajib diisi.'], 400);
    }

    // Generate slug if empty
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("
            UPDATE categories 
            SET name = ?, slug = ?, icon = ?, description = ?, display_order = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $slug, $icon, $description, $displayOrder, $id]);
        jsonResponse(['success' => true, 'message' => 'Kategori berhasil diperbarui!']);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO categories (name, slug, icon, description, display_order)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $slug, $icon, $description, $displayOrder]);
        jsonResponse(['success' => true, 'message' => 'Kategori baru berhasil ditambahkan!'], 201);
    }
}
