<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Walk-In</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
    <link rel="stylesheet" href="../VCSS/Cashier_CSS/cashier_walk_in.css">
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
            <div class="profile-name"><span>Cashier</span></div>
        </div>
        <a href="cashier_walk_in.php" class="sidenav-item"><i class="fas fa-home"></i> Dashboard</a>
        <a href="cashier_invoices.php" class="sidenav-item"><i class="fas fa-receipt"></i> Invoices</a>
        <a href="cashier_profile.php" class="sidenav-item"><i class="fas fa-user-circle"></i> Profile</a>
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

    <div class="header">
        <div class="header-left">
            <h1 id="welcome-greeting"></h1>
            <input type="text" id="search-bar" placeholder="Search menu items...">
        </div>
        <div class="header-right">
            <span id="current-date"></span>
            <button class="all-menu-button">All Menu</button>
        </div>
    </div>

    <main>
        <div class="main-container">
            <div class="menu-contents">
                <!-- Dynamic menu tabs (categories) -->
                <div class="menu-tab" id="menuTabs"></div>

                <!-- Dynamic menu grids (items) -->
                <div id="menuContents"></div>
            </div>

            <div class="order-summary-panel">
                <div class="summary-header">
                    <h2>Order Summary</h2>
                    <button class="clear-order-button">Clear Order</button>
                </div>
                <div id="order-list"></div>

                <div class="payment-summary">
                    <div class="total-summary">
                        <span>Total:</span>
                        <span id="total-price">₱0.00</span>
                    </div>
                    <div class="payment-methods">
                        <button class="payment-button active" data-method="cash">Cash</button>
                        <button class="payment-button" data-method="gcash">GCash</button>
                        <button class="payment-button" data-method="split">Split Payment</button>
                    </div>
                    <div id="payment-inputs">
                        <div id="cash-payment" class="payment-input-group active">
                            <label for="cash-amount">Cash Amount:</label>
                            <input type="number" id="cash-amount" placeholder="Enter cash amount">
                            <p class="change-due">Change due: <span id="cash-change">₱0.00</span></p>
                        </div>
                        <div id="gcash-payment" class="payment-input-group">
                            <label for="gcash-amount">GCash Amount:</label>
                            <input type="number" id="gcash-amount" placeholder="Enter GCash amount">
                        </div>
                        <div id="split-payment" class="payment-input-group">
                            <label for="split-cash-amount">Cash Amount:</label>
                            <input type="number" id="split-cash-amount" placeholder="Enter cash amount">
                            <label for="split-gcash-amount">GCash Amount:</label>
                            <input type="number" id="split-gcash-amount" placeholder="Enter GCash amount">
                        </div>
                    </div>
                    <button id="confirm-payment-button">Confirm Payment</button>
                </div>

                <div class="receipt-preview">
                    <h2>Receipt Preview</h2>
                    <div id="receipt-content"></div>
                    <button id="download-receipt-button">Download Receipt</button>
                </div>
            </div>
        </div>
    </main>

    <script src="../VJavaScript/cashier_walk_in.js"></script>
</body>
</html>
