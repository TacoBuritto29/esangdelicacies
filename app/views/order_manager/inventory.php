<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
    <link rel="stylesheet" href="../VCSS/Order_Manager_CSS/inventory.css">
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
        <span class="openbtn" onclick="openNav()">&#9776;</span>
        <div class="controls">
            <input type="text" id="searchBar" placeholder="Search for items...">
            <select id="categoryFilter">
                <option value="all">All Categories</option>
                <!-- Categories will be populated from the database -->
            </select>
            <div class="view-buttons">
                <button id="inventoryBtn" class="active">Inventory</button>
                <button id="dailyBtn">Daily</button>
                <button id="weeklyBtn">Weekly</button>
                <button id="monthlyBtn">Monthly</button>
                <button id="yearlyBtn">Yearly</button>
            </div>
        </div>
        <main>
        <div id="inventoryTable" class="inventory-table active">
            <h2>Product Inventory</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Current Stock</th>
                        <th>Min Stock Level</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="inventoryTableBody">
                    <!-- Product inventory data will be loaded here -->
                    <tr>
                        <td colspan="8" class="loading-message">Loading inventory data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div id="dailyTable" class="inventory-table">
            <h2>Daily Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Item Name</th>
                        <th>Stock</th>
                        <th>Remaining Stock</th>
                        <th>Sold</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="dailyTableBody">
                    </tbody>
            </table>
        </div>
        
        <div id="weeklyTable" class="inventory-table">
            <h2>Weekly Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Week</th>
                        <th>Item Name</th>
                        <th>Starting Stock</th>
                        <th>Ending Stock</th>
                        <th>Total Sold</th>
                    </tr>
                </thead>
                <tbody id="weeklyTableBody">
                    </tbody>
            </table>
        </div>
        
        <div id="monthlyTable" class="inventory-table">
            <h2>Monthly Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Week</th>
                        <th>Total Sales</th>
                        <th>Total Sold</th>
                        <th>Stocks left</th>
                        <th>Best Seller</th>
                    </tr>
                </thead>
                <tbody id="monthlyTableBody">
                    </tbody>
            </table>
        </div>

        <div id="yearlyTable" class="inventory-table">
            <h2>Yearly Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Total_Sales</th>
                        <th>Best Seller</th>
                    </tr>
                </thead>
                <tbody id="yearlyTableBody">
                    </tbody>
            </table>
        </div>
    </main>
    </div>

    <!-- Stock Update Modal -->
    <div id="stockModal" class="modal">
        <div class="modal-content">
            <span class="close-button" id="closeStockModal">&times;</span>
            <h2>Update Stock</h2>
            <form id="stockForm">
                <input type="hidden" id="productId">
                <div class="form-group">
                    <label for="productName">Product Name:</label>
                    <span id="productName"></span>
                </div>
                <div class="form-group">
                    <label for="currentStock">Current Stock:</label>
                    <input type="number" id="currentStock" min="0" required>
                </div>
                <div class="form-group">
                    <label for="minStockLevel">Minimum Stock Level:</label>
                    <input type="number" id="minStockLevel" min="0" required>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="button save">Save Changes</button>
                    <button type="button" id="cancelStock" class="button cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../VJavaScript/sidebar.js"></script>
    <script src="/esang_delicacies/public/VJavaScript/inventory.js"></script>
</body>
</html>