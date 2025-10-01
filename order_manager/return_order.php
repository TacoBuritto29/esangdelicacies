<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Order</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
    <link rel="stylesheet" href="../VCSS/Common_CSS/sidebar.css">
    <link rel="stylesheet" href="../VCSS/Order_Manager_CSS/return_order.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="sidenav" id="mySidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <div class="profile">
            <div class="profile-pic">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-name"><span>Order Manager</span></div>
        </div>
        <a href="order_management.php" class="sidenav-item"><i class="fas fa-th-large"></i>Order Management</a>
        <a href="inventory.php" class="sidenav-item"><i class="fas fa-receipt"></i>Inventory Management</a>
        <a href="return_order.php" class="sidenav-item"><i class="fa-solid fa-arrow-rotate-left"></i>Return Order</a>
        <a href="return_management.php" class="sidenav-item"><i class="fa-solid fa-arrow-rotate-left"></i>Return Management</a>
        <a href="order_manager_status.php" class="sidenav-item"><i class="fas fa-clipboard-list"></i> Order Status</a>
        <a href="order_manager_profile.php" class="sidenav-item"><i class="fas fa-user-circle"></i> Profile</a>
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
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Order Name</th>
                        <th>Quantity</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Payment Method</th>
                        <th>Proof of Payment</th>
                        <th>#RN</th>
                        <th>Proof of Refund</th>
                        <th>Reason</th>
                        <th>Refund Method</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="return-orders-body"></tbody>
            </table>
        </div>
    </div>

    <div id="confirmationModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalTitle">Confirm Action</h5>
                <button type="button" class="btn-close confirmation-close-btn" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmationMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary confirmation-cancel-btn">Cancel</button>
                <button type="button" class="btn btn-primary confirmation-confirm-btn">Confirm</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="proof-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Proof of Payment/Refund</h5>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="modal-image" src="" alt="Proof" style="width: 100%; height: auto; display: block;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary">Close</button>
            </div>
        </div>
    </div>

    <script src="../VJavaScript/sidebar.js"></script>
    <script src="../VJavaScript/return_order.js"></script>
</body>
</html>