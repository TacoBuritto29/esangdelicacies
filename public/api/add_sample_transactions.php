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
    
    // First, let's check what user IDs exist in the users table
    $userStmt = $mysqli->prepare("SELECT user_id, first_name, last_name, user_type FROM users WHERE user_type = 'CUSTOMER'");
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $users = [];
    while ($row = $userResult->fetch_assoc()) {
        $users[] = $row;
    }
    $userStmt->close();
    
    // If no customer users exist, create some sample customer users first
    if (empty($users)) {
        $sampleUsers = [
            ['first_name' => 'Mary Rose', 'last_name' => 'Tacuyan', 'email' => 'maryrose@example.com', 'phone_number' => '09123456001', 'user_type' => 'CUSTOMER'],
            ['first_name' => 'Alvin Jefftey', 'last_name' => 'Delos Reyes', 'email' => 'alvin@example.com', 'phone_number' => '09123456002', 'user_type' => 'CUSTOMER'],
            ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@example.com', 'phone_number' => '09123456003', 'user_type' => 'CUSTOMER'],
            ['first_name' => 'Jane', 'last_name' => 'Smith', 'email' => 'jane@example.com', 'phone_number' => '09123456004', 'user_type' => 'CUSTOMER']
        ];
        
        foreach ($sampleUsers as $user) {
            // Check if user already exists with this email or phone
            $checkStmt = $mysqli->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR phone_number = ?");
            $checkStmt->bind_param('ss', $user['email'], $user['phone_number']);
            $checkStmt->execute();
            $checkStmt->bind_result($count);
            $checkStmt->fetch();
            $checkStmt->close();
            
            if ($count == 0) {
                $stmt = $mysqli->prepare("INSERT INTO users (first_name, last_name, email, phone_number, password_hash, user_type, status, email_verified, phone_verified) VALUES (?, ?, ?, ?, ?, ?, 'active', 1, 1)");
                $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
                $stmt->bind_param('ssssss', $user['first_name'], $user['last_name'], $user['email'], $user['phone_number'], $passwordHash, $user['user_type']);
                $stmt->execute();
                $stmt->close();
            }
        }
        
        // Re-fetch users after creating them
        $userStmt = $mysqli->prepare("SELECT user_id, first_name, last_name, user_type FROM users WHERE user_type = 'CUSTOMER'");
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $users = [];
        while ($row = $userResult->fetch_assoc()) {
            $users[] = $row;
        }
        $userStmt->close();
    }
    
    // Use the first 4 users for sample transactions
    $sampleTransactions = [];
    for ($i = 0; $i < 8; $i++) {
        $userIndex = $i % count($users);
        $sampleTransactions[] = [
            'customer_id' => $users[$userIndex]['user_id'],
            'total_amount' => rand(100, 500) + (rand(0, 99) / 100), // Random amount between 100-500
            'payment_method' => ['gcash', 'cash', 'card'][rand(0, 2)],
            'payment_status' => 'paid',
            'delivery_address' => 'Sample Address ' . ($i + 1),
            'created_at' => date('Y-m-d H:i:s', strtotime('-' . ($i + 1) . ' days'))
        ];
    }
    
    $addedCount = 0;
    
    foreach ($sampleTransactions as $transaction) {
        // Check if order already exists
        $checkStmt = $mysqli->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ? AND total_amount = ? AND created_at = ?");
        $checkStmt->bind_param('ids', $transaction['customer_id'], $transaction['total_amount'], $transaction['created_at']);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();
        
        if ($count == 0) {
            // Insert new order
            $stmt = $mysqli->prepare("INSERT INTO orders (customer_id, total_amount, status, order_type, delivery_address, payment_method, payment_status, created_at, updated_at) VALUES (?, ?, 'delivered', 'delivery', ?, ?, ?, ?, ?)");
            $updatedAt = $transaction['created_at'];
            $stmt->bind_param('idsssss', $transaction['customer_id'], $transaction['total_amount'], $transaction['delivery_address'], $transaction['payment_method'], $transaction['payment_status'], $transaction['created_at'], $updatedAt);
            
            if ($stmt->execute()) {
                $addedCount++;
            }
            $stmt->close();
        }
    }
    
    echo json_encode(['success' => true, 'message' => "Added {$addedCount} new sample transactions"]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
