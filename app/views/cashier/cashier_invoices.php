<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Invoices</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
    <link rel="stylesheet" href="../VCSS/Cashier_CSS/cashier_invoices.css">
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
            <h1 id="welcome-greeting">Invoices</h1>
        </div>
        <div class="header-right">
            <span id="current-date"></span>
        </div>
    </div>

    <main>
        <div class="invoices-header">
            <div class="filter-buttons">
                <button class="filter-btn active" data-period="daily">Daily</button>
                <button class="filter-btn" data-period="monthly">Monthly</button>
                <button class="filter-btn" data-period="yearly">Yearly</button>
            </div>
            <button id="delete-selected-btn" class="delete-btn" disabled>Delete</button>
        </div>
        <div id="invoices-container">
            </div>
    </main>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Delete Invoices</h2>
            <p>Are you sure you want to delete the selected invoices? This action cannot be undone.</p>
            <div class="modal-actions">
                <button id="cancelDelete" class="button cancel">Cancel</button>
                <button id="confirmDelete" class="button delete">Delete</button>
            </div>
        </div>
    </div>
    <script src="../VJavaScript/cashier_invoices.js"></script>
</body>
</html>