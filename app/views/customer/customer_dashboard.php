<?php
require_once __DIR__ . '/../_bootstrap.php';

// Check if user is logged in as customer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header('Location: ../auth/LogIn.php');
    exit;
}

// Get user name for display
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Customer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
    <link rel="stylesheet" href="../VCSS/Customer_CSS/customer_dashboard.css">
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
            <div class="profile-name"><span id="userNameDisplay"><?php echo htmlspecialchars($userName); ?></span></div>
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


    <div class="main-content">
        <div class="top-header">
            <span class="openbtn" onclick="openNav()">&#9776;</span>
            <div class="greeting">
                <h2>Hello, <span style="color: #d9534f;" id="greetingName"><?php echo htmlspecialchars($userName); ?></span>! <br>What would you like to order today?</h2>
            </div>
            <div class="header-icons">
                <div class="search-bar">
                    <input type="text" placeholder="Search...">
                    <i class="fas fa-search"></i>
                </div>
                <div class="icon-link" id="notificationBell">
                    <i class="fas fa-bell"></i>
                    <span id="notification-count" class="badge">0</span>
                </div>
                <div class="icon-link" id="cartButton">
                    <i class="fas fa-shopping-cart"></i>
                    <span id="cart-count" class="badge">0</span>
                </div>
            </div>
        </div>

        <div class="menu-contents">
            <div class="menu-tab" id="menuTabs">
                </div>

            <div id="menuGrids">
                </div>
        </div>
    </div>

    <div id="cartModal" class="modal">
        <div class="modal-content">
            <span class="close-button" id="closeCartModal">&times;</span>
            <h2><i class="fas fa-shopping-cart"></i> Your Cart</h2>
            <div id="cart-items-container">
                </div>
            <div class="cart-summary">
                <p>Total: <span id="cart-total">₱0.00</span></p>
                <button class="add-button" id="checkoutButton">Checkout</button>
            </div>
        </div>
    </div>

    <div id="notification-container">
        </div>
    
    <script src="../VJavaScript/customer_dashboard.js"></script>
    <script src="../VJavaScript/sidebar.js"></script>
</body>
</html>