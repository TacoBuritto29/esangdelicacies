<?php
// public/api/profile.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/esang_delicacies/app/views/_bootstrap.php';
json_headers_local();

if (!isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$role = strtolower($_SESSION['role']);
$roles = [
    'admin' => ['table' => 'ADMIN', 'id' => 'adminId', 'fields' => ['name', 'password']],
    'cashier' => ['table' => 'CASHIER', 'id' => 'cashierId', 'fields' => ['name', 'password']],
    'order_manager' => ['table' => 'ORDER_MANAGER', 'id' => 'orderManagerId', 'fields' => ['name', 'password']],
    'customer' => ['table' => 'CUSTOMER', 'id' => 'customerId', 'fields' => ['name', 'email', 'password']],
    // Rider intentionally omitted
];

if (!isset($roles[$role])) {
    echo json_encode(['success' => false, 'error' => 'Profile editing not allowed for this role.']);
    exit;
}

$table = $roles[$role]['table'];
$idField = $roles[$role]['id'];
$userId = $_SESSION[$idField];
$fields = $roles[$role]['fields'];
$mysqli = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $fieldList = implode(', ', $fields);
    $stmt = $mysqli->prepare("SELECT $fieldList FROM $table WHERE {$idField} = ? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();
    $stmt->close();
    echo json_encode(['success' => true, 'profile' => $profile]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [];
    $params = [];
    $types = '';
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $updates[] = "$field = ?";
            $params[] = $_POST[$field];
            $types .= 's';
        }
    }
    if (empty($updates)) {
        echo json_encode(['success' => false, 'error' => 'No fields to update.']);
        exit;
    }
    $params[] = $userId;
    $types .= 'i';
    $sql = "UPDATE $table SET " . implode(', ', $updates) . " WHERE {$idField} = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $ok = $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => $ok]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
