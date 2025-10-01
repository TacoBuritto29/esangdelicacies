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
    
    // Get the most recent order for this customer or a specific order if provided
    $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;
    
    if ($orderId) {
        // Get specific order
        $stmt = $mysqli->prepare("
            SELECT 
                o.order_id,
                o.customer_id,
                o.total_amount,
                o.status,
                o.order_type,
                o.delivery_address,
                o.special_instructions,
                o.payment_method,
                o.payment_status,
                o.rider_id,
                o.notes,
                o.created_at,
                o.updated_at,
                r.name as rider_name,
                r.phone as rider_phone,
                r.plateNum as rider_plate,
                r.email as rider_email
            FROM orders o
            LEFT JOIN rider r ON o.rider_id = r.empId
            WHERE o.order_id = ? AND o.customer_id = ?
            ORDER BY o.created_at DESC
            LIMIT 1
        ");
        $stmt->bind_param('ii', $orderId, $customerId);
    } else {
        // Get most recent order
        $stmt = $mysqli->prepare("
            SELECT 
                o.order_id,
                o.customer_id,
                o.total_amount,
                o.status,
                o.order_type,
                o.delivery_address,
                o.special_instructions,
                o.payment_method,
                o.payment_status,
                o.rider_id,
                o.notes,
                o.created_at,
                o.updated_at,
                r.name as rider_name,
                r.phone as rider_phone,
                r.plateNum as rider_plate,
                r.email as rider_email
            FROM orders o
            LEFT JOIN rider r ON o.rider_id = r.empId
            WHERE o.customer_id = ?
            ORDER BY o.created_at DESC
            LIMIT 1
        ");
        $stmt->bind_param('i', $customerId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    
    if (!$order) {
        echo json_encode([
            'success' => false, 
            'message' => 'No orders found',
            'has_orders' => false
        ]);
        exit;
    }
    
    // Get order items
    $stmt = $mysqli->prepare("
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
    $stmt->bind_param('i', $order['order_id']);
    $stmt->execute();
    $itemsResult = $stmt->get_result();
    
    $orderItems = [];
    while ($item = $itemsResult->fetch_assoc()) {
        $orderItems[] = [
            'order_item_id' => $item['order_item_id'],
            'product_id' => $item['product_id'],
            'product_name' => $item['product_name'],
            'category' => $item['category'],
            'quantity' => (int)$item['quantity'],
            'price' => (float)$item['price'],
            'subtotal' => (float)$item['subtotal'],
            'image_url' => $item['image_url'] ? '/esang_delicacies/public/Images/products/' . $item['image_url'] : null
        ];
    }
    $stmt->close();
    
    // Format the response
    $orderData = [
        'order_id' => (int)$order['order_id'],
        'customer_id' => (int)$order['customer_id'],
        'total_amount' => (float)$order['total_amount'],
        'status' => $order['status'],
        'order_type' => $order['order_type'],
        'delivery_address' => $order['delivery_address'],
        'special_instructions' => $order['special_instructions'],
        'payment_method' => $order['payment_method'],
        'payment_status' => $order['payment_status'],
        'notes' => $order['notes'],
        'created_at' => $order['created_at'],
        'updated_at' => $order['updated_at'],
        'items' => $orderItems,
        'rider' => null
    ];
    
    // Add rider information if assigned
    if ($order['rider_id'] && $order['rider_name']) {
        $orderData['rider'] = [
            'rider_id' => (int)$order['rider_id'],
            'name' => $order['rider_name'],
            'phone' => $order['rider_phone'],
            'plate_number' => $order['rider_plate'],
            'email' => $order['rider_email'],
            'tracking_id' => 'TRK' . str_pad($order['order_id'], 6, '0', STR_PAD_LEFT) // Generate tracking ID
        ];
    }
    
    // Determine progress step based on status
    $progressSteps = [
        'pending' => 0,
        'confirmed' => 1,
        'preparing' => 2,
        'ready_for_pickup' => 3,
        'out_for_delivery' => 3,
        'delivered' => 4,
        'cancelled' => -1
    ];
    
    $currentStep = $progressSteps[$order['status']] ?? 0;
    
    echo json_encode([
        'success' => true,
        'has_orders' => true,
        'order' => $orderData,
        'progress_step' => $currentStep,
        'status_text' => ucwords(str_replace('_', ' ', $order['status'])),
        'can_return' => $order['status'] === 'delivered',
        'show_rider_details' => !is_null($orderData['rider'])
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>