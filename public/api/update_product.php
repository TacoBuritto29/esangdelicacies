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

$productId = (int)($data['product_id'] ?? 0);
$name = trim($data['name'] ?? '');
$category = trim($data['category'] ?? '');
$price = isset($data['price']) ? (float)$data['price'] : 0;

if ($productId <= 0 || $name === '' || $category === '' || $price <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid fields']);
    exit;
}

try {
    $mysqli = Database::getConnection();
    
    // Check if product name already exists in another product
    $checkStmt = $mysqli->prepare("SELECT COUNT(*) FROM products WHERE product_name = ? AND category = ? AND product_id != ?");
    $checkStmt->bind_param('ssi', $name, $category, $productId);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();
    
    if ($count > 0) {
        echo json_encode(['success' => false, 'message' => 'Product with this name already exists in this category']);
        exit;
    }
    
    $stmt = $mysqli->prepare("
        UPDATE products 
        SET product_name = ?, category = ?, price = ?, updated_at = NOW()
        WHERE product_id = ?
    ");
    $stmt->bind_param('ssdi', $name, $category, $price, $productId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
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
