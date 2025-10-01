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

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access - not logged in']);
    exit;
}

// Get the appropriate user ID based on role
$userId = null;
$roleSessionMap = [
    'ADMIN' => 'adminId',
    'CASHIER' => 'cashierId',
    'RIDER' => 'riderId',
    'ORDER_MANAGER' => 'orderManagerId',
    'CUSTOMER' => 'customerId'
];

// Check if we have a role-specific ID in the session
if (isset($roleSessionMap[$_SESSION['role']]) && isset($_SESSION[$roleSessionMap[$_SESSION['role']]])) {
    $userId = $_SESSION[$roleSessionMap[$_SESSION['role']]];
} else if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access - user ID not found']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON body']);
    exit;
}

$firstName = trim($data['first_name'] ?? '');
$lastName = trim($data['last_name'] ?? '');
$phoneNumber = trim($data['phone_number'] ?? '');
$profileImage = trim($data['profile_image'] ?? '');

if ($firstName === '' || $lastName === '' || $phoneNumber === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $mysqli = Database::getConnection();
    
    // Update user profile based on user_id
    $stmt = $mysqli->prepare("
        UPDATE users 
        SET first_name = ?, last_name = ?, phone_number = ?, profile_image = ?, updated_at = NOW()
        WHERE user_id = ?
    ");
    
    $stmt->bind_param('ssssi', $firstName, $lastName, $phoneNumber, $profileImage, $userId);
    
    if ($stmt->execute()) {
        // Update session with new name
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
