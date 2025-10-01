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

try {
    $mysqli = Database::getConnection();
    
    // Get user role from session
    $userRole = $_SESSION['role'];
    
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
    if (isset($roleSessionMap[$userRole]) && isset($_SESSION[$roleSessionMap[$userRole]])) {
        $userId = $_SESSION[$roleSessionMap[$userRole]];
    } else if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    // Prepare query based on user ID
    if ($userId) {
        // If we have a specific user ID, use it
        $stmt = $mysqli->prepare("
            SELECT 
                user_id,
                first_name,
                last_name,
                email,
                phone_number,
                profile_image,
                created_at,
                last_login,
                user_type
            FROM users 
            WHERE user_id = ?
        ");
        $stmt->bind_param('i', $userId);
    } else {
        // Otherwise use the role to find the user
        $stmt = $mysqli->prepare("
            SELECT 
                user_id,
                first_name,
                last_name,
                email,
                phone_number,
                profile_image,
                created_at,
                last_login,
                user_type
            FROM users 
            WHERE user_type = ?
            LIMIT 1
        ");
        $stmt->bind_param('s', $userRole);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Store user_id in session if not already there
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['user_id'] = $row['user_id'];
        }
        
        // Store user name in session for display
        $_SESSION['user_name'] = $row['first_name'] . ' ' . $row['last_name'];
        
        echo json_encode([
            'success' => true,
            'profile' => [
                'user_id' => $row['user_id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'email' => $row['email'],
                'phone_number' => $row['phone_number'],
                'profile_image' => $row['profile_image'] ?: '',
                'created_at' => $row['created_at'],
                'last_login' => $row['last_login'],
                'user_type' => $row['user_type']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User profile not found']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
