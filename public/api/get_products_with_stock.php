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

// For testing purposes, we'll skip session check for now
// session_start();
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
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
            p.is_available,
            p.status,
            COALESCE(s.stock_quantity, 0) as stock_quantity,
            COALESCE(s.min_stock_level, 0) as min_stock_level,
            s.last_updated
        FROM products p
        LEFT JOIN product_stock s ON p.product_id = s.product_id
        WHERE p.status = 'active'
        ORDER BY p.category, p.product_name
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id' => $row['product_id'],
            'name' => $row['product_name'],
            'category' => $row['category'],
            'price' => (float)$row['price'],
            'image' => $row['image_url'] ?: '',
            'is_available' => (bool)$row['is_available'],
            'stock_quantity' => (int)$row['stock_quantity'],
            'min_stock_level' => (int)$row['min_stock_level'],
            'last_updated' => $row['last_updated']
        ];
    }
    
    // Get unique categories
    $categoryStmt = $mysqli->prepare("
        SELECT DISTINCT category
        FROM products 
        WHERE status = 'active'
        ORDER BY category
    ");
    
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
