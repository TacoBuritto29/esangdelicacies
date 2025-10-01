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

session_start();
json_headers();

// Check if customer is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $mysqli = Database::getConnection();
    $customerId = $_SESSION['customerId'];
    $customerName = $_SESSION['user_name'] ?? 'Customer';
    
    // Get completed orders for this customer
    $stmt = $mysqli->prepare("
        SELECT 
            o.order_id,
            o.customer_id,
            o.total_amount,
            o.status,
            o.order_type,
            o.delivery_address,
            o.payment_method,
            o.payment_status,
            o.created_at,
            o.updated_at
        FROM orders o
        WHERE o.customer_id = ? 
        AND o.status IN ('delivered', 'completed')
        ORDER BY o.created_at DESC
    ");
    
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $completedOrders = [];
    
    while ($order = $result->fetch_assoc()) {
        // Get order items
        $itemsStmt = $mysqli->prepare("
            SELECT 
                oi.order_item_id,
                oi.product_id,
                oi.quantity,
                oi.price,
                oi.subtotal,
                p.product_name,
                p.image_url,
                p.category
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = ?
        ");
        $itemsStmt->bind_param('i', $order['order_id']);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        
        $orderItems = [];
        while ($item = $itemsResult->fetch_assoc()) {
            $orderItems[] = [
                'name' => $item['product_name'],
                'price' => (float)$item['price'],
                'quantity' => (int)$item['quantity'],
                'subtotal' => (float)$item['subtotal'],
                'image_url' => $item['image_url'] ? '/esang_delicacies/public/Images/products/' . $item['image_url'] : null
            ];
        }
        $itemsStmt->close();
        
        // Parse delivery address
        $addressParts = explode(', ', $order['delivery_address']);
        $location = [
            'barangay' => $addressParts[0] ?? '',
            'district' => $addressParts[1] ?? '',
            'city' => $addressParts[2] ?? '',
            'region' => $addressParts[3] ?? 'Metro Manila'
        ];
        
        $createdDate = new DateTime($order['created_at']);
        
        $completedOrders[] = [
            'id' => (int)$order['order_id'],
            'orderNumber' => '#' . $order['order_id'],
            'items' => $orderItems,
            'payment' => ucwords(str_replace('_', ' ', $order['payment_method'])),
            'location' => $location,
            'total' => (float)$order['total_amount'],
            'deliveryFee' => 0.00, // You can calculate this based on your business logic
            'date' => $createdDate->format('m/d/Y'),
            'time' => $createdDate->format('g:i:s A'),
            'timestamp' => $createdDate->format('m/d/Y g:i:s A'),
            'username' => $customerName,
            'image' => !empty($orderItems) && $orderItems[0]['image_url'] 
                        ? $orderItems[0]['image_url'] 
                        : 'https://placehold.co/200x150?text=Order+Image',
            'status' => $order['status'],
            'order_type' => $order['order_type']
        ];
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'orders' => $completedOrders,
        'count' => count($completedOrders)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>