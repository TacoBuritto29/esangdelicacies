<?php
require_once __DIR__ . '/../../app/config/database.php';

json_headers();
// Ensure PHP notices/warnings are returned as JSON, not HTML
set_error_handler(function($severity, $message, $file, $line) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $message, 'at' => basename($file) . ':' . $line]);
    exit;
});
set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    exit;
});
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$db = Database::getConnection();

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON body']);
    exit;
}

$customerId = isset($payload['customerId']) ? (int)$payload['customerId'] : 0;
$orderManagerId = isset($payload['orderManagerId']) ? (int)$payload['orderManagerId'] : 1;
$deliveryAddress = isset($payload['deliveryAddress']) ? trim($payload['deliveryAddress']) : '';
$orderStatus = isset($payload['orderStatus']) ? trim($payload['orderStatus']) : 'Pending';
$items = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];

if ($customerId <= 0 || $deliveryAddress === '' || empty($items)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing required fields']);
    exit;
}

// Known name→ID mappings in lowercase schema for resilient resolution
$PRODUCT_NAME_TO_ID = [
    'ready to fry siomai' => 11,
];

// Normalize items to use prodId and quantity
$normalizedItems = [];
foreach ($items as $i => $item) {
    $prodId = null;
    if (isset($item['prodId'])) { $prodId = (int)$item['prodId']; }
    elseif (isset($item['product_id'])) { $prodId = (int)$item['product_id']; }
    elseif (isset($item['id'])) { $prodId = (int)$item['id']; }
    $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
    $name = isset($item['name']) ? trim($item['name']) : null;

    if ($quantity <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid item quantity at index ' . $i]);
        exit;
    }

    // If prodId is missing or clearly not a real DB id, try to resolve by name
    if ((!$prodId || $prodId <= 0) && $name) {
        // Normalize name: strip size in parentheses and trim
        $normalizedName = trim(preg_replace('/\s*\([^\)]*\)\s*$/', '', $name));

        // Try exact match first
        $stmt = $db->prepare('SELECT prodId FROM PRODUCT WHERE prodName = ? LIMIT 1');
        $stmt->bind_param('s', $normalizedName);
        if ($stmt->execute()) {
            $resolvedId = 0; $stmt->bind_result($resolvedId);
            if ($stmt->fetch()) { $prodId = (int)$resolvedId; }
        }
        $stmt->close();

        // If not found, try case-insensitive LIKE match
        if ($prodId <= 0) {
            $like = '%' . $normalizedName . '%';
            $stmt = $db->prepare('SELECT prodId FROM PRODUCT WHERE LOWER(prodName) LIKE LOWER(?) LIMIT 1');
            $stmt->bind_param('s', $like);
            if ($stmt->execute()) {
                $resolvedId = 0; $stmt->bind_result($resolvedId);
                if ($stmt->fetch()) { $prodId = (int)$resolvedId; }
            }
            $stmt->close();
        }
    }

    if ($prodId <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Product not found: ' . ($name ?: 'Unknown')]);
        exit;
    }

    $normalizedItems[] = ['prodId' => $prodId, 'quantity' => $quantity, 'name' => $name];
}

// Small helpers to adapt to either legacy (PRODUCT/ORDERS/ORDER_DETAILS)
// or new schema (products/orders/order_items)
$tableExists = function(mysqli $db, string $table): bool {
    // Note: SHOW TABLES does not support placeholders in prepared statements
    $like = $db->real_escape_string($table);
    $sql = "SHOW TABLES LIKE '".$like."'";
    $res = $db->query($sql);
    if (!$res) { return false; }
    $exists = $res->num_rows > 0;
    $res->close();
    return $exists;
};

// Note: Avoid dynamic column inspection to reduce warnings; use explicit column lists

$hasLower = $tableExists($db, 'products') && $tableExists($db, 'orders');
$hasLegacy = $tableExists($db, 'PRODUCT') && $tableExists($db, 'ORDERS');
// Prefer lowercase schema when both appear due to case-insensitive table matching on some systems
$isLower = $hasLower ? true : false;
$isLegacy = $hasLower ? false : $hasLegacy;

// Calculate total from detected products table to prevent tampering
$totalAmount = 0.0;
$resolvedItems = [];
foreach ($normalizedItems as $ni) {
    $currentProdId = (int)$ni['prodId'];
    $quantity = (int)$ni['quantity'];
    $price = 0.0;

    if ($isLegacy) {
        $stmt = $db->prepare('SELECT prodPrice FROM PRODUCT WHERE prodId = ?');
        $stmt->bind_param('i', $currentProdId);
        if (!$stmt->execute()) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>$stmt->error]); exit; }
        $stmt->bind_result($price);
        $found = $stmt->fetch();
        $stmt->close();

        if (!$found && $ni['name']) {
            // Try resolve by name in legacy table
            $name = trim(preg_replace('/\s*\([^\)]*\)\s*$/', '', $ni['name']));
            $stmt = $db->prepare('SELECT prodId, prodPrice FROM PRODUCT WHERE prodName = ? LIMIT 1');
            $stmt->bind_param('s', $name);
            if ($stmt->execute()) { $newId = 0; $stmt->bind_result($newId, $price); $found = $stmt->fetch(); }
            $stmt->close();
            if ($found) { $currentProdId = (int)$newId; }
        }
    } else {
        // lowercase schema
        $stmt = $db->prepare('SELECT price FROM products WHERE product_id = ?');
        $stmt->bind_param('i', $currentProdId);
        if (!$stmt->execute()) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>$stmt->error]); exit; }
        $stmt->bind_result($price);
        $found = $stmt->fetch();
        $stmt->close();

        if (!$found && $ni['name']) {
            // Try resolve by product_name
            $name = trim(preg_replace('/\s*\([^\)]*\)\s*$/', '', $ni['name']));
            $stmt = $db->prepare('SELECT product_id, price FROM products WHERE product_name = ? LIMIT 1');
            $stmt->bind_param('s', $name);
            if ($stmt->execute()) { $newId = 0; $stmt->bind_result($newId, $price); $found = $stmt->fetch(); }
            $stmt->close();
            if ($found) { $currentProdId = (int)$newId; }
            // Case-insensitive LIKE fallback
            if (!$found) {
                $like = '%' . $name . '%';
                $stmt = $db->prepare('SELECT product_id, price FROM products WHERE LOWER(product_name) LIKE LOWER(?) LIMIT 1');
                $stmt->bind_param('s', $like);
                if ($stmt->execute()) { $newId = 0; $stmt->bind_result($newId, $price); $found = $stmt->fetch(); }
                $stmt->close();
                if ($found) { $currentProdId = (int)$newId; }
            }
            // Hard mapping fallback
            if (!$found) {
                $lc = strtolower($name);
                if (isset($PRODUCT_NAME_TO_ID[$lc])) {
                    $currentProdId = (int)$PRODUCT_NAME_TO_ID[$lc];
                    // fetch price for mapped id
                    $stmt = $db->prepare('SELECT price FROM products WHERE product_id = ?');
                    $stmt->bind_param('i', $currentProdId);
                    if ($stmt->execute()) { $stmt->bind_result($price); $found = $stmt->fetch(); }
                    $stmt->close();
                }
            }
        }
    }

    if (!$found) {
        http_response_code(400);
        $errName = isset($ni['name']) ? $ni['name'] : null;
        echo json_encode(['ok' => false, 'error' => 'Product not found: ' . $ni['prodId'], 'name' => $errName]);
        exit;
    }

    $totalAmount += ((float)$price) * $quantity;
    $resolvedItems[] = ['prodId' => $currentProdId, 'quantity' => $quantity, 'name' => $ni['name']];
}

$db->begin_transaction();
try {
    if ($isLegacy) {
        $orderDate = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d');
        $stmt = $db->prepare('INSERT INTO ORDERS (customerId, orderManagerId, orderDate, deliveryAddress, totalAmount, orderStatus) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('iissds', $customerId, $orderManagerId, $orderDate, $deliveryAddress, $totalAmount, $orderStatus);
        if (!$stmt->execute()) { throw new Exception($stmt->error); }
        $orderId = $stmt->insert_id;
        $stmt->close();
    } else {
        // Lowercase schema explicit columns
        $now = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s');
        $orderType = 'online';
        $paymentMethodVal = isset($payload['paymentMethod']) ? $payload['paymentMethod'] : null;
        $stmt = $db->prepare('INSERT INTO orders (customer_id, total_amount, status, delivery_address, payment_method, order_type, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        if (!$stmt) { throw new Exception($db->error); }
        $stmt->bind_param('idssssss', $customerId, $totalAmount, $orderStatus, $deliveryAddress, $paymentMethodVal, $orderType, $now, $now);
        if (!$stmt->execute()) { throw new Exception($stmt->error); }
        $orderId = $stmt->insert_id;
        $stmt->close();
    }

    $cashierId = isset($payload['cashierId']) ? (int)$payload['cashierId'] : 1;
    $paymentMethod = isset($payload['paymentMethod']) ? $payload['paymentMethod'] : null;

    foreach ($resolvedItems as $ni) {
        $prodId = $ni['prodId'];
        $quantity = $ni['quantity'];

        if ($isLegacy) {
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
        } else {
            // lowercase schema explicit insert
            $stmt = $db->prepare('SELECT price FROM products WHERE product_id = ?');
            $stmt->bind_param('i', $prodId);
            if (!$stmt->execute()) { throw new Exception($stmt->error); }
            $price = 0.0; $stmt->bind_result($price);
            if (!$stmt->fetch()) { throw new Exception('Product not found during insert'); }
            $stmt->close();

            // Some schemas don't have an 'amount' column; store per-item price only
            $stmt = $db->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
            if (!$stmt) { throw new Exception($db->error); }
            $stmt->bind_param('iiid', $orderId, $prodId, $quantity, $price);
            if (!$stmt->execute()) { throw new Exception($stmt->error); }
            $stmt->close();
        }
    }

    $db->commit();
    echo json_encode(['ok' => true, 'orderId' => $orderId, 'totalAmount' => $totalAmount]);
} catch (Exception $e) {
    $db->rollback();
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

?>


