<?php
require_once __DIR__ . '/../../app/config/database.php';

// Set proper error handling
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

// Set JSON headers
header('Content-Type: application/json');

// Check if user is logged in
session_start();
// Temporarily disable authentication for testing
// if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'ADMIN' && $_SESSION['role'] !== 'ORDER_MANAGER')) {
//     http_response_code(403);
//     echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
//     exit;
// }

try {
    $mysqli = Database::getConnection();
    
    // Get all products with their stock information
    $stmt = $mysqli->prepare("
        SELECT 
            p.product_id,
            p.product_name,
            p.category,
            p.price,
            p.image_url,
            COALESCE(s.stock_quantity, 0) as stock_quantity,
            COALESCE(s.min_stock_level, 0) as min_stock_level,
            COALESCE(s.last_updated, p.created_at) as last_updated
        FROM products p
        LEFT JOIN product_stock s ON p.product_id = s.product_id
        ORDER BY p.category, p.product_name
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Calculate status based on stock levels
        $status = 'Low';
        if ($row['stock_quantity'] > $row['min_stock_level'] * 2) {
            $status = 'High';
        } else if ($row['stock_quantity'] > $row['min_stock_level']) {
            $status = 'Medium';
        }
        
        $products[] = [
            'id' => $row['product_id'],
            'name' => $row['product_name'],
            'category' => $row['category'],
            'price' => (float)$row['price'],
            'stock' => (int)$row['stock_quantity'],
            'min_stock' => (int)$row['min_stock_level'],
            'status' => $status,
            'last_updated' => $row['last_updated'],
            'image' => $row['image_url'] ? '/esang_delicacies/public/Images/products/' . $row['image_url'] : null
        ];
    }
    
    // Get all unique categories
    $categoryStmt = $mysqli->prepare("SELECT DISTINCT category FROM products ORDER BY category");
    $categoryStmt->execute();
    $categoryResult = $categoryStmt->get_result();
    
    $categories = [];
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row['category'];
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'categories' => $categories
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>