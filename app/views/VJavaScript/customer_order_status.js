document.addEventListener('DOMContentLoaded', function () {
    let currentOrder = null;
    
    // DOM Elements
    const progressSteps = document.querySelectorAll('.progress-step');
    const deliveredStep = document.querySelector('[data-title="delivered"]');
    const viewDetailsLink = document.getElementById('viewDetailsLink');
    const returnOrderBtn = document.getElementById('returnOrderBtn');
    const riderInfoSection = document.getElementById('riderInfoSection');
    const viewRiderDetailsBtn = document.getElementById('viewRiderDetailsBtn');

    // Modals
    const selectReasonModal = document.getElementById('selectReasonModal');
    const requestSummaryModal = document.getElementById('requestSummaryModal');
    const refundGcashModal = document.getElementById('refundGcashModal');
    const riderDetailsModal = document.getElementById('riderDetailsModal');

    // Buttons and Links
    const closeReasonModal = document.getElementById('closeReasonModal');
    const doneReasonBtn = document.getElementById('doneReasonBtn');
    const changeReasonLink = document.getElementById('changeReasonLink');
    const refundGcashOption = document.getElementById('refundGcashOption');
    const addGcashLink = document.getElementById('addGcashLink');
    const closeGcashModal = document.getElementById('closeGcashModal');
    const closeRiderDetailsModal = document.getElementById('closeRiderDetailsModal');

    // Dynamic content elements
    const selectedReasonSpan = document.getElementById('selectedReason');
    const refundMethodOptions = document.querySelectorAll('.refund-option');
    const refundMethodInputs = document.querySelectorAll('input[name="refund-method"]');
    const gcashForm = document.getElementById('gcashForm');
    const submitRequestBtn = document.getElementById('submitRequestBtn');

    // Pre-fill GCash form with placeholder data
    const gcashFirstName = document.getElementById('gcashFirstName');
    const gcashLastName = document.getElementById('gcashLastName');
    const gcashNumber = document.getElementById('gcashNumber');
    
    // Load order status from API
    async function loadOrderStatus() {
        try {
            const response = await fetch('/esang_delicacies/public/api/get_customer_order_status.php');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success && result.has_orders) {
                currentOrder = result.order;
                updateUI(result);
            } else {
                showNoOrdersMessage();
            }
        } catch (error) {
            console.error('Error loading order status:', error);
            showErrorMessage('Failed to load order status. Please refresh the page.');
        }
    }
    
    // Update UI with order data
    function updateUI(orderData) {
        updateProgressBar(orderData.progress_step);
        updateRiderInfo(orderData.order.rider, orderData.show_rider_details);
        updateReturnButton(orderData.can_return);
        updateViewDetailsLink(orderData.can_return);
    }

    // Function to update the progress bar based on step
    function updateProgressBar(step) {
        const progress = document.getElementById('progress');
        
        progressSteps.forEach((progressStep, index) => {
            progressStep.classList.remove('active');
            if (index <= step) {
                progressStep.classList.add('active');
            }
        });

        if (step >= 0) {
            const percent = (step / (progressSteps.length - 1)) * 100;
            progress.style.width = percent + '%';
        }
    }
    
    // Update rider information display
    function updateRiderInfo(riderData, showRiderDetails) {
        if (riderData && showRiderDetails) {
            // Show rider info section
            riderInfoSection.style.display = 'block';
            
            // Update summary section
            document.getElementById('riderNameSummary').textContent = riderData.name;
            document.getElementById('riderPhoneSummary').textContent = riderData.phone;
            document.getElementById('riderTrackingSummary').textContent = `Tracking: ${riderData.tracking_id}`;
            
            // Update modal content
            document.getElementById('modalRiderName').textContent = riderData.name;
            document.getElementById('modalTrackingId').textContent = riderData.tracking_id;
            document.getElementById('modalRiderPhone').textContent = riderData.phone;
            document.getElementById('modalRiderPlate').textContent = riderData.plate_number || 'Not specified';
            document.getElementById('modalRiderEmail').textContent = riderData.email || 'Not available';
        } else {
            riderInfoSection.style.display = 'none';
        }
    }
    
    // Update return button visibility
    function updateReturnButton(canReturn) {
        returnOrderBtn.style.display = canReturn ? 'block' : 'none';
    }
    
    // Update view details link visibility
    function updateViewDetailsLink(canReturn) {
        viewDetailsLink.style.display = canReturn ? 'inline' : 'none';
    }
    
    // Show message when no orders found
    function showNoOrdersMessage() {
        const container = document.querySelector('.container');
        container.innerHTML = `
            <div class="no-orders-message">
                <h2>No Orders Found</h2>
                <p>You haven't placed any orders yet.</p>
                <a href="customer_dashboard.php" class="btn btn-primary">Start Shopping</a>
            </div>
        `;
    }
    
    // Show error message
    function showErrorMessage(message) {
        const container = document.querySelector('.container');
        container.innerHTML = `
            <div class="error-message">
                <h2>Error Loading Order Status</h2>
                <p>${message}</p>
                <button onclick="location.reload()" class="btn btn-primary">Retry</button>
            </div>
        `;
    }


    // --- Modal Logic ---

    // Open Select Reason Modal

    submitRequestBtn.addEventListener('click', () => {
        const selectedRefundMethod = document.querySelector('input[name="refund-method"]:checked');
        if (!selectedRefundMethod) {
            alert("Please select a refund method.");
            return;
        }
        console.log('Return request submitted:', {
            reason: selectedReasonSpan.textContent,
            refundMethod: selectedRefundMethod.value,
            gcashDetails: {
                firstName: gcashFirstName.value,
                lastName: gcashLastName.value,
                number: gcashNumber.value
            }
        });
        alert('Your return request has been submitted!');
        requestSummaryModal.style.display = 'none';
    });

    // Event Listeners
    
    // View Rider Details - from "view details" link
    viewDetailsLink.addEventListener('click', (e) => {
        e.preventDefault();
        riderDetailsModal.style.display = 'flex';
    });
    
    // View Rider Details - from rider info section button
    viewRiderDetailsBtn.addEventListener('click', (e) => {
        e.preventDefault();
        riderDetailsModal.style.display = 'flex';
    });
    
    // Close rider details modal
    closeRiderDetailsModal.addEventListener('click', () => {
        riderDetailsModal.style.display = 'none';
    });
    
    // Return order button
    returnOrderBtn.addEventListener('click', () => {
        selectReasonModal.style.display = 'flex';
    });
    
    // Close return reason modal
    closeReasonModal.addEventListener('click', () => {
        selectReasonModal.style.display = 'none';
    });
    
    // Done with reason selection
    doneReasonBtn.addEventListener('click', () => {
        const selectedReason = document.querySelector('input[name="return-reason"]:checked');
        if (selectedReason) {
            selectedReasonSpan.textContent = selectedReason.value;
            selectReasonModal.style.display = 'none';
            requestSummaryModal.style.display = 'flex';
        } else {
            alert('Please select a reason for return.');
        }
    });
    
    // Change reason link
    changeReasonLink.addEventListener('click', (e) => {
        e.preventDefault();
        requestSummaryModal.style.display = 'none';
        selectReasonModal.style.display = 'flex';
    });
    
    // Add GCash link
    addGcashLink.addEventListener('click', (e) => {
        e.preventDefault();
        refundGcashModal.style.display = 'flex';
    });
    
    // Close GCash modal
    closeGcashModal.addEventListener('click', () => {
        refundGcashModal.style.display = 'none';
    });
    
    // GCash form submission
    gcashForm.addEventListener('submit', (e) => {
        e.preventDefault();
        refundGcashModal.style.display = 'none';
        alert('GCash details saved!');
    });
    
    // Submit return request
    submitRequestBtn.addEventListener('click', () => {
        const selectedRefundMethod = document.querySelector('input[name="refund-method"]:checked');
        if (!selectedRefundMethod) {
            alert("Please select a refund method.");
            return;
        }
        console.log('Return request submitted:', {
            orderId: currentOrder ? currentOrder.order_id : null,
            reason: selectedReasonSpan.textContent,
            refundMethod: selectedRefundMethod.value,
            gcashDetails: {
                firstName: gcashFirstName.value,
                lastName: gcashLastName.value,
                number: gcashNumber.value
            }
        });
        alert('Your return request has been submitted!');
        requestSummaryModal.style.display = 'none';
    });
    
    // Close modals when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === selectReasonModal) {
            selectReasonModal.style.display = 'none';
        }
        if (e.target === requestSummaryModal) {
            requestSummaryModal.style.display = 'none';
        }
        if (e.target === refundGcashModal) {
            refundGcashModal.style.display = 'none';
        }
        if (e.target === riderDetailsModal) {
            riderDetailsModal.style.display = 'none';
        }
    });
    
    // Initialize - Load order status on page load
    loadOrderStatus();
});
