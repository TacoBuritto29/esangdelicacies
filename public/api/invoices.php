<?php
require_once __DIR__ . '/../../app/config/database.php';

json_headers();
set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
});

$db = Database::getConnection();

// Accept either session or explicit customerId
$payload = json_decode(file_get_contents('php://input'), true) ?: [];
$customerId = isset($payload['customerId']) ? (int)$payload['customerId'] : 0;
if ($customerId <= 0 && isset($_SESSION['customerId'])) {
    $customerId = (int)$_SESSION['customerId'];
}
if ($customerId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing customerId']);
    exit;
}

// Fetch orders for customer (lowercase schema)
$orders = [];
$stmt = $db->prepare('SELECT order_id, total_amount, status, delivery_address, payment_method, created_at FROM orders WHERE customer_id = ? ORDER BY order_id DESC LIMIT 50');
$stmt->bind_param('i', $customerId);
if (!$stmt->execute()) { throw new Exception($stmt->error); }
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $orders[(int)$row['order_id']] = [
        'orderId' => (int)$row['order_id'],
        'total' => (float)$row['total_amount'],
        'status' => $row['status'],
        'deliveryAddress' => $row['delivery_address'],
        'paymentMethod' => $row['payment_method'],
        'paymentStatus' => $row['payment_status'] ?? null,
        'createdAt' => $row['created_at'],
        'items' => [],
    ];
}
$stmt->close();

if (empty($orders)) {
    echo json_encode(['ok' => true, 'data' => []]);
    exit;
}

// Fetch items for those orders
$orderIds = array_keys($orders);
$placeholders = implode(',', array_fill(0, count($orderIds), '?'));
$types = str_repeat('i', count($orderIds));
$stmt = $db->prepare('SELECT oi.order_id, oi.product_id, oi.quantity, oi.price, p.product_name FROM order_items oi JOIN products p ON p.product_id = oi.product_id WHERE oi.order_id IN ('.$placeholders.')');
$stmt->bind_param($types, ...$orderIds);
if (!$stmt->execute()) { throw new Exception($stmt->error); }
$res = $stmt->get_result();
while ($it = $res->fetch_assoc()) {
    $oid = (int)$it['order_id'];
    if (!isset($orders[$oid])) { continue; }
    $orders[$oid]['items'][] = [
        'productId' => (int)$it['product_id'],
        'name' => $it['product_name'],
        'quantity' => (int)$it['quantity'],
        'price' => (float)$it['price'],
    ];
}
$stmt->close();

echo json_encode(['ok' => true, 'data' => array_values($orders)]);

?>


