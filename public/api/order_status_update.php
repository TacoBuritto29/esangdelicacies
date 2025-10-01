<?php
require_once __DIR__ . '/../../app/config/database.php';

json_headers();
set_exception_handler(function($e){ http_response_code(500); echo json_encode(['ok'=>false,'error'=>$e->getMessage()]); });

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'error'=>'Method not allowed']); exit; }

$db = Database::getConnection();
$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid JSON']); exit; }

$orderId = (int)($payload['orderId'] ?? 0);
$status = trim($payload['status'] ?? '');
if ($orderId <= 0 || $status === '') { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Missing fields']); exit; }

$stmt = $db->prepare('UPDATE orders SET status = ?, updated_at = NOW() WHERE order_id = ?');
$stmt->bind_param('si', $status, $orderId);
if (!$stmt->execute()) { throw new Exception($stmt->error); }
$stmt->close();

echo json_encode(['ok'=>true]);
?>


