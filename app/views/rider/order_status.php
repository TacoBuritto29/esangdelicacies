<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
    <link rel="stylesheet" href="../VCSS/Common_CSS/sidebar.css">
    <link rel="stylesheet" href="../VCSS/Rider_CSS/OS.css">
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
        <div class="container">
            <div class="customer-info">
                <div class="info-row">
                    <span class="customer-icon"><i class="fas fa-user"></i></span>
                    <span class="customer-name">Insert name</span>
                </div>
                <div class="info-details">
                    <p><b>Address: </b><span></span></p>
                    <p><b>Phone Number: </b><span></span></p>
                    <p><b>Quantity: </b><span>0</span></p>
                </div>
                <div class="total-amount">
                    Total Amount: ₱ <span>0.0</span>
                </div>
            </div>
            <div class="progressbar">
                <div class="progress" id="progress"></div>
                <div class="progress-step active" data-title="Order Placed">
                    <i class="fas fa-box"></i>
                </div>
                <div class="progress-step" data-title="Need Approval">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="progress-step" data-title="Order is being prepared">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="progress-step" data-title="Order is ready to pick up">
                    <i class="fas fa-truck-pickup"></i>
                </div>
                <div class="progress-step" data-title="Delivered">
                    <i class="fas fa-home"></i>
                </div>
            </div>
        </div>
    </div>
    <script src="../VJavaScript/sidebar.js"></script>
</body>
</html>