<?php
require_once __DIR__ . '/../../app/config/database.php';

// Set proper error handling to prevent HTML output
set_error_handler(function($severity, $message, $file, $line) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $message]);
    exit;
});

set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
});

json_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON body']);
    exit;
}

$name = trim($data['product_name'] ?? $data['prodName'] ?? '');
$category = trim($data['category'] ?? $data['prodCategory'] ?? '');
$price = isset($data['price']) ? (float)$data['price'] : (isset($data['prodPrice']) ? (float)$data['prodPrice'] : 0);
$imageUrl = trim($data['image_url'] ?? '');

if ($name === '' || $category === '' || $price <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid fields']);
    exit;
}

try {
    $db = Database::getConnection();
    
    // Check if product already exists
    $checkStmt = $db->prepare('SELECT COUNT(*) FROM products WHERE product_name = ? AND category = ?');
    $checkStmt->bind_param('ss', $name, $category);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();
    
    if ($count > 0) {
        echo json_encode(['success' => false, 'message' => 'Product with this name already exists in this category']);
        exit;
    }
    
    $stmt = $db->prepare('INSERT INTO products (product_name, category, price, image_url) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssds', $name, $category, $price, $imageUrl);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'product_id' => $stmt->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
