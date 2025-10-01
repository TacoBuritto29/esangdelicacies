<?php
require_once __DIR__ . '/../../app/config/database.php';

json_headers();
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$db = Database::getConnection();

$query = "SELECT prodId, prodName, prodCategory, prodPrice FROM PRODUCT ORDER BY prodCategory, prodName";
$result = $db->query($query);
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

?>


