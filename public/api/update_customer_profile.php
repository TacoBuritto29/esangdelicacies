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

// Validate required fields
$firstName = trim($data['first_name'] ?? '');
$lastName = trim($data['last_name'] ?? '');
$phoneNumber = trim($data['phone_number'] ?? '');
$address = trim($data['address'] ?? '');
$profileImage = trim($data['profile_image'] ?? '');

if (empty($firstName) || empty($lastName) || empty($phoneNumber)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'First name, last name, and phone number are required']);
    exit;
}

// Validate phone number format (basic validation)
if (!preg_match('/^[0-9+\-\s()]+$/', $phoneNumber)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
    exit;
}

try {
    $mysqli = Database::getConnection();
    $customerId = $_SESSION['customerId'];
    
    $stmt = $mysqli->prepare("
        UPDATE CUSTOMER 
        SET first_name = ?, 
            last_name = ?, 
            phone_number = ?, 
            address = ?, 
            profile_image = ?,
            updated_at = NOW()
        WHERE customerId = ?
    ");
    
    $stmt->bind_param('sssssi', $firstName, $lastName, $phoneNumber, $address, $profileImage, $customerId);
    
    if ($stmt->execute()) {
        $stmt->close();
        
        // Update session data
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Profile updated successfully',
            'profile' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone_number' => $phoneNumber,
                'address' => $address,
                'profile_image' => $profileImage
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        $stmt->close();
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>