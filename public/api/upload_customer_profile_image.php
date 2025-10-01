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

// Check if file was uploaded
if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No image file uploaded or upload error']);
    exit;
}

$file = $_FILES['profile_image'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed']);
    exit;
}

// Validate file size (max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB']);
    exit;
}

try {
    // Create upload directory if it doesn't exist
    $uploadDir = __DIR__ . '/../../public/Images/profiles/customers/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $customerId = $_SESSION['customerId'];
    $filename = 'customer_' . $customerId . '_' . time() . '_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Remove old profile image if exists
    $mysqli = Database::getConnection();
    $stmt = $mysqli->prepare("SELECT profile_image FROM CUSTOMER WHERE customerId = ?");
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $oldProfile = $result->fetch_assoc();
    $stmt->close();
    
    if ($oldProfile && $oldProfile['profile_image']) {
        $oldFilepath = $uploadDir . basename($oldProfile['profile_image']);
        if (file_exists($oldFilepath)) {
            unlink($oldFilepath);
        }
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Return just the filename (not full path) for database storage
        echo json_encode(['success' => true, 'image_url' => $filename]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Upload error: ' . $e->getMessage()]);
}
?>