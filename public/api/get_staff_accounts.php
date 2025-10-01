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
    $staffAccounts = [];

    // Get admin accounts
    $stmt = $mysqli->prepare("SELECT empId, name, email, phoneNum, status, verified, created_at FROM admin ORDER BY empId");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $staffAccounts[] = [
            'id' => $row['empId'],
            'username' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phoneNum'],
            'role' => 'admin',
            'status' => $row['status'],
            'verified' => $row['verified'],
            'created_at' => $row['created_at']
        ];
    }
    $stmt->close();

    // Get cashier accounts
    $stmt = $mysqli->prepare("SELECT empId, name, email, phone, status, verified, created_at FROM cashier ORDER BY empId");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $staffAccounts[] = [
            'id' => $row['empId'],
            'username' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'role' => 'cashier',
            'status' => $row['status'],
            'verified' => $row['verified'],
            'created_at' => $row['created_at']
        ];
    }
    $stmt->close();

    // Get rider accounts
    $stmt = $mysqli->prepare("SELECT empId, name, email, phone, plateNum, status, verified, created_at FROM rider ORDER BY empId");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $staffAccounts[] = [
            'id' => $row['empId'],
            'username' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'plateNum' => $row['plateNum'],
            'role' => 'rider',
            'status' => $row['status'],
            'verified' => $row['verified'],
            'created_at' => $row['created_at']
        ];
    }
    $stmt->close();

    // Get order manager accounts
    $stmt = $mysqli->prepare("SELECT empId, name, email, phoneNum, status, verified, created_at FROM order_manager ORDER BY empId");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $staffAccounts[] = [
            'id' => $row['empId'],
            'username' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phoneNum'],
            'role' => 'order_manager',
            'status' => $row['status'],
            'verified' => $row['verified'],
            'created_at' => $row['created_at']
        ];
    }
    $stmt->close();

    echo json_encode(['success' => true, 'data' => $staffAccounts]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
