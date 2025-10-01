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

try {
    $mysqli = Database::getConnection();
    
    // Get all products with their categories
    $stmt = $mysqli->prepare("
        SELECT 
            product_id,
            product_name,
            category,
            price,
            image_url,
            is_available,
            status,
            created_at
        FROM products 
        WHERE status = 'active'
        ORDER BY category, product_name
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
            'created_at' => $row['created_at']
        ];
    }
    
    // Get unique categories
    $categoryStmt = $mysqli->prepare("
        SELECT DISTINCT category, COUNT(*) as item_count
        FROM products 
        WHERE status = 'active'
        GROUP BY category
        ORDER BY category
    ");
    
    $categoryStmt->execute();
    $categoryResult = $categoryStmt->get_result();
    
    $categories = [];
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = [
            'name' => $row['category'],
            'item_count' => (int)$row['item_count']
        ];
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
