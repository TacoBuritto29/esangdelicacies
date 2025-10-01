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
$stockQuantity = (int)($data['stock_quantity'] ?? 0);
$minStockLevel = (int)($data['min_stock_level'] ?? 0);

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

try {
    $mysqli = Database::getConnection();
    
    // Check if stock record exists
    $checkStmt = $mysqli->prepare("SELECT COUNT(*) FROM product_stock WHERE product_id = ?");
    $checkStmt->bind_param('i', $productId);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();
    
    if ($count > 0) {
        // Update existing stock record
        $stmt = $mysqli->prepare("
            UPDATE product_stock 
            SET stock_quantity = ?, min_stock_level = ?, last_updated = NOW()
            WHERE product_id = ?
        ");
        $stmt->bind_param('iii', $stockQuantity, $minStockLevel, $productId);
    } else {
        // Insert new stock record
        $stmt = $mysqli->prepare("
            INSERT INTO product_stock (product_id, stock_quantity, min_stock_level, last_updated)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->bind_param('iii', $productId, $stockQuantity, $minStockLevel);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Stock updated successfully']);
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
