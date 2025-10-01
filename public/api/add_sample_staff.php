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
    
    // Sample staff accounts to add
    $sampleAccounts = [
        // Cashiers
        [
            'table' => 'cashier',
            'data' => [
                ['name' => 'Maria Santos', 'email' => 'maria.santos@esangdelicacies.com', 'phone' => '09123456789', 'password' => 'password123'],
                ['name' => 'Juan Cruz', 'email' => 'juan.cruz@esangdelicacies.com', 'phone' => '09123456790', 'password' => 'password123']
            ]
        ],
        // Riders
        [
            'table' => 'rider',
            'data' => [
                ['name' => 'Pedro Reyes', 'email' => 'pedro.reyes@esangdelicacies.com', 'phone' => '09123456791', 'plateNum' => 'ABC-1234', 'password' => 'password123'],
                ['name' => 'Ana Lopez', 'email' => 'ana.lopez@esangdelicacies.com', 'phone' => '09123456792', 'plateNum' => 'XYZ-5678', 'password' => 'password123']
            ]
        ],
        // Order Managers
        [
            'table' => 'order_manager',
            'data' => [
                ['name' => 'Carlos Mendoza', 'email' => 'carlos.mendoza@esangdelicacies.com', 'phone' => '09123456793', 'password' => 'password123'],
                ['name' => 'Sofia Garcia', 'email' => 'sofia.garcia@esangdelicacies.com', 'phone' => '09123456794', 'password' => 'password123']
            ]
        ]
    ];
    
    $addedCount = 0;
    
    foreach ($sampleAccounts as $accountType) {
        $table = $accountType['table'];
        
        foreach ($accountType['data'] as $account) {
            // Check if account already exists
            $checkStmt = $mysqli->prepare("SELECT COUNT(*) FROM {$table} WHERE email = ?");
            $checkStmt->bind_param('s', $account['email']);
            $checkStmt->execute();
            $checkStmt->bind_result($count);
            $checkStmt->fetch();
            $checkStmt->close();
            
            if ($count == 0) {
                // Insert new account
                if ($table === 'rider') {
                    $stmt = $mysqli->prepare("INSERT INTO {$table} (name, email, phone, plateNum, password, status, verified) VALUES (?, ?, ?, ?, ?, 'active', 1)");
                    $stmt->bind_param('sssss', $account['name'], $account['email'], $account['phone'], $account['plateNum'], $account['password']);
                } else {
                    $stmt = $mysqli->prepare("INSERT INTO {$table} (name, email, phoneNum, password, status, verified) VALUES (?, ?, ?, ?, 'active', 1)");
                    $phoneField = $table === 'cashier' ? 'phone' : 'phoneNum';
                    $stmt->bind_param('ssss', $account['name'], $account['email'], $account[$phoneField], $account['password']);
                }
                
                if ($stmt->execute()) {
                    $addedCount++;
                }
                $stmt->close();
            }
        }
    }
    
    echo json_encode(['success' => true, 'message' => "Added {$addedCount} new staff accounts"]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
