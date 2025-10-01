document.addEventListener('DOMContentLoaded', () => {
    // Load actual cart data from localStorage (set in Customer Dashboard)
    let cartItems = JSON.parse(localStorage.getItem('customerCart')) || [];

    // Normalize structure: add id and image if missing
    cartItems = cartItems.map((item, index) => ({
        id: index + 1,
        name: item.name,
        price: item.price,
        quantity: item.quantity,
        size: item.size || null,
        flavors: item.flavors || null,
        packageDetails: item.packageDetails || null,
        image: item.image || 'https://placehold.co/200x150?text=No+Image'
    }));

    // Order state arrays
    let pendingOrders = [...cartItems];
    let ongoingOrders = [];
    // Completed orders will be loaded from database
    let completedOrders = [];
    
    // Load completed orders from database
    async function loadCompletedOrders() {
        try {
            const response = await fetch('/esang_delicacies/public/api/get_customer_orders.php');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                completedOrders = result.orders;
                renderCompleted();
            } else {
                console.error('Failed to load completed orders:', result.message);
                // Keep sample data as fallback
                completedOrders = [
                    {
                        id: 999,
                        orderNumber: '#999',
                        items: [
                            { name: 'BIKO', price: 120.00, quantity: 1 }
                        ],
                        payment: 'Bank Transfer',
                        location: {
                            region: 'Metro Manila',
                            city: 'Caloocan City',
                            district: 'District 1',
                            barangay: 'Barangay 172'
                        },
                        total: 120.00,
                        deliveryFee: 0.00,
                        date: new Date().toLocaleDateString('en-US'),
                        time: new Date().toLocaleTimeString('en-US'),
                        timestamp: new Date().toLocaleString('en-US'),
                        username: window.customerData?.name || 'Sample Customer',
                        image: 'https://placehold.co/200x150?text=Sample+Order'
                    }
                ];
                renderCompleted();
            }
        } catch (error) {
            console.error('Error loading completed orders:', error);
            // Keep sample data as fallback
            completedOrders = [
                {
                    id: 999,
                    orderNumber: '#999',
                    items: [
                        { name: 'BIKO', price: 120.00, quantity: 1 }
                    ],
                    payment: 'Bank Transfer',
                    location: {
                        region: 'Metro Manila',
                        city: 'Caloocan City',
                        district: 'District 1',
                        barangay: 'Barangay 172'
                    },
                    total: 120.00,
                    deliveryFee: 0.00,
                    date: new Date().toLocaleDateString('en-US'),
                    time: new Date().toLocaleTimeString('en-US'),
                    timestamp: new Date().toLocaleString('en-US'),
                    username: window.customerData?.name || 'Sample Customer',
                    image: 'https://placehold.co/200x150?text=Sample+Order'
                }
            ];
            renderCompleted();
        }
    }

    // DOM elements
    const pendingContainer = document.getElementById('pending-container');
    const ongoingContainer = document.getElementById('ongoing-container');
    const completedContainer = document.getElementById('completed-container');
    const checkoutBtn = document.getElementById('checkout-btn');
    const ongoingDetails = document.getElementById('ongoing-details');
    const ongoingForm = document.getElementById('ongoing-form');
    const placeOrderBtn = document.getElementById('place-order-btn');
    const pendingTotal = document.getElementById('pending-total');

    // NEW INVOICE MODAL DOM ELEMENTS
    const invoiceModal = document.getElementById('invoiceModal');
    const closeInvoiceModal = document.querySelector('.close-invoice-modal');
    const invoicePreviewArea = document.getElementById('invoice-preview-area');
    const printInvoiceBtn = document.getElementById('print-invoice-btn');
    const downloadInvoiceBtn = document.getElementById('download-invoice-btn');

    // Sample location data
    const locations = {
        "Metro Manila": {
            "Caloocan City": {
                "District 1": ["Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 77", "Barangay 78", "Barangay 79", "Barangay 80", "Barangay 81", "Barangay 82", "Barangay 83", "Barangay 84", "Barangay 85", "Barangay 132", "Barangay 133", "Barangay 134", "Barangay 135", "Barangay 136", "Barangay 137", "Barangay 138", "Barangay 139", "Barangay 140", "Barangay 141", "Barangay 142", "Barangay 143", "Barangay 144", "Barangay 145", "Barangay 146", "Barangay 147", "Barangay 148", "Barangay 149", "Barangay 150", "Barangay 151", "Barangay 152", "Barangay 153", "Barangay 154", "Barangay 155", "Barangay 156", "Barangay 157", "Barangay 158", "Barangay 159", "Barangay 160", "Barangay 161", "Barangay 162", "Barangay 163", "Barangay 164", "Barangay 165", "Barangay 166", "Barangay 167", "Barangay 168", "Barangay 169", "Barangay 170", "Barangay 171", "Barangay 172", "Barangay 173", "Barangay 174" ]
            },
        },
    };

    const regionSelect = document.getElementById('region');
    const citySelect = document.getElementById('city');
    const districtSelect = document.getElementById('district');
    const barangaySelect = document.getElementById('barangay');

    // Function to calculate and update the total price
    const updateTotalPrice = () => {
        const checkedItems = Array.from(pendingContainer.querySelectorAll('input[type="checkbox"]:checked'));
        let totalPrice = 0;
        checkedItems.forEach(checkbox => {
            const orderId = parseInt(checkbox.dataset.id);
            const item = pendingOrders.find(order => order.id === orderId);
            if (item) {
                totalPrice += item.price * item.quantity;
            }
        });
        pendingTotal.textContent = `Total: ₱${totalPrice.toFixed(2)}`;
    };

    // Function to render a single order item card
    const createOrderCard = (order, isPending = false, isCompleted = false) => {
        const card = document.createElement('div');
        card.className = 'order-card';
        
        let headerContentHTML = ''; // Holds the top part of the card (image, details, controls/info)
        
        if (isPending) {
            headerContentHTML = `
                <input type="checkbox" data-id="${order.id}" class="checkbox-input">
                <img src="${order.image}" alt="${order.name}">
                <div class="order-details">
                    <p class="product-name">${order.name}</p>
                    <p class="product-price">Price: ₱${order.price.toFixed(2)}</p>
                </div>
                <div class="quantity-controls">
                    <button class="quantity-btn minus-btn" data-id="${order.id}">-</button>
                    <span class="quantity-value" data-id="${order.id}">${order.quantity}</span>
                    <button class="quantity-btn plus-btn" data-id="${order.id}">+</button>
                </div>
            `;
        } else {
            // For ongoing/completed orders, calculate total based on detailed items
            const subtotal = order.items ? order.items.reduce((sum, item) => sum + item.price * item.quantity, 0) : order.price * order.quantity;
            const totalDisplay = order.total ? order.total.toFixed(2) : subtotal.toFixed(2);
            headerContentHTML = `
                <div style="display: flex; width: 100%; align-items: center; gap: 1rem;">
                    <img src="${order.image}" alt="Order Image" style="height: 4rem; width: 4rem; object-fit: cover; border-radius: 0.375rem;">
                    <div class="order-details">
                        <p class="product-name">Order: ${order.orderNumber ? order.orderNumber : 'Pending submission'}</p>
                        <p class="product-price">Total: ₱${totalDisplay}</p>
                        <p class="product-qty">Items: ${order.items ? order.items.length : 1}</p>
                    </div>
                </div>
            `;
        }

        card.innerHTML = headerContentHTML;

        if (!isPending && order.payment && order.location) {
            const details = document.createElement('div');
            details.className = 'order-info';
            details.innerHTML = `
                <p><strong>Payment:</strong> ${order.payment}</p>
                <p><strong>Location:</strong> ${order.location.barangay}, ${order.location.city}</p>
            `;
            card.appendChild(details);
        }

        // Add View Invoice, Feedback, and Order Completed buttons for completed orders
        if (isCompleted) {
            const actionDiv = document.createElement('div');
            actionDiv.className = 'completed-actions';
    
            const invoiceBtn = document.createElement('button');
            invoiceBtn.className = 'action-btn invoice-btn';
            invoiceBtn.innerHTML = '<i class="fas fa-file-invoice"></i> View Invoice';
            invoiceBtn.dataset.orderId = order.id; 

        const feedbackBtn = document.createElement('button');
        feedbackBtn.className = 'action-btn feedback-btn';
        feedbackBtn.innerHTML = '<i class="fas fa-comment"></i> Feedback';
        feedbackBtn.dataset.orderId = order.id; 

        const completedBtn = document.createElement('button');
        completedBtn.className = 'action-btn completed-btn';
        completedBtn.innerHTML = '<i class="fas fa-credit-card"></i> Make Payment';
        completedBtn.dataset.orderId = order.id;

        actionDiv.appendChild(invoiceBtn);
        actionDiv.appendChild(feedbackBtn);
        actionDiv.appendChild(completedBtn);
        card.appendChild(actionDiv);
    }

        return card;
    };

    // Function to render the pending orders section
    const renderPending = () => {
        pendingContainer.innerHTML = '';
        if (pendingOrders.length === 0) {
            pendingContainer.innerHTML = '<p style="text-align: center; color: #6b7280;">No pending orders.</p>';
        }
        pendingOrders.forEach(order => {
            pendingContainer.appendChild(createOrderCard(order, true));
        });
        updateTotalPrice();
    };

    // Function to render the ongoing orders section
    const renderOngoing = () => {
        ongoingContainer.innerHTML = '';
        if (ongoingOrders.length > 0) {
            ongoingDetails.classList.add('visible');
            ongoingOrders.forEach(order => {
                ongoingContainer.appendChild(createOrderCard(order));
            });
        } else {
            ongoingDetails.classList.remove('visible');
            ongoingContainer.innerHTML = '<p style="text-align: center; color: #6b7280;">No ongoing orders.</p>';
        }
    };

    // Function to render the completed orders section
    const renderCompleted = () => {
        completedContainer.innerHTML = '';
        if (completedOrders.length === 0) {
            completedContainer.innerHTML = '<p style="text-align: center; color: #6b7280;">No completed orders.</p>';
        }
        completedOrders.forEach(order => {
            completedContainer.appendChild(createOrderCard(order, false, true)); // Pass true for isCompleted
        });
    };
    
    // Function to generate the receipt/invoice HTML
    const generateInvoiceHTML = (order) => {
        // Fallback for missing data
        if (!order.items || order.items.length === 0) {
            return '<p style="text-align: center;">Invoice details incomplete.</p>';
        }

        const address = `${order.location.barangay}, ${order.location.district ? order.location.district + ',' : ''} ${order.location.city}, ${order.location.region}`;
        const subtotal = order.items.reduce((sum, item) => sum + item.price * item.quantity, 0).toFixed(2);
        const deliveryFee = order.deliveryFee ? order.deliveryFee.toFixed(2) : '0.00';
        const total = order.total ? order.total.toFixed(2) : (parseFloat(subtotal) + parseFloat(deliveryFee)).toFixed(2);

        let itemsHTML = order.items.map(item => `
            <div class="invoice-item-row">
                <span class="item-name">${item.name}</span>
                <span class="item-qty">x${item.quantity}</span>
                <span class="item-price">₱${(item.price * item.quantity).toFixed(2)}</span>
            </div>
        `).join('');

        return `
            <div class="invoice-container" data-order-number="${order.orderNumber}">
                <div class="invoice-header">
                    ========================================
                </div>
                <p class="invoice-title">WELCOME TO ESANG DELICACIES!</p>
                <p class="invoice-address">${address}</p>
                <div class="invoice-header" style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 5px 0; margin-bottom: 0;">
                    ========================================
                </div>
                <div class="invoice-details">
                    <table>
                        <tr><td>Order Number:</td><td><strong>${order.orderNumber}</strong></td></tr>
                        <tr><td>Date:</td><td>${order.date}</td></tr>
                        <tr><td>Payment Type:</td><td>${order.payment}</td></tr>
                        <tr><td>Username:</td><td>${order.username}</td></tr>
                        <tr><td>Time Stamp:</td><td>${order.timestamp}</td></tr>
                    </table>
                </div>
                <div class="invoice-items">
                    ${itemsHTML}
                </div>
                <div class="invoice-summary">
                    <div class="summary-row"><span>Sub Total</span><span>₱${subtotal}</span></div>
                    <div class="summary-row"><span>Delivery Fee</span><span>₱${deliveryFee}</span></div>
                    <div class="summary-row"><strong style="font-size: 1.2em;">TOTAL:</strong><strong style="font-size: 1.2em;">₱${total}</strong></div>
                </div>
                <div class="invoice-footer">
                    ========================================
                    <p>THANK YOU FOR ORDERING !</p>
                    ========================================
                </div>
            </div>
        `;
    };

    // Event listener for all completed order buttons
    completedContainer.addEventListener('click', (e) => {
        const target = e.target.closest('.action-btn');
        if (!target) return;

        const orderId = parseInt(target.dataset.orderId);
        const order = completedOrders.find(o => o.id === orderId);

        if (!order) return;

        if (target.classList.contains('invoice-btn')) {
            // VIEW INVOICE LOGIC
            invoicePreviewArea.innerHTML = generateInvoiceHTML(order);
            invoiceModal.style.display = 'block';

        } else if (target.classList.contains('feedback-btn')) {
            // FEEDBACK LOGIC (Simple alert/redirection)
            alert(`Redirecting to feedback form for Order ${order.orderNumber}. (In a real app, this would redirect)`);
            // window.location.href = 'feedback.html?orderId=' + order.orderNumber;
        } else if (target.classList.contains('completed-btn')) {
            // MAKE PAYMENT -> Redirect to Payments/Billing page
            window.location.href = 'customer_billing.php';
        }
    });

    // Close invoice modal event listeners
    closeInvoiceModal.addEventListener('click', () => {
        invoiceModal.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target === invoiceModal) {
            invoiceModal.style.display = 'none';
        }
    });

    // PRINT FUNCTIONALITY
    printInvoiceBtn.addEventListener('click', () => {
        // Uses the '@media print' CSS rules to isolate and print the invoice
        window.print();
    });

    // DOWNLOAD PDF FUNCTIONALITY (Requires html2pdf.js library)
    downloadInvoiceBtn.addEventListener('click', () => {
        // Check if html2pdf is loaded
        if (typeof html2pdf === 'undefined') {
            alert("PDF library (html2pdf.js) not loaded. Cannot download.");
            return;
        }
        
        const element = invoicePreviewArea.querySelector('.invoice-container');
        const orderNumber = element.dataset.orderNumber || 'Invoice';

        const opt = {
            margin:       0.5,
            filename:     `${orderNumber}_invoice.pdf`,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
        };

        // Use html2pdf to generate and download the PDF
        html2pdf().set(opt).from(element).save();
    });


    // Event listener for quantity buttons and checkboxes
    pendingContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('quantity-btn')) {
            const orderId = parseInt(e.target.dataset.id);
            const order = pendingOrders.find(item => item.id === orderId);
            if (!order) return;

            if (e.target.classList.contains('plus-btn')) {
                order.quantity++;
            } else if (e.target.classList.contains('minus-btn')) {
                if (order.quantity > 1) {
                    order.quantity--;
                }
            }
            renderPending();
        } else if (e.target.classList.contains('checkbox-input')) {
            updateTotalPrice();
        }
    });

    // Function to populate dropdowns (location logic)
    const populateDropdowns = () => {
        regionSelect.innerHTML = '<option value="" disabled selected>Select Region</option>';
        Object.keys(locations).forEach(region => {
            const option = document.createElement('option');
            option.value = region;
            option.textContent = region;
            regionSelect.appendChild(option);
        });

        regionSelect.addEventListener('change', (e) => {
            const selectedRegion = e.target.value;
            citySelect.innerHTML = '<option value="" disabled selected>Select City</option>';
            districtSelect.innerHTML = '<option value="" disabled selected>Select District</option>';
            barangaySelect.innerHTML = '<option value="" disabled selected>Select Barangay</option>';
            citySelect.disabled = false;
            districtSelect.disabled = true;
            barangaySelect.disabled = true;

            if (selectedRegion) {
                Object.keys(locations[selectedRegion]).forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
            }
        });

        citySelect.addEventListener('change', (e) => {
            const selectedRegion = regionSelect.value;
            const selectedCity = citySelect.value;
            districtSelect.innerHTML = '<option value="" disabled selected>Select District</option>';
            barangaySelect.innerHTML = '<option value="" disabled selected>Select Barangay</option>';
            districtSelect.disabled = false;
            barangaySelect.disabled = true;

            if (selectedCity) {
                Object.keys(locations[selectedRegion][selectedCity]).forEach(district => {
                    const option = document.createElement('option');
                    option.value = district;
                    option.textContent = district;
                    districtSelect.appendChild(option);
                });
            }
        });

        districtSelect.addEventListener('change', (e) => {
            const selectedRegion = regionSelect.value;
            const selectedCity = citySelect.value;
            const selectedDistrict = e.target.value;
            barangaySelect.innerHTML = '<option value="" disabled selected>Select Barangay</option>';
            barangaySelect.disabled = false;

            if (selectedDistrict) {
                locations[selectedRegion][selectedCity][selectedDistrict].forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay;
                    option.textContent = barangay;
                    barangaySelect.appendChild(option);
                });
            }
        });
    };

    // Event listener for the "Check Out" button
    checkoutBtn.addEventListener('click', () => {
        const checkedItems = Array.from(pendingContainer.querySelectorAll('input[type="checkbox"]:checked'));
        if (checkedItems.length === 0) {
            alert("Please select at least one item to check out.");
            return;
        }

        const checkedIds = checkedItems.map(item => parseInt(item.dataset.id));
        const itemsToMove = pendingOrders.filter(order => checkedIds.includes(order.id));
        
        ongoingOrders = [];
        ongoingOrders.push(...itemsToMove);

        pendingOrders = pendingOrders.filter(order => !checkedIds.includes(order.id));

        renderPending();
        renderOngoing();
    });

    // Event listener for the "Place Order" button

    ongoingForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const selectedPayment = document.querySelector('input[name="payment"]:checked').value;
        const selectedRegion = regionSelect.value;
        const selectedCity = citySelect.value;
        const selectedDistrict = districtSelect.value;
        const selectedBarangay = barangaySelect.value;

        if (!selectedRegion || !selectedCity || !selectedDistrict || !selectedBarangay) {
            alert("Please fill out all location details.");
            return;
        }

        // Save COD address to localStorage
        const fullAddress = `${selectedBarangay}, ${selectedDistrict}, ${selectedCity}, ${selectedRegion}`;
        localStorage.setItem('customerCODAddress', fullAddress);

        // Prepare order data for backend
        const customerId = window.customerData?.id || 1;
        const orderManagerId = 1; // Replace with actual order manager ID if available
        // Build items from ongoingOrders (the items the user is placing now)
        const items = (ongoingOrders || []).map(item => {
            // Try to preserve real DB product identifier if available
            const resolvedProdId = (item.prodId != null ? item.prodId
                                   : (item.product_id != null ? item.product_id
                                   : (item.id != null ? item.id : null)));
            const payloadItem = {
                quantity: item.quantity
            };
            // Include both keys for backend compatibility; backend will normalize
            if (resolvedProdId != null) {
                payloadItem.prodId = resolvedProdId;
                payloadItem.product_id = resolvedProdId;
            }
            // As a robust fallback, also include name so backend can resolve by name if needed
            if (item.name) {
                payloadItem.name = item.name;
            }
            return payloadItem;
        });

        const payload = {
            customerId,
            orderManagerId,
            deliveryAddress: fullAddress,
            orderStatus: 'Pending',
            items,
            paymentMethod: selectedPayment
        };

        try {
            const response = await fetch('/esang_delicacies/public/api/order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (data.ok) {
                alert('Order placed successfully!');
                const now = new Date();
                const orderDate = now.toLocaleDateString('en-US');
                const orderTime = now.toLocaleTimeString('en-US');
                const timestamp = now.toLocaleString('en-US');
                
                completedOrders.unshift({
                    id: data.orderId,
                    orderNumber: `#${data.orderId}`,
                    items: ongoingOrders.map(item => ({ name: item.name, price: item.price, quantity: item.quantity })),
                    payment: selectedPayment,
                    location: { region: selectedRegion, city: selectedCity, district: selectedDistrict, barangay: selectedBarangay },
                    total: data.totalAmount,
                    deliveryFee: 0.00, // Add delivery fee if needed
                    date: orderDate,
                    time: orderTime,
                    timestamp: timestamp,
                    username: window.customerData?.name || 'Customer',
                    image: ongoingOrders[0]?.image || 'https://placehold.co/200x150?text=Order+Image'
                });
                ongoingOrders = [];
                renderOngoing();
                renderCompleted();
            } else {
                alert('Order failed: ' + (data.error || 'Unknown error'));
            }
        } catch (err) {
            alert('Error placing order: ' + err);
        }
    });


    // Initial render on page load
    renderPending();
    renderOngoing();
    loadCompletedOrders(); // This will call renderCompleted() internally
    populateDropdowns();
});