<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Assignment</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
    <link rel="stylesheet" href="../VCSS/Common_CSS/sidebar.css">
    <link rel="stylesheet" href="../VCSS/Rider_CSS/OA.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="sidenav" id="mySidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <div class="profile">
            <div class="profile-pic">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-name"><span>Rider name</span></div>
        </div>
        <a href="order_assignments.php" class="sidenav-item"><i class="fas fa-th-large"></i> Order Assignments</a>
        <a href="order_status.php" class="sidenav-item"><i class="fas fa-clipboard-list"></i> Order Status</a>
        <a href="rider_profile.php" class="sidenav-item"><i class="fas fa-user-circle"></i> Profile</a>
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
        <div class="page-header">
            <i class="fas fa-utensils"></i>
            <h2>Order Assignments</h2>
        </div>

        <div class="order-assignments-list">
            <div class="order-card">
                <div class="customer-info-section">
                    <div class="customer-profile-pic">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="customer-details">
                        <p class="customer-name"><b>Name: </b><span></span></p>
                        <p class="customer-address"><b>Address: </b><span></span></p>
                        <p class="customer-phone"><b>Phone Number: </b><span></span></p>
                    </div>
                </div>
                <div class="order-summary-section">
                    <p class="order-quantity"><b>Quantity: </b><span>0</span></p>
                    <p class="order-total-amount">Total Amount: ₱ <span>0.0</span></p>
                </div>
            </div>

            <div class="order-card">
                <div class="customer-info-section">
                    <div class="customer-profile-pic">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="customer-details">
                        <p class="customer-name"><b>Name: </b></p>
                        <p class="customer-address"><b>Address: </b></p>
                        <p class="customer-phone"><b>Phone Number: </b></p>
                    </div>
                </div>
                <div class="order-summary-section">
                    <p class="order-quantity"><b>Quantity: </b>0</p>
                    <p class="order-total-amount">Total Amount: ₱ 0.0</p>
                </div>
            </div>
        </div>
    </div>
    <script src="../VJavaScript/sidebar.js"></script>
</body>
</html>