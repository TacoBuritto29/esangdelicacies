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

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

try {
    $mysqli = Database::getConnection();
    
    // Check if product is used in any orders
    $orderCheckStmt = $mysqli->prepare("
        SELECT COUNT(*) 
        FROM order_items oi 
        JOIN orders o ON oi.order_id = o.order_id 
        WHERE oi.product_id = ? AND o.status NOT IN ('cancelled', 'delivered')
    ");
    $orderCheckStmt->bind_param('i', $productId);
    $orderCheckStmt->execute();
    $orderCheckStmt->bind_result($orderCount);
    $orderCheckStmt->fetch();
    $orderCheckStmt->close();
    
    if ($orderCount > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete product that is used in active orders']);
        exit;
    }
    
    // Start transaction
    $mysqli->begin_transaction();
    
    try {
        // Delete stock record first
        $stockStmt = $mysqli->prepare("DELETE FROM product_stock WHERE product_id = ?");
        $stockStmt->bind_param('i', $productId);
        $stockStmt->execute();
        $stockStmt->close();
        
        // Delete order items
        $orderItemsStmt = $mysqli->prepare("DELETE FROM order_items WHERE product_id = ?");
        $orderItemsStmt->bind_param('i', $productId);
        $orderItemsStmt->execute();
        $orderItemsStmt->close();
        
        // Delete product
        $productStmt = $mysqli->prepare("DELETE FROM products WHERE product_id = ?");
        $productStmt->bind_param('i', $productId);
        $productStmt->execute();
        $productStmt->close();
        
        $mysqli->commit();
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        
    } catch (Exception $e) {
        $mysqli->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
