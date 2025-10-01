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
        <title>Order Status</title>
        <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
        <link rel="stylesheet" href="../VCSS/Common_CSS/sidebar.css">
        <link rel="stylesheet" href="../VCSS/Customer_CSS/customer_order_status.css">
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
            <div class="container">
                <span class="openbtn" onclick="openNav()">&#9776;</span>
                <h2>Order Status</h2>
                <div class="order-status-header">
                    <div class="progressbar">
                        <div class="progress" id="progress"></div>
                        <div class="progress-step active" data-title="order is placed"><i class="fas fa-box"></i></div>
                        <div class="progress-step" data-title="need approval"><i class="fas fa-clipboard-check"></i></div>
                        <div class="progress-step" data-title="Order is being prepared"><i class="fas fa-hourglass-half"></i></div>
                        <div class="progress-step" data-title="order is out for delivery"><i class="fas fa-truck"></i></div>
                        <div class="progress-step" data-title="delivered">
                            <i class="fas fa-check"></i>
                            <a href="#" class="view-details-link" id="viewDetailsLink">view details</a>
                        </div>
                    </div>
                    
                    <!-- Rider Information Section -->
                    <div class="rider-info-section" id="riderInfoSection" style="display: none;">
                        <h3>Your Delivery Driver</h3>
                        <div class="rider-summary">
                            <div class="rider-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="rider-details-summary">
                                <p class="rider-name-summary" id="riderNameSummary">Loading...</p>
                                <p class="rider-phone-summary" id="riderPhoneSummary">Loading...</p>
                                <p class="rider-tracking-summary" id="riderTrackingSummary">Loading...</p>
                            </div>
                            <button class="rider-details-btn" id="viewRiderDetailsBtn">View Details</button>
                        </div>
                    </div>
                    
                    <button class="return-order-button" id="returnOrderBtn">Return Order</button>
                </div>
            </div>
        </div>
        
        <div class="modal" id="selectReasonModal">
            <div class="modal-content-small">
                <div class="modal-header">
                    <h3>Select reason</h3>
                    <span class="close-button" id="closeReasonModal">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="reason-option">
                        <label>Wrong Item Delivered</label>
                        <input type="radio" name="return-reason" value="Wrong Item Delivered">
                    </div>
                    <div class="reason-option">
                        <label>Missing Item(s)</label>
                        <input type="radio" name="return-reason" value="Missing Item(s)">
                    </div>
                    <div class="reason-option">
                        <label>Food is Spoiled or Stale</label>
                        <input type="radio" name="return-reason" value="Food is Spoiled or Stale">
                    </div>
                    <button class="done-button" id="doneReasonBtn">Done</button>
                </div>
            </div>
        </div>

        <div class="modal" id="requestSummaryModal">
            <div class="modal-content">
                <h3>Submit Request</h3>
                <div class="request-summary-body">
                    <div class="item-list">
                        <h4>item</h4>
                        <div class="item-card">
                            <img src="../Vimages/Full Menu/Delicacies/Turon Bites.png" alt="Turon Bites">
                            <div class="item-details">
                                <p>Turon Bites</p>
                                <p class="quantity">Quantity</p>
                            </div>
                            <span class="quantity-value">1</span>
                        </div>
                        <div class="item-card">
                            <img src="../Vimages/Full Menu/Delicacies/Yema Ube Biko.png" alt="Yema Ube Biko">
                            <div class="item-details">
                                <p>Yema Ube Biko</p>
                                <p class="quantity">Quantity</p>
                            </div>
                            <span class="quantity-value">1</span>
                        </div>
                    </div>
                    <div class="request-details">
                        <div class="detail-row">
                            <span>Reason</span>
                            <span id="selectedReason">Wrong Item Delivered</span>
                            <a href="#" id="changeReasonLink">Change</a>
                        </div>
                        <div class="detail-row">
                            <span>Solution</span>
                            <span id="selectedSolution">Return</span>
                        </div>
                        <div class="refund-summary">
                            <div class="refund-total">
                                <span>Refund Total</span>
                                <span class="total-amount">₱150.00</span>
                            </div>
                            <div class="refund-breakdown">
                                <p>Sub Total: P120.00</p>
                                <p>Delivery fee: P30.00</p>
                                <p>Total: P150.00</p>
                            </div>
                        </div>
                        <div class="refund-method-selection">
                            <h4>Select Refund Method</h4>
                            <p class="note">Your original payment method does not support refunds. Your refund will be sent after your request is approved.</p>
                            <div class="refund-options">
                                <div class="refund-option" id="replaceOrderOption">
                                    <input type="radio" name="refund-method" id="replaceOrder" value="replace">
                                    <label for="replaceOrder">
                                        <h5>Replace order after approval</h5>
                                        <p>Esang Delicacies will prepare and deliver the same meal again to you for free.</p>
                                    </label>
                                </div>
                                <div class="refund-option" id="refundGcashOption">
                                    <input type="radio" name="refund-method" id="refundGcash" value="gcash">
                                    <label for="refundGcash">
                                        <h5>Refund via GCash</h5>
                                        <p>Esang Delicacies will return your payment to your GCash account once your request is approved.</p>
                                    </label>
                                    <a href="#" id="addGcashLink">Add</a>
                                </div>
                            </div>
                        </div>
                        <div class="upload-images">
                            <h4>Upload Images*</h4>
                            <label for="imageUpload" class="upload-label">
                                <p>attached photo</p>
                            </label>
                            <input type="file" id="imageUpload" multiple accept="image/*" style="display: none;">
                        </div>
                        <button class="submit-button" id="submitRequestBtn">Submit</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal" id="refundGcashModal">
            <div class="modal-content-small">
                <div class="modal-header">
                    <h3>Refund Via Gcash</h3>
                    <span class="close-button" id="closeGcashModal">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="gcashForm">
                        <div class="form-group">
                            <label for="gcashFirstName">First Name</label>
                            <input type="text" id="gcashFirstName" name="firstName" value="Ivan" required>
                        </div>
                        <div class="form-group">
                            <label for="gcashLastName">Last Name</label>
                            <input type="text" id="gcashLastName" name="lastName" value="Francis" required>
                        </div>
                        <div class="form-group">
                            <label for="gcashNumber">Gcash Number</label>
                            <input type="text" id="gcashNumber" name="gcashNumber" required>
                        </div>
                        <button type="submit" class="submit-button">Submit</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal" id="riderDetailsModal">
            <div class="modal-content-small">
                <div class="modal-header">
                    <h3>Rider Details</h3>
                    <span class="close-button" id="closeRiderDetailsModal">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="rider-info-card">
                        <div class="profile-pic-container">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="details-container">
                            <p class="rider-name" id="modalRiderName">Loading...</p>
                            <p class="tracking-id" id="modalTrackingId">Loading...</p>
                            <div class="contact-info">
                                <p><i class="fas fa-phone"></i> <span id="modalRiderPhone">Loading...</span></p>
                                <p><i class="fas fa-motorcycle"></i> <span id="modalRiderPlate">Loading...</span></p>
                                <p><i class="fas fa-envelope"></i> <span id="modalRiderEmail">Loading...</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <script src="../VJavaScript/sidebar.js"></script>
    <script src="../VJavaScript/customer_order_status.js"></script>
</body>
</html>