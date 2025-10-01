<?php
require_once __DIR__ . '/../_bootstrap.php';
// Handle payment + receipt upload (keeps backend tables/logic intact)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_payment'])) {
    $db = db();
    $cashierId = 1; // Example static assignment (replace with session if available)
    $orderId = (int)($_POST['orderId'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $paymentMethod = trim($_POST['paymentMethod'] ?? '');
    $isFullPayment = isset($_POST['isFullPayment']) ? 1 : 0;

    if ($orderId > 0 && $amount > 0 && $paymentMethod !== '') {
        // Insert payment (preserves original table/columns)
        $stmt = $db->prepare('INSERT INTO PAYMENTS (orderId, cashierId, paymentDate, amount, paymentMethod, isFullPayment) VALUES (?, ?, NOW(), ?, ?, ?)');
        $stmt->bind_param('ii d s i', $orderId, $cashierId, $amount, $paymentMethod, $isFullPayment);
        // Workaround for strict signature spacing
        $stmt->bind_param('iidsi', $orderId, $cashierId, $amount, $paymentMethod, $isFullPayment);
        $stmt->execute();
        $stmt->close();

        // Handle receipt upload
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/esang_delicacies/public/uploads/receipts/';
            if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
            $fileName = basename($_FILES['receipt']['name']);
            $targetPath = $uploadDir . $fileName;
            @move_uploaded_file($_FILES['receipt']['tmp_name'], $targetPath);

            $fileType = $_FILES['receipt']['type'];
            $publicPath = '/esang_delicacies/public/uploads/receipts/' . $fileName;

            $stmtR = $db->prepare('INSERT INTO RECEIPTS (orderId, cashierId, fileName, filePath, fileType) VALUES (?, ?, ?, ?, ?)');
            $stmtR->bind_param('iisss', $orderId, $cashierId, $fileName, $publicPath, $fileType);
            $stmtR->execute();
            $stmtR->close();
        }

        $payment_message = 'Payment and receipt saved successfully!';
    } else {
        $payment_message = 'Please fill out all required payment fields.';
    }
}

// Fetch recent orders for the current customer to populate the selector
$orders_for_select = [];
try {
    $db = db();
    $customerId = $_SESSION['customerId'] ?? 0;
    if ($customerId) {
        $q = $db->prepare('SELECT order_id, total_amount, status FROM orders WHERE customer_id = ? ORDER BY order_id DESC LIMIT 20');
        $q->bind_param('i', $customerId);
        $q->execute();
        $res = $q->get_result();
        while ($row = $res->fetch_assoc()) { $orders_for_select[] = $row; }
        $q->close();
    }
} catch (Throwable $e) { /* silent fallback */ }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
    <link rel="stylesheet" href="../VCSS/Common_CSS/sidebar.css">
    <link rel="stylesheet" href="../VCSS/Customer_CSS/customer_billing.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-------Customer Sidenav-------->
    <div class="sidenav" id="mySidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <div class="profile">
            <div class="profile-pic">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-name">
                <span id="userNameDisplay"><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Customer'; ?></span>
            </div>
        </div>
        <a href="customer_dashboard.php" class="sidenav-item"><i class="fas fa-home"></i> Dashboard</a>
        <a href="orders.php" class="sidenav-item"><i class="fas fa-cart-shopping"></i> Orders</a>
        <a href="customer_billing.php" class="sidenav-item"><i class="fas fa-credit-card"></i> Invoices</a>
        <a href="feedback.php" class="sidenav-item"><i class="fas fa-comment"></i> Feedback</a>
        <a href="customer_order_status.php" class="sidenav-item"><i class="fas fa-clipboard-list"></i> Order Status</a>
        <a href="order_history.php" class="sidenav-item"><i class="fas fa-clock-rotate-left"></i> Order History</a>
        <a href="customer_profile.php" class="sidenav-item"><i class="fas fa-user-circle"></i> Profile</a>
        <a href="#" class="sidenav-item" id="logoutLink"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>

    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Log Out</h2>
            <p>Are you sure you want to log out?</p>
            <div class="modal-actions">
                <button id="cancelLogout" class="button cancel">Cancel</button>
                <button id="confirmLogout" class="button logout">Log Out</button>
            </div>
        </div>
    </div>

    <main class="billing-container">
        <span class="openbtn" onclick="openNav()">&#9776;</span>
        <!-- Header with Invoices and Payments dropdown -->
        <header class="header-section">
            <button class="btn btn-light rounded shadow-small">Invoices</button>
            <!-- Payments Dropdown -->
            <div class="dropdown-container">
                <button class="btn btn-light rounded shadow-small" type="button" id="paymentsDropdown">Payments</button>
                <ul class="dropdown-list">
                    <li><button class="dropdown-link" type="button" data-payment="cod">COD</button></li>
                    <li><button class="dropdown-link" type="button" data-payment="gcash">GCash</button></li>
                    <li><button class="dropdown-link" type="button" data-payment="metrobank">MetroBank</button></li>
                </ul>
            </div>
        </header>

        <!-- Dynamic Content Area -->
        <section id="payment-forms-container" class="margin-top-large">
            <!-- Instructions message visible by default -->
            <div id="instruction-message" class="text-center text-muted">
                <p>Please select a payment method from the "Payments" menu to proceed.</p>
            </div>

            <!-- COD Form -->
            <div id="form-cod" class="payment-form hidden">
                <h2 class="fs-4 font-bold margin-bottom-small">Cash on Delivery</h2>
                <div class="margin-bottom-small">
                    <label for="cod-address" class="form-label font-bold required">Address:</label>
                    <input type="text" id="cod-address" class="form-input" readonly>
                </div>
            </div>

            <!-- GCash Form -->
            <div id="form-gcash" class="payment-form hidden">
                <h2 class="fs-4 font-bold margin-bottom-small">GCash</h2>
                
                <!-- Phone Number Field -->
                <div class="margin-bottom-small">
                    <label for="gcash-phone-number" class="form-label font-bold required">Phone Number(+63):</label>
                    <input type="text" class="form-input" id="gcash-phone-number" placeholder="Enter your phone number">
                </div>
                
                <!-- Full Name Field -->
                <div class="margin-bottom-small">
                    <label for="gcash-full-name" class="form-label font-bold required">Full Name:</label>
                    <input type="text" class="form-input" id="gcash-full-name" placeholder="Enter your name">
                </div>

                <!-- Reference Number Field -->
                <div class="margin-bottom-small">
                    <label for="gcash-reference-number" class="form-label font-bold required">Reference Number:</label>
                    <input type="text" class="form-input" id="gcash-reference-number" placeholder="Enter your reference number">
                </div>
                
                <!-- Photo Upload Field -->
                <div class="margin-bottom-small">
                    <label for="gcash-photo-upload" class="form-label font-bold required">Photo:</label>
                    <input type="file" id="gcash-photo-upload" style="display: none;" accept="image/*">
                    <label for="gcash-photo-upload" class="btn btn-light rounded shadow-small padding-y-medium padding-x-medium flex align-items-center">
                        <svg class="margin-right-small" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        attached photo
                    </label>
                </div>
                
                <button class="btn btn-primary width-full rounded shadow-small padding-y-medium">Confirm Payment</button>
            </div>

            <!-- MetroBank Form -->
            <div id="form-metrobank" class="payment-form hidden">
                <h2 class="fs-4 font-bold margin-bottom-small">MetroBank</h2>
                
                <!-- Full Name Field -->
                <div class="margin-bottom-small">
                    <label for="metrobank-full-name" class="form-label font-bold required">Full Name:</label>
                    <input type="text" class="form-input" id="metrobank-full-name" placeholder="Enter your name">
                </div>

                <!-- Reference Number Field -->
                <div class="margin-bottom-small">
                    <label for="metrobank-reference-number" class="form-label font-bold required">Reference Number:</label>
                    <input type="text" class="form-input" id="metrobank-reference-number" placeholder="Enter your reference number">
                </div>
                
                <!-- Photo Upload Field -->
                <div class="margin-bottom-small">
                    <label for="metrobank-photo-upload" class="form-label font-bold required">Photo:</label>
                    <input type="file" id="metrobank-photo-upload" style="display: none;" accept="image/*">
                    <label for="metrobank-photo-upload" class="btn btn-light rounded shadow-small padding-y-medium padding-x-medium flex align-items-center">
                        <svg class="margin-right-small" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        attached photo
                    </label>
                </div>
                
                <button class="btn btn-primary width-full rounded shadow-small padding-y-medium">Confirm Payment</button>
            </div>

            <!-- Cashier Payment + Receipt Upload (keeps backend table names) -->
            <div class="payment-form margin-top-large">
                <h2 class="fs-4 font-bold margin-bottom-small">Manual Payment Entry</h2>
                <?php if (!empty($payment_message)): ?>
                    <p style="color:#0ea5e9; padding: .5rem 0;"><?php echo htmlspecialchars($payment_message); ?></p>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data" style="display:grid; gap:.75rem; max-width: 32rem;">
                    <input type="hidden" name="save_payment" value="1">
                    <label>Order:
                        <select name="orderId" required>
                            <option value="">-- Select Order --</option>
                            <?php foreach ($orders_for_select as $o): ?>
                                <option value="<?php echo (int)$o['order_id']; ?>">#<?php echo (int)$o['order_id']; ?> — ₱<?php echo number_format((float)$o['total_amount'], 2); ?> (<?php echo htmlspecialchars($o['status']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Amount:
                        <input type="number" name="amount" step="0.01" min="0" required>
                    </label>
                    <label>Payment Method:
                        <select name="paymentMethod" required>
                            <option value="GCash">GCash</option>
                            <option value="COD">Cash on Delivery</option>
                            <option value="PayMaya">PayMaya</option>
                            <option value="Bank">Bank</option>
                        </select>
                    </label>
                    <label>
                        <input type="checkbox" name="isFullPayment" value="1"> Full Payment
                    </label>
                    <label>Receipt (image/pdf):
                        <input type="file" name="receipt" accept="image/*,application/pdf">
                    </label>
                    <button type="submit" class="btn btn-primary width-full rounded shadow-small padding-y-medium">Save Payment</button>
                </form>
            </div>
        </section>
    </main>
    <script>
        // Pass customer information to JavaScript
        window.customerId = <?php echo $customerId; ?>;
        window.customerData = {
            id: <?php echo $customerId; ?>,
            name: '<?php echo addslashes($_SESSION['user_name'] ?? 'Customer'); ?>'
        };
    </script>
    <script src="../VJavaScript/sidebar.js"></script>
    <script src="../VJavaScript/customer_billing.js"></script>
</body>
</html>
