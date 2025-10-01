<?php
require_once __DIR__ . '/../_bootstrap.php';
json_headers_local();
require_login();

$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $db->query('SELECT prodId, prodName, prodCategory, prodPrice FROM PRODUCT ORDER BY prodCategory, prodName');
    if (!$result) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => $db->error]);
        exit;
    }
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'prodId' => (int)$row['prodId'],
            'prodName' => $row['prodName'],
            'prodCategory' => $row['prodCategory'],
            'prodPrice' => (float)$row['prodPrice'],
        ];
    }
    echo json_encode(['ok' => true, 'data' => $products]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid JSON body']);
        exit;
    }
    $customerId = current_user_id();
    $orderManagerId = (int)($payload['orderManagerId'] ?? 1);
    $deliveryAddress = trim($payload['deliveryAddress'] ?? '');
    $orderStatus = trim($payload['orderStatus'] ?? 'Pending');
    $items = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];
    if ($deliveryAddress === '' || empty($items)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Missing deliveryAddress or items']);
        exit;
    }

    // Calculate total
    $totalAmount = 0.0;
    foreach ($items as $i => $item) {
        $prodId = (int)($item['prodId'] ?? 0);
        $quantity = (int)($item['quantity'] ?? 0);
        if ($prodId <= 0 || $quantity <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid item at index ' . $i]);
            exit;
        }
        $stmt = $db->prepare('SELECT prodPrice FROM PRODUCT WHERE prodId = ?');
        $stmt->bind_param('i', $prodId);
        if (!$stmt->execute()) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>$stmt->error]); exit; }
        $price = 0.0;
        $stmt->bind_result($price);
        if ($stmt->fetch()) {
            $totalAmount += ((float)$price) * $quantity;
        } else { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Product not found: '.$prodId]); exit; }
        $stmt->close();
    }

    $db->begin_transaction();
    try {
        $orderDate = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d');
        $stmt = $db->prepare('INSERT INTO ORDERS (customerId, orderManagerId, orderDate, deliveryAddress, totalAmount, orderStatus) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('iissds', $customerId, $orderManagerId, $orderDate, $deliveryAddress, $totalAmount, $orderStatus);
        if (!$stmt->execute()) { throw new Exception($stmt->error); }
        $orderId = $stmt->insert_id;
        $stmt->close();

        $cashierId = (int)($payload['cashierId'] ?? 1);
        $paymentMethod = $payload['paymentMethod'] ?? null;

        foreach ($items as $item) {
            $prodId = (int)$item['prodId'];
            $quantity = (int)$item['quantity'];
            $stmt = $db->prepare('SELECT prodPrice FROM PRODUCT WHERE prodId = ?');
            $stmt->bind_param('i', $prodId);
            if (!$stmt->execute()) { throw new Exception($stmt->error); }
            $price = 0.0; $stmt->bind_result($price);
            if (!$stmt->fetch()) { throw new Exception('Product not found during insert'); }
            $stmt->close();

            $amount = ((float)$price) * $quantity;
            $stmt = $db->prepare('INSERT INTO ORDER_DETAILS (orderId, prodId, cashierId, amount, paymentMethod) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('iiids', $orderId, $prodId, $cashierId, $amount, $paymentMethod);
            if (!$stmt->execute()) { throw new Exception($stmt->error); }
            $stmt->close();
        }

        $db->commit();
        echo json_encode(['ok' => true, 'orderId' => $orderId, 'totalAmount' => $totalAmount]);
    } catch (Exception $e) {
        $db->rollback();
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
?>


