// Global variables for data
let analyticsData = null;
let transactionData = [];
let chartInstance = null;

// Function to load analytics data from API
async function loadAnalyticsData() {
    try {
        const response = await fetch('/esang_delicacies/public/api/get_analytics_data.php', {
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            analyticsData = result.data;
            updateStatistics();
            return true;
        } else {
            console.error('Failed to load analytics:', result.message);
            showNotification('Failed to load analytics data: ' + result.message, 'error');
            return false;
        }
    } catch (error) {
        console.error('Error loading analytics:', error);
        showNotification('Error loading analytics data: ' + error.message, 'error');
        return false;
    }
}

// Function to load transaction data from API
async function loadTransactionData() {
    try {
        const response = await fetch('/esang_delicacies/public/api/get_transaction_history.php', {
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            transactionData = result.data;
            updateTable();
            return true;
        } else {
            console.error('Failed to load transactions:', result.message);
            showNotification('Failed to load transaction data: ' + result.message, 'error');
            return false;
        }
    } catch (error) {
        console.error('Error loading transactions:', error);
        showNotification('Error loading transaction data: ' + error.message, 'error');
        return false;
    }
}

// Function to update statistics cards
function updateStatistics() {
    if (!analyticsData || !analyticsData.statistics) return;
    
    const stats = analyticsData.statistics;
    document.getElementById('total-orders').textContent = stats.total_orders;
    document.getElementById('total-revenue').textContent = '₱' + stats.total_revenue;
    document.getElementById('avg-order-value').textContent = '₱' + stats.average_order_value;
    document.getElementById('unique-customers').textContent = stats.unique_customers;
}

// Function to create or update the chart
function updateChart(type, period) {
    if (!analyticsData) return;
    
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    // Destroy the old chart instance if it exists
    if (chartInstance) {
        chartInstance.destroy();
    }

    let chartData;
    let chartType = type;
    
    switch (period) {
        case 'weekly':
            chartData = {
                labels: analyticsData.weekly.labels,
                datasets: [{
                    label: 'Weekly Revenue',
                    data: analyticsData.weekly.data,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    borderRadius: 8,
                    barPercentage: 0.6
                }]
            };
            chartType = 'bar';
            break;
        case 'monthly':
            chartData = {
                labels: analyticsData.monthly.labels,
                datasets: [{
                    label: 'Monthly Revenue',
                    data: analyticsData.monthly.data,
                    fill: false,
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    tension: 0.4
                }]
            };
            chartType = 'line';
            break;
        case 'yearly':
            chartData = {
                labels: analyticsData.yearly.labels,
                datasets: [{
                    label: 'Yearly Revenue',
                    data: analyticsData.yearly.data,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1,
                    borderRadius: 8,
                    barPercentage: 0.6
                }]
            };
            chartType = 'bar';
            break;
    }

    // Create a new chart instance
    chartInstance = new Chart(ctx, {
        type: chartType,
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

// Function to populate the transaction table
function updateTable() {
    const tableBody = document.getElementById('transaction-table-body');
    tableBody.innerHTML = ''; // Clear existing rows

    if (transactionData.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="4" class="text-center">No transaction data available</td>
        `;
        tableBody.appendChild(row);
        return;
    }

    transactionData.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.customer_name}</td>
            <td>${item.payment_method}</td>
            <td>${item.date}</td>
            <td class="amount">₱${item.amount}</td>
        `;
        tableBody.appendChild(row);
    });
}

// Function to handle button clicks and update the UI
function handleNavClick(view) {
    // Remove 'active' class from all buttons
    document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('active'));

    // Add 'active' class to the clicked button
    let selectedButton;
    switch (view) {
        case 'weekly':
            selectedButton = document.getElementById('weekly-btn');
            updateChart('bar', 'weekly');
            break;
        case 'monthly':
            selectedButton = document.getElementById('monthly-btn');
            updateChart('line', 'monthly');
            break;
        case 'yearly':
            selectedButton = document.getElementById('yearly-btn');
            updateChart('bar', 'yearly');
            break;
    }
    if (selectedButton) {
        selectedButton.classList.add('active');
    }
}

// Function to show notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-weight: bold;
        z-index: 10000;
        max-width: 300px;
        word-wrap: break-word;
        ${type === 'success' ? 'background-color: #28a745;' : ''}
        ${type === 'error' ? 'background-color: #dc3545;' : ''}
        ${type === 'info' ? 'background-color: #17a2b8;' : ''}
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// Function to add sample transaction data
async function addSampleData() {
    if (!confirm('This will add sample transaction data to the database. Continue?')) {
        return;
    }
    
    try {
        const response = await fetch('/esang_delicacies/public/api/add_sample_transactions.php', {
            method: 'POST',
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            // Reload data to show the new transactions
            await loadAnalyticsData();
            await loadTransactionData();
            if (analyticsData) {
                handleNavClick('weekly'); // Refresh the chart
            }
        } else {
            showNotification('Failed to add sample data: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error adding sample data:', error);
        showNotification('Error adding sample data', 'error');
    }
}

// Event listeners for navigation buttons
document.getElementById('weekly-btn').addEventListener('click', () => handleNavClick('weekly'));
document.getElementById('monthly-btn').addEventListener('click', () => handleNavClick('monthly'));
document.getElementById('yearly-btn').addEventListener('click', () => handleNavClick('yearly'));
document.getElementById('add-sample-btn').addEventListener('click', addSampleData);

// Initial setup on page load
window.onload = async function() {
    // Load data from APIs
    const analyticsLoaded = await loadAnalyticsData();
    const transactionsLoaded = await loadTransactionData();
    
    if (analyticsLoaded) {
        handleNavClick('weekly'); // Load weekly performance by default
    }
    
    if (!analyticsLoaded && !transactionsLoaded) {
        showNotification('Failed to load performance data', 'error');
    }
};