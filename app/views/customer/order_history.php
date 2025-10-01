<?php
require_once __DIR__ . '/../_bootstrap.php';
require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
    <link rel="stylesheet" href="../VCSS/Common_CSS/sidebar.css">
    <link rel="stylesheet" href="../VCSS/Customer_CSS/order_history.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="sidenav" id="mySidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <div class="profile">
            <div class="profile-pic">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-name"><span>Customer Name</span></div>
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
        <span class="openbtn" onclick="openNav()">&#9776;</span>
        <div class="order-history">
        <div class="header">
            <span class="status-tag">May 12 Delivered</span>
            <div>
                <span>Ordered</span>
                <span>Delivered</span>
            </div>
        </div>
        
        <div class="order-item">
            <img src="../VImages/Full Menu/Delicacies/Turon Bites.png" alt="Turon Bites">
            <div class="item-details">
                <h2>Turon Bites</h2>
                <p>x1</p>
            </div>
            <div class="item-price">₱60.00</div>
        </div>

        <div class="order-item">
            <img src="../VImages/Full Menu/Delicacies/Yema Ube Biko.png" alt="Yema Ube Biko">
            <div class="item-details">
                <h2>Yema Ube Biko</h2>
                <p>x1</p>
            </div>
            <div class="item-price">₱60.00</div>
        </div>

        <div class="summary">
            <div class="summary-row">
                <div class="label">Sub Total:</div>
                <div class="value">₱120.00</div>
            </div>
            <div class="summary-row">
                <div class="label">Delivery fee:</div>
                <div class="value">₱30.00</div>
            </div>
            <div class="summary-row total">
                <div class="label">Total:</div>
                <div class="value">₱150.00</div>
            </div>
        </div>
    </div>
    </div>
    <script src="../VJavaScript/sidebar.js"></script>
</body>
</html>