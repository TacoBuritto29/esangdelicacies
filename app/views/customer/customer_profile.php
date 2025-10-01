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
    <title>Profile Settings</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
    <link rel="stylesheet" href="../VCSS/Common_CSS/profile.css">
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
        <span class="openbtn" onclick="openNav()">&#9776;</span>
        <div class="container">
            <h1 class="title">Profile Settings</h1>
            <div class="form-group profile-photo-section">
                <label for="profilePhoto" class="required">Profile photo</label>
                <div class="profile-photo-circle" id="profilePhotoCircle">
                    <input type="file" id="profilePhoto" accept="image/*" style="display: none;">
                    <img id="profilePhotoPreview" src="#" alt="Profile Photo Review" class="hidden">
                </div>
                <p class="upload-text">Click to upload photo</p>
            </div>

            <div class="form-group">
                <label for="firstName" class="required">First Name: </label>
                <input type="text" id="firstName" placeholder="Enter your first name">
            </div>
            <div class="form-group">
                <label for="lastName" class="required">Last Name: </label>
                <input type="text" id="lastName" placeholder="Enter your last name">
            </div>
            <div class="form-group">
                <label for="address" class="required">Address: </label>
                <input type="text" id="address" placeholder="Enter your address">
            </div>
            <div class="form-group">
                <label for="phoneNumber" class="required">Phone Number(+63)</label>
                <input type="tel" id="phoneNumber" placeholder="Enter your phone number">
            </div>
            <div class="button-group">
                <button class="save-button" onclick="saveProfile()">Save Changes</button>
                <button class="cancel-button" onclick="resetProfile()">Reset</button>
            </div>
        </div>
    </div>
    <script src="../VJavaScript/sidebar.js"></script>
    <script src="../VJavaScript/customer_profile.js"></script>
</body>
</html>
