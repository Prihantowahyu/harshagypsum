<?php
/**
 * Products REST API
 * Handles listing, filtering, adding, updating, and deleting products.
 */

require_once __DIR__ . '/config.php';

$pdo = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Handle CORS Preflight
if ($method === 'OPTIONS') {
    jsonResponse(['status' => 'ok']);
}

// Helper to get request input (JSON or Form Data)
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
// GET: List products or single product
// -------------------------------------------------------------
if ($method === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $bestSellerOnly = isset($_GET['best_seller']) && $_GET['best_seller'] == '1';
    $includeInactive = isAdminLoggedIn() && isset($_GET['include_inactive']) && $_GET['include_inactive'] == '1';

    if ($id > 0) {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_slug = c.slug 
            WHERE p.id = ? " . ($includeInactive ? "" : "AND p.is_active = 1") . "
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $prod = $stmt->fetch();

        if (!$prod) {
            jsonResponse(['success' => false, 'message' => 'Produk tidak ditemukan.'], 404);
        }

        jsonResponse([
            'success' => true,
            'data' => formatProductOutput($prod)
        ]);
    }

    // Build query
    $whereClauses = [];
    $params = [];

    if (!$includeInactive) {
        $whereClauses[] = "p.is_active = 1";
    }

    if (!empty($category) && $category !== 'all') {
        $whereClauses[] = "p.category_slug = ?";
        $params[] = $category;
    }

    if ($bestSellerOnly) {
        $whereClauses[] = "p.is_best_seller = 1";
    }

    if (!empty($search)) {
        $whereClauses[] = "(p.name LIKE ? OR p.code LIKE ? OR p.description LIKE ?)";
        $wildcard = "%" . $search . "%";
        $params[] = $wildcard;
        $params[] = $wildcard;
        $params[] = $wildcard;
    }

    $whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";
    $sql = "
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_slug = c.slug 
        {$whereSql} 
        ORDER BY p.is_best_seller DESC, p.id ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $products = array_map('formatProductOutput', $rows);

    jsonResponse([
        'success' => true,
        'count' => count($products),
        'data' => $products
    ]);
}

// -------------------------------------------------------------
// POST: Create, Update, or Toggle (Admin Only)
// -------------------------------------------------------------
if ($method === 'POST') {
    requireAdmin();
    $data = getRequestPayload();
    $action = isset($_GET['action']) ? $_GET['action'] : (isset($data['action']) ? $data['action'] : 'save');

    // Action: Toggle Best Seller
    if ($action === 'toggle_bestseller') {
        $prodId = intval($data['id'] ?? 0);
        if ($prodId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID produk tidak valid.'], 400);
        }
        $stmt = $pdo->prepare("UPDATE products SET is_best_seller = CASE WHEN is_best_seller = 1 THEN 0 ELSE 1 END WHERE id = ?");
        $stmt->execute([$prodId]);
        
        $fetch = $pdo->prepare("SELECT is_best_seller FROM products WHERE id = ?");
        $fetch->execute([$prodId]);
        $newVal = $fetch->fetchColumn();

        jsonResponse([
            'success' => true,
            'message' => 'Status Best Seller berhasil diperbarui.',
            'is_best_seller' => (int)$newVal
        ]);
    }

    // Action: Toggle Active Status
    if ($action === 'toggle_status') {
        $prodId = intval($data['id'] ?? 0);
        if ($prodId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID produk tidak valid.'], 400);
        }
        $stmt = $pdo->prepare("UPDATE products SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?");
        $stmt->execute([$prodId]);
        
        $fetch = $pdo->prepare("SELECT is_active FROM products WHERE id = ?");
        $fetch->execute([$prodId]);
        $newVal = $fetch->fetchColumn();

        jsonResponse([
            'success' => true,
            'message' => 'Status aktif berhasil diperbarui.',
            'is_active' => (int)$newVal
        ]);
    }

    // Action: Delete (via POST)
    if ($action === 'delete') {
        $prodId = intval($data['id'] ?? 0);
        if ($prodId <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID produk tidak valid.'], 400);
        }
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$prodId]);
        jsonResponse(['success' => true, 'message' => 'Produk berhasil dihapus.']);
    }

    // Save or Update Product
    $prodId = isset($data['id']) ? intval($data['id']) : 0;
    $code = trim($data['code'] ?? '');
    $name = trim($data['name'] ?? '');
    $category_slug = trim($data['category'] ?? ($data['category_slug'] ?? ''));
    $width = trim($data['width'] ?? '');
    $width_number = floatval($data['width_number'] ?? ($data['widthNumber'] ?? 0));
    $length = trim($data['length'] ?? '');
    $length_meter = floatval($data['length_meter'] ?? ($data['lengthMeter'] ?? 0));
    $price = floatval($data['price'] ?? 0);
    $price_bulk = floatval($data['price_bulk'] ?? ($data['priceBulk'] ?? 0));
    $min_bulk_qty = intval($data['min_bulk_qty'] ?? ($data['minBulkQty'] ?? 10));
    $image = trim($data['image'] ?? 'assets/images/list-minimalis.jpg');
    $description = trim($data['description'] ?? '');
    $is_best_seller = !empty($data['is_best_seller']) || !empty($data['isBestSeller']) ? 1 : 0;
    $is_popular = !empty($data['is_popular']) || !empty($data['isPopular']) ? 1 : 0;
    $is_active = isset($data['is_active']) ? intval($data['is_active']) : 1;

    // Parse specs: could be array or JSON string
    $specs = [];
    if (isset($data['specs'])) {
        if (is_array($data['specs'])) {
            $specs = $data['specs'];
        } elseif (is_string($data['specs'])) {
            $decoded = json_decode($data['specs'], true);
            if (is_array($decoded)) {
                $specs = $decoded;
            } else {
                // If text lines, parse lines like "Label: Value"
                $lines = explode("\n", $data['specs']);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        $parts = explode(':', $line, 2);
                        if (count($parts) === 2) {
                            $specs[] = ['label' => trim($parts[0]), 'value' => trim($parts[1])];
                        } else {
                            $specs[] = ['label' => 'Spesifikasi', 'value' => $line];
                        }
                    }
                }
            }
        }
    }
    $specsJson = json_encode($specs, JSON_UNESCAPED_UNICODE);

    // Validation
    if (empty($name)) {
        jsonResponse(['success' => false, 'message' => 'Nama produk wajib diisi.'], 400);
    }
    if (empty($category_slug)) {
        jsonResponse(['success' => false, 'message' => 'Kategori produk wajib dipilih.'], 400);
    }
    if ($price <= 0) {
        jsonResponse(['success' => false, 'message' => 'Harga eceran harus lebih besar dari 0.'], 400);
    }

    // Auto-generate code if empty
    if (empty($code)) {
        $prefix = strtoupper(substr($category_slug, 0, 2));
        $code = 'HG-' . $prefix . rand(100, 999);
    }

    if ($prodId > 0) {
        // UPDATE existing
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
            $image, $description, $specsJson, $is_best_seller, $is_popular, $is_active,
            $prodId
        ]);

        jsonResponse([
            'success' => true,
            'message' => 'Produk berhasil diperbarui!',
            'id' => $prodId
        ]);
    } else {
        // INSERT new
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
            $image, $description, $specsJson, $is_best_seller, $is_popular, $is_active
        ]);
        $newId = (int)$pdo->lastInsertId();

        jsonResponse([
            'success' => true,
            'message' => 'Produk baru berhasil ditambahkan!',
            'id' => $newId
        ], 201);
    }
}

// -------------------------------------------------------------
// DELETE: Delete product by ID (Admin Only)
// -------------------------------------------------------------
if ($method === 'DELETE') {
    requireAdmin();
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        $payload = getRequestPayload();
        $id = intval($payload['id'] ?? 0);
    }

    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID produk tidak valid.'], 400);
    }

    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    jsonResponse(['success' => true, 'message' => 'Produk berhasil dihapus secara permanen.']);
}

/**
 * Format DB row to JSON frontend contract
 */
function formatProductOutput($row) {
    $specs = [];
    if (!empty($row['specs'])) {
        $decoded = json_decode($row['specs'], true);
        if (is_array($decoded)) {
            $specs = $decoded;
        }
    }

    return [
        'id' => (int)$row['id'],
        'code' => $row['code'],
        'name' => $row['name'],
        'category' => $row['category_slug'],
        'categoryLabel' => $row['category_name'] ?? ucfirst($row['category_slug']),
        'width' => $row['width'] ?? '',
        'widthNumber' => floatval($row['width_number'] ?? 0),
        'length' => $row['length'] ?? '',
        'lengthMeter' => floatval($row['length_meter'] ?? 0),
        'price' => floatval($row['price']),
        'priceBulk' => floatval($row['price_bulk']),
        'minBulkQty' => intval($row['min_bulk_qty']),
        'rating' => floatval($row['rating'] ?? 5.0),
        'sold' => intval($row['sold'] ?? 0),
        'isPopular' => (bool)$row['is_popular'],
        'isBestSeller' => (bool)$row['is_best_seller'],
        'image' => $row['image'],
        'description' => $row['description'] ?? '',
        'specs' => $specs,
        'isActive' => (bool)$row['is_active'],
        'createdAt' => $row['created_at'] ?? ''
    ];
}
