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

// Check if user is admin
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $mysqli = Database::getConnection();
    $transactions = [];

    // Get transaction history with customer names
    // Join orders with users table to get customer names
    $stmt = $mysqli->prepare("
        SELECT 
            o.order_id,
            o.total_amount,
            o.payment_method,
            o.payment_status,
            o.created_at,
            CONCAT(u.first_name, ' ', u.last_name) as customer_name,
            u.email as customer_email
        FROM orders o
        LEFT JOIN users u ON o.customer_id = u.user_id
        WHERE o.payment_status IN ('paid', 'paid_pending_verification')
        ORDER BY o.created_at DESC
        LIMIT 100
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $transactions[] = [
            'order_id' => $row['order_id'],
            'customer_name' => $row['customer_name'] ?: 'Unknown Customer',
            'customer_email' => $row['customer_email'],
            'payment_method' => ucfirst($row['payment_method']),
            'payment_status' => $row['payment_status'],
            'amount' => number_format($row['total_amount'], 2),
            'date' => date('m/d/Y', strtotime($row['created_at'])),
            'created_at' => $row['created_at']
        ];
    }
    $stmt->close();

    echo json_encode(['success' => true, 'data' => $transactions]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
