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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$role = $input['role'] ?? '';
$id = $input['id'] ?? '';
$newStatus = $input['status'] ?? '';

if (empty($role) || empty($id) || empty($newStatus)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Validate status
if (!in_array($newStatus, ['active', 'inactive'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $mysqli = Database::getConnection();
    $tableName = '';
    
    // Map role to table name
    switch ($role) {
        case 'admin':
            $tableName = 'admin';
            break;
        case 'cashier':
            $tableName = 'cashier';
            break;
        case 'rider':
            $tableName = 'rider';
            break;
        case 'order_manager':
            $tableName = 'order_manager';
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid role']);
            exit;
    }

    // Update the status
    $stmt = $mysqli->prepare("UPDATE {$tableName} SET status = ? WHERE empId = ?");
    $stmt->bind_param('si', $newStatus, $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Log the activity
            $adminId = $_SESSION['adminId'] ?? 1;
            $description = "Admin {$adminId} changed {$role} account {$id} status to {$newStatus}";
            
            $logStmt = $mysqli->prepare("INSERT INTO security_log (user_id, event_type, description, severity, ip_address, user_agent) VALUES (?, 'account_status_change', ?, 'medium', ?, ?)");
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $logStmt->bind_param('isss', $adminId, $description, $ipAddress, $userAgent);
            $logStmt->execute();
            $logStmt->close();
            
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No account found with that ID']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $mysqli->error]);
    }
    
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
