<?php
require_once __DIR__ . '/../_bootstrap.php';

// Check if user is logged in as customer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header('Location: ../auth/LogIn.php');
    exit;
}

// Get user name and ID for display
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Customer';
$customerId = $_SESSION['customerId'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Orders</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
    <link rel="stylesheet" href="../VCSS/Customer_CSS/orders.css">
    <link rel="stylesheet" href="../VCSS/Common_CSS/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="sidenav" id="mySidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <div class="profile">
            <div class="profile-pic">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-name">
                <span id="userNameDisplay"><?php echo htmlspecialchars($userName); ?></span>
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

    <div id="invoiceModal" class="modal">
        <div class="modal-content invoice-modal-content">
            <span class="close-button close-invoice-modal">&times;</span>
            <h2>Order Invoice</h2>
            <div id="invoice-preview-area" class="invoice-preview">
                </div>
            <div class="modal-actions invoice-actions">
                <button id="print-invoice-btn" class="button print-button"><i class="fas fa-print"></i> Print</button>
                <button id="download-invoice-btn" class="button download-button"><i class="fas fa-download"></i> Download PDF</button>
            </div>
        </div>
    </div>
    <div class="main-content">
        <span class="openbtn" onclick="openNav()">&#9776;</span>
        <div class="container">
            <div class="column-card">
                <h2 class="pending-title">Pending Orders</h2>
                <div id="pending-container" class="order-container order-list"></div>
                <div class="checkout-section">
                    <div id="pending-total" class="total-price">Total: ₱0.00</div>
                    <button id="checkout-btn" class="checkout-button">Check Out</button>
                </div>
            </div>
            <div class="column-card">
                <h2 class="ongoing-title">Ongoing Orders</h2>
                <div id="ongoing-container" class="order-container order-list"></div>
                <div id="ongoing-details" class="ongoing-details-form">
                    <h3>Delivery & Payment Details</h3>
                    <form id="ongoing-form">
                        <div class="form-field">
                            <label>Payment Option:</label>
                            <div class="radio-options">
                                <div class="radio-option">
                                    <input id="payment-bank" name="payment" type="radio" value="Bank Transfer" checked class="checkbox-input">
                                    <label for="payment-bank">Bank Transfer</label>
                                </div>
                                <div class="radio-option">
                                    <input id="payment-gcash" name="payment" type="radio" value="GCash" class="checkbox-input">
                                    <label for="payment-gcash">GCash</label>
                                </div>
                                <div class="radio-option">
                                    <input id="payment-cod" name="payment" type="radio" value="Cash on Delivery" class="checkbox-input">
                                    <label for="payment-cod">Cash on Delivery</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="region">Region:</label>
                            <select id="region" name="region"></select>
                        </div>
                        <div class="form-field">
                            <label for="city">City:</label>
                            <select id="city" name="city" disabled></select>
                        </div>
                        <div class="form-field">
                            <label for="district">District:</label>
                            <select id="district" name="district" disabled></select>
                        </div>
                        <div class="form-field">
                            <label for="barangay">Barangay:</label>
                            <select id="barangay" name="barangay" disabled></select>
                        </div>
                        <button type="submit" id="place-order-btn" class="place-order-button">Place Order</button>
                    </form>
                </div>
            </div>
            <div class="column-card">
                <h2 class="completed-title">Completed Orders</h2>
                <div id="completed-container" class="order-container order-list"></div>
                <div class="completed-footer">
                    <p>All orders here have been successfully delivered.</p>
                </div>
                <!-- Invoice Modal -->
                <div id="invoiceModal" class="modal" style="display:none;">
                    <div class="modal-content" id="invoiceContent">
                        <span class="close-button" id="closeInvoice">&times;</span>
                        <h2>Invoice</h2>
                        <div id="invoiceDetails">
                        <!-- Invoice details will be injected here -->
                        </div>
                        <div class="modal-actions">
                            <button id="downloadInvoice" class="button">Download PDF</button>
                            <button id="printInvoice" class="button">Print</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Pass user information to JavaScript
        window.customerData = {
            id: <?php echo $customerId; ?>,
            name: '<?php echo addslashes($userName); ?>'
        };
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="../VJavaScript/sidebar.js"></script>
    <script src="../VJavaScript/orders.js"></script>
</body>
</html>