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
    $analytics = [];

    // Weekly Analytics (last 4 weeks)
    $weeklyData = [];
    for ($i = 3; $i >= 0; $i--) {
        $startDate = date('Y-m-d', strtotime("-$i weeks monday"));
        $endDate = date('Y-m-d', strtotime("-$i weeks sunday"));
        
        $stmt = $mysqli->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as total
            FROM orders 
            WHERE payment_status IN ('paid', 'paid_pending_verification')
            AND DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt->bind_param('ss', $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $weeklyData[] = (float)$row['total'];
        $stmt->close();
    }

    // Monthly Analytics (last 12 months)
    $monthlyData = [];
    $monthlyLabels = [];
    for ($i = 11; $i >= 0; $i--) {
        $monthStart = date('Y-m-01', strtotime("-$i months"));
        $monthEnd = date('Y-m-t', strtotime("-$i months"));
        $monthLabel = date('M', strtotime("-$i months"));
        
        $monthlyLabels[] = $monthLabel;
        
        $stmt = $mysqli->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as total
            FROM orders 
            WHERE payment_status IN ('paid', 'paid_pending_verification')
            AND DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt->bind_param('ss', $monthStart, $monthEnd);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $monthlyData[] = (float)$row['total'];
        $stmt->close();
    }

    // Yearly Analytics (last 5 years)
    $yearlyData = [];
    $yearlyLabels = [];
    for ($i = 4; $i >= 0; $i--) {
        $year = date('Y', strtotime("-$i years"));
        $yearlyLabels[] = $year;
        
        $stmt = $mysqli->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as total
            FROM orders 
            WHERE payment_status IN ('paid', 'paid_pending_verification')
            AND YEAR(created_at) = ?
        ");
        $stmt->bind_param('s', $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $yearlyData[] = (float)$row['total'];
        $stmt->close();
    }

    // Overall Statistics
    $stmt = $mysqli->prepare("
        SELECT 
            COUNT(*) as total_orders,
            COALESCE(SUM(total_amount), 0) as total_revenue,
            COALESCE(AVG(total_amount), 0) as average_order_value,
            COUNT(DISTINCT customer_id) as unique_customers
        FROM orders 
        WHERE payment_status IN ('paid', 'paid_pending_verification')
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();
    $stmt->close();

    $analytics = [
        'weekly' => [
            'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            'data' => $weeklyData
        ],
        'monthly' => [
            'labels' => $monthlyLabels,
            'data' => $monthlyData
        ],
        'yearly' => [
            'labels' => $yearlyLabels,
            'data' => $yearlyData
        ],
        'statistics' => [
            'total_orders' => (int)$stats['total_orders'],
            'total_revenue' => number_format($stats['total_revenue'], 2),
            'average_order_value' => number_format($stats['average_order_value'], 2),
            'unique_customers' => (int)$stats['unique_customers']
        ]
    ];

    echo json_encode(['success' => true, 'data' => $analytics]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
