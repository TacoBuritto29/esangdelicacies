<?php
require_once __DIR__ . '/../../app/config/database.php';

json_headers();
set_exception_handler(function($e){ http_response_code(500); echo json_encode(['ok'=>false,'error'=>$e->getMessage()]); });

$db = Database::getConnection();

// Load orders joined with minimal customer info
$res = $db->query("SELECT o.order_id, o.customer_id, o.total_amount, o.status, o.payment_method, o.payment_status, o.delivery_address, o.created_at FROM orders o ORDER BY o.order_id DESC LIMIT 200");
$orders = [];
while ($row = $res->fetch_assoc()) { $orders[] = $row; }

echo json_encode(['ok'=>true,'data'=>$orders]);
?>


