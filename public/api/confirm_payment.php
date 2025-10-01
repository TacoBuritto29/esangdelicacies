<?php
require_once __DIR__ . '/../../app/config/database.php';

json_headers();
set_exception_handler(function($e){ http_response_code(500); echo json_encode(['ok'=>false,'error'=>$e->getMessage()]); });

$db = Database::getConnection();
$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid JSON']); exit; }

$orderId = isset($payload['orderId']) ? (int)$payload['orderId'] : 0;
$customerId = isset($payload['customerId']) ? (int)$payload['customerId'] : 0;
$method = isset($payload['paymentMethod']) ? trim($payload['paymentMethod']) : null;
$reference = isset($payload['referenceNumber']) ? trim($payload['referenceNumber']) : null;

if ($orderId <= 0 || $customerId <= 0 || !$method) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Missing fields']);
    exit;
}

// Update orders payment fields
$stmt = $db->prepare('UPDATE orders SET payment_method = ?, payment_status = ?, updated_at = NOW() WHERE order_id = ? AND customer_id = ?');
$status = 'paid_pending_verification';
$stmt->bind_param('ssii', $method, $status, $orderId, $customerId);
if (!$stmt->execute()) { throw new Exception($stmt->error); }
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected <= 0) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Order not found']); exit; }

// Note: Skipping optional insert into order_status_log to avoid FK to users.changed_by

echo json_encode(['ok'=>true]);
?>


