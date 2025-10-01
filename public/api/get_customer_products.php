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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $mysqli = Database::getConnection();
    
    // Get all active products with their stock information
    $stmt = $mysqli->prepare("
        SELECT 
            p.product_id,
            p.product_name,
            p.category,
            p.price,
            p.image_url,
            p.is_available,
            p.status,
            p.description,
            COALESCE(s.stock_quantity, 0) as stock_quantity,
            COALESCE(s.min_stock_level, 0) as min_stock_level,
            s.last_updated as stock_updated
        FROM products p
        LEFT JOIN product_stock s ON p.product_id = s.product_id
        WHERE p.status = 'active' AND p.is_available = 1
        ORDER BY p.category, p.product_name
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    $categories = [];
    
    while ($row = $result->fetch_assoc()) {
        $product = [
            'id' => $row['product_id'],
            'name' => $row['product_name'],
            'category' => $row['category'],
            'price' => (float)$row['price'],
            'image' => $row['image_url'] ? '/esang_delicacies/public/Images/products/' . $row['image_url'] : null,
            'description' => $row['description'],
            'stock_quantity' => (int)$row['stock_quantity'],
            'min_stock_level' => (int)$row['min_stock_level'],
            'stock_status' => $row['stock_quantity'] <= $row['min_stock_level'] ? 'low' : 'good',
            'is_available' => (bool)$row['is_available'],
            'stock_updated' => $row['stock_updated']
        ];
        
        $products[] = $product;
        
        // Group products by category
        if (!isset($categories[$row['category']])) {
            $categories[$row['category']] = [];
        }
        $categories[$row['category']][] = $product;
    }
    
    // Format categories for frontend
    $formattedCategories = [];
    foreach ($categories as $categoryName => $categoryProducts) {
        $formattedCategories[] = [
            'id' => strtolower(str_replace(' ', '-', $categoryName)),
            'name' => $categoryName,
            'itemType' => 'single-price', // Default type for now
            'items' => $categoryProducts
        ];
    }
    
    echo json_encode([
        'success' => true,
        'categories' => $formattedCategories,
        'products' => $products,
        'total_products' => count($products)
    ]);
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>