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

session_start();
json_headers();

// Check if user is logged in as customer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $mysqli = Database::getConnection();
    $customerId = $_SESSION['customerId'];
    
    $stmt = $mysqli->prepare("
        SELECT 
            customerId,
            first_name,
            last_name,
            name,
            email,
            phone_number,
            phone,
            address,
            profile_image,
            created_at,
            updated_at,
            status
        FROM CUSTOMER 
        WHERE customerId = ? 
        LIMIT 1
    ");
    
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();
    $stmt->close();
    
    if ($profile) {
        // Convert profile image path to full URL if it exists
        if ($profile['profile_image']) {
            $profile['profile_image'] = '/esang_delicacies/public/Images/profiles/customers/' . $profile['profile_image'];
        }
        
        echo json_encode(['success' => true, 'profile' => $profile]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Profile not found']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>