<?php
// public/api/get_order_status.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/esang_delicacies/app/views/_bootstrap.php';
json_headers_local();

if (!isset($_SESSION['customerId'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$customerId = $_SESSION['customerId'];
$mysqli = db();

// Fetch all orders for this customer with their status
$sql = 'SELECT order_id, status, updated_at FROM orders WHERE customer_id = ? ORDER BY updated_at DESC';
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $customerId);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

echo json_encode(['success' => true, 'orders' => $orders]);
