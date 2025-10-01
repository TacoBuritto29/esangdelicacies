<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
    <link rel="stylesheet" href="../VCSS/Order_Manager_CSS/order_management.css">
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
        <div class="tab-list">
            <button class="tab-button active" data-target="pending-table-container">Pending Orders</button>
            <button class="tab-button" data-target="ongoing-table-container">Ongoing Orders</button>
            <button class="tab-button" data-target="completed-table-container">Completed Orders</button>
        </div>

        <div class="tab-content">
            <div class="tab-pane active" id="pending-table-container">
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
                                <th>Assigned Rider</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pending-orders-body">
                            <!-- Pending order rows will be injected here by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane" id="ongoing-table-container">
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
                                <th>Assigned Rider</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ongoing-orders-body">
                            <!-- Ongoing order rows will be injected here by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane" id="completed-table-container">
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
                                <th>Assigned Rider</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="completed-orders-body">
                            <!-- Completed order rows will be injected here by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Proof of Payment -->
    <div class="modal-overlay" id="proof-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Proof of Payment</h5>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="modal-image" src="" alt="Proof of Payment" style="width: 100%; height: auto; display: block;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary">Close</button>
            </div>
        </div>
    </div>
    <script src="../VJavaScript/sidebar.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', async () => {
        const tbodyPending = document.getElementById('pending-orders-body');
        const tbodyOngoing = document.getElementById('ongoing-orders-body');
        const tbodyCompleted = document.getElementById('completed-orders-body');

        function renderRow(o) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>#${o.order_id}</td>
                <td>Customer #${o.customer_id}</td>
                <td>—</td>
                <td>—</td>
                <td>₱${Number(o.total_amount).toFixed(2)}</td>
                <td>${o.status}</td>
                <td>${o.payment_status || ''}</td>
                <td>${o.payment_method || ''}</td>
                <td><a href="#">View</a></td>
                <td>—</td>
                <td>—</td>
                <td>
                    <button class="confirm-btn" data-id="${o.order_id}">Confirm Order</button>
                </td>`;
            return tr;
        }

        async function loadOrders() {
            const res = await fetch('/esang_delicacies/public/api/order_manager_orders.php');
            const data = await res.json();
            if (!data.ok) return;
            tbodyPending.innerHTML = tbodyOngoing.innerHTML = tbodyCompleted.innerHTML = '';
            (data.data || []).forEach(o => {
                const row = renderRow(o);
                // Based strictly on DB value:
                // - 'pending' => Pending tab
                // - 'completed' => Completed tab
                // - everything else (e.g., preparing, on_delivery, ready, etc.) => Ongoing tab
                if ((o.status || '').toLowerCase() === 'pending') {
                    tbodyPending.appendChild(row);
                } else if ((o.status || '').toLowerCase() === 'completed') {
                    tbodyCompleted.appendChild(row);
                } else {
                    tbodyOngoing.appendChild(row);
                }
            });
        }

        document.body.addEventListener('click', async (e) => {
            const btn = e.target.closest('.confirm-btn');
            if (!btn) return;
            const id = parseInt(btn.dataset.id, 10);
            await fetch('/esang_delicacies/public/api/order_status_update.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ orderId: id, status: 'completed' })
            });
            await loadOrders();
        });

        await loadOrders();
    });
    </script>
</body>
</html>