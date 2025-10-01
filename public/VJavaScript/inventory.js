document.addEventListener('DOMContentLoaded', function() {
    // Load inventory data when the page loads
    loadInventoryData();
    
    // Set up event listeners
    document.getElementById('searchBar').addEventListener('input', filterInventory);
    document.getElementById('categoryFilter').addEventListener('change', filterInventory);
    
    // View buttons event listeners
    document.getElementById('inventoryBtn').addEventListener('click', function() {
        showTable('inventoryTable');
    });
    document.getElementById('dailyBtn').addEventListener('click', function() {
        showTable('dailyTable');
        loadDailyData();
    });
    document.getElementById('weeklyBtn').addEventListener('click', function() {
        showTable('weeklyTable');
        loadWeeklyData();
    });
    document.getElementById('monthlyBtn').addEventListener('click', function() {
        showTable('monthlyTable');
        loadMonthlyData();
    });
    document.getElementById('yearlyBtn').addEventListener('click', function() {
        showTable('yearlyTable');
        loadYearlyData();
    });
    
    // Stock modal event listeners
    document.getElementById('closeStockModal').addEventListener('click', closeStockModal);
    document.getElementById('cancelStock').addEventListener('click', closeStockModal);
    document.getElementById('stockForm').addEventListener('submit', updateStock);
});

// Function to load inventory data from the API
function loadInventoryData() {
    fetch('/esang_delicacies/public/api/get_inventory_products.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayInventoryData(data.products);
                loadCategories(data.categories);
            } else {
                document.getElementById('inventoryTableBody').innerHTML = 
                    `<tr><td colspan="8" class="error-message">${data.message || 'Failed to load inventory data'}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error fetching inventory data:', error);
            document.getElementById('inventoryTableBody').innerHTML = 
                '<tr><td colspan="8" class="error-message">Error loading inventory data. Please try again.</td></tr>';
        });
}

// Function to load categories for the filter dropdown
function loadCategories(categories) {
    if (!categories) return;
    
    const categoryFilter = document.getElementById('categoryFilter');
    // Clear existing options except the first one
    while (categoryFilter.options.length > 1) {
        categoryFilter.remove(1);
    }
    
    // Add new category options
    categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category;
        option.textContent = category;
        categoryFilter.appendChild(option);
    });
}

// Function to display inventory data in the table
function displayInventoryData(products) {
    const tableBody = document.getElementById('inventoryTableBody');
    tableBody.innerHTML = '';
    
    if (products.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="8" class="no-data">No products found</td></tr>';
        return;
    }
    
    products.forEach(product => {
        const row = document.createElement('tr');
        
        // Add status class to the row
        if (product.status === 'Low') {
            row.classList.add('low-stock');
        } else if (product.status === 'Medium') {
            row.classList.add('medium-stock');
        } else if (product.status === 'High') {
            row.classList.add('high-stock');
        }
        
        row.innerHTML = `
            <td>${product.id}</td>
            <td>${product.name}</td>
            <td>${product.category}</td>
            <td>₱${parseFloat(product.price).toFixed(2)}</td>
            <td>${product.stock}</td>
            <td>${product.min_stock}</td>
            <td class="status-cell ${product.status.toLowerCase()}">${product.status}</td>
            <td>
                <button class="update-stock-btn" data-id="${product.id}" 
                data-name="${product.name}" 
                data-stock="${product.stock}" 
                data-min="${product.min_stock}">
                    Update Stock
                </button>
            </td>
        `;
        
        tableBody.appendChild(row);
    });
    
    // Add event listeners to update stock buttons
    document.querySelectorAll('.update-stock-btn').forEach(button => {
        button.addEventListener('click', function() {
            openStockModal(
                this.getAttribute('data-id'),
                this.getAttribute('data-name'),
                this.getAttribute('data-stock'),
                this.getAttribute('data-min')
            );
        });
    });
}

// Function to filter inventory based on search and category
function filterInventory() {
    const searchTerm = document.getElementById('searchBar').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    
    const rows = document.querySelectorAll('#inventoryTableBody tr');
    
    rows.forEach(row => {
        const productName = row.cells[1]?.textContent.toLowerCase() || '';
        const category = row.cells[2]?.textContent || '';
        
        const matchesSearch = productName.includes(searchTerm);
        const matchesCategory = categoryFilter === 'all' || category === categoryFilter;
        
        if (matchesSearch && matchesCategory) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Function to show the selected table and hide others
function showTable(tableId) {
    const tables = document.querySelectorAll('.inventory-table');
    tables.forEach(table => {
        table.classList.remove('active');
    });
    
    document.getElementById(tableId).classList.add('active');
    
    // Update active button
    const buttons = document.querySelectorAll('.view-buttons button');
    buttons.forEach(button => {
        button.classList.remove('active');
    });
    
    // Find the button that corresponds to the table
    const buttonId = tableId.replace('Table', 'Btn');
    document.getElementById(buttonId).classList.add('active');
}

// Stock modal functions
function openStockModal(productId, productName, currentStock, minStockLevel) {
    document.getElementById('productId').value = productId;
    document.getElementById('productName').textContent = productName;
    document.getElementById('currentStock').value = currentStock;
    document.getElementById('minStockLevel').value = minStockLevel;
    
    document.getElementById('stockModal').style.display = 'block';
}

function closeStockModal() {
    document.getElementById('stockModal').style.display = 'none';
}

function updateStock(event) {
    event.preventDefault();
    
    const productId = document.getElementById('productId').value;
    const stockQuantity = document.getElementById('currentStock').value;
    const minStockLevel = document.getElementById('minStockLevel').value;
    
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('stock_quantity', stockQuantity);
    formData.append('min_stock_level', minStockLevel);
    
    fetch('/esang_delicacies/public/api/update_product_stock.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeStockModal();
            loadInventoryData(); // Reload the inventory data
            alert('Stock updated successfully');
        } else {
            alert('Failed to update stock: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error updating stock:', error);
        alert('Error updating stock. Please try again.');
    });
}

// Functions for loading summary data (daily, weekly, monthly, yearly)
function loadDailyData() {
    fetch('/esang_delicacies/public/api/get_daily_inventory.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayDailyData(data.items);
            } else {
                document.getElementById('dailyTableBody').innerHTML = 
                    `<tr><td colspan="6" class="error-message">${data.message || 'Failed to load daily data'}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error fetching daily data:', error);
            document.getElementById('dailyTableBody').innerHTML = 
                '<tr><td colspan="6" class="error-message">Error loading daily data. Please try again.</td></tr>';
            
            // For demonstration purposes, load sample data if API fails
            loadSampleDailyData();
        });
}

function loadSampleDailyData() {
    // Sample data for demonstration
    const dailyData = [
        { date: '2025-02-28', item: 'Biko', stock: 50, remaining: 40, sold: 10, status: 'High' },
        { date: '2025-02-28', item: 'Cassava Cake', stock: 30, remaining: 25, sold: 5, status: 'High' },
        { date: '2025-02-28', item: 'Carbonara', stock: 100, remaining: 90, sold: 10, status: 'High' },
        { date: '2025-02-28', item: 'Carbonara w/puto', stock: 75, remaining: 65, sold: 10, status: 'High' },
        { date: '2025-02-28', item: 'Maja Blanca', stock: 40, remaining: 35, sold: 5, status: 'High' },
        { date: '2025-02-28', item: 'Kalamay', stock: 25, remaining: 20, sold: 5, status: 'Medium' },
        { date: '2025-02-28', item: 'Lumpiáng Sariwa', stock: 20, remaining: 5, sold: 15, status: 'Low' },
        { date: '2025-02-28', item: 'Turon Bites', stock: 15, remaining: 2, sold: 13, status: 'Low' },
        { date: '2025-02-28', item: 'Ube Macapuno turon', stock: 10, remaining: 1, sold: 9, status: 'Low' },
        { date: '2025-02-28', item: 'Puto', stock: 60, remaining: 10, sold: 50, status: 'Low' },
        { date: '2025-02-28', item: 'Siopao', stock: 45, remaining: 5, sold: 40, status: 'Low' },
        { date: '2025-02-28', item: 'Baked Ube Halaya', stock: 35, remaining: 3, sold: 32, status: 'Low' }
    ];
    
    displayDailyData(dailyData);
}

function displayDailyData(data) {
    const tableBody = document.getElementById('dailyTableBody');
    tableBody.innerHTML = '';
    
    if (data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" class="no-data">No daily data found</td></tr>';
        return;
    }
    
    data.forEach(item => {
        const row = document.createElement('tr');
        
        // Add status class to the row
        if (item.status === 'Low') {
            row.classList.add('low-stock');
        } else if (item.status === 'Medium') {
            row.classList.add('medium-stock');
        } else if (item.status === 'High') {
            row.classList.add('high-stock');
        }
        
        row.innerHTML = `
            <td>${item.date}</td>
            <td>${item.item}</td>
            <td>${item.stock}</td>
            <td>${item.remaining}</td>
            <td>${item.sold}</td>
            <td class="status-cell ${item.status.toLowerCase()}">${item.status}</td>
        `;
        
        tableBody.appendChild(row);
    });
}

function loadWeeklyData() {
    fetch('../../public/api/get_weekly_inventory.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayWeeklyData(data.items);
            } else {
                document.getElementById('weeklyTableBody').innerHTML = 
                    `<tr><td colspan="5" class="error-message">${data.message || 'Failed to load weekly data'}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error fetching weekly data:', error);
            document.getElementById('weeklyTableBody').innerHTML = 
                '<tr><td colspan="5" class="error-message">Error loading weekly data. Please try again.</td></tr>';
            
            // For demonstration purposes, load sample data if API fails
            loadSampleWeeklyData();
        });
}

function loadSampleWeeklyData() {
    // Sample data for demonstration
    const weeklyData = [
        { week: 'Week 1 (Feb 1-7)', item: 'Biko', startingStock: 200, endingStock: 50, totalSold: 150 },
        { week: 'Week 1 (Feb 1-7)', item: 'Cassava Cake', startingStock: 150, endingStock: 30, totalSold: 120 },
        { week: 'Week 1 (Feb 1-7)', item: 'Carbonara', startingStock: 300, endingStock: 75, totalSold: 225 },
        { week: 'Week 2 (Feb 8-14)', item: 'Biko', startingStock: 200, endingStock: 40, totalSold: 160 },
        { week: 'Week 2 (Feb 8-14)', item: 'Cassava Cake', startingStock: 150, endingStock: 25, totalSold: 125 },
        { week: 'Week 2 (Feb 8-14)', item: 'Carbonara', startingStock: 300, endingStock: 90, totalSold: 210 }
    ];
    
    displayWeeklyData(weeklyData);
}

function displayWeeklyData(data) {
    const tableBody = document.getElementById('weeklyTableBody');
    tableBody.innerHTML = '';
    
    if (data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="5" class="no-data">No weekly data found</td></tr>';
        return;
    }
    
    data.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.week}</td>
            <td>${item.item}</td>
            <td>${item.startingStock}</td>
            <td>${item.endingStock}</td>
            <td>${item.totalSold}</td>
        `;
        
        tableBody.appendChild(row);
    });
}

function loadMonthlyData() {
    fetch('../../public/api/get_monthly_inventory.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayMonthlyData(data.items);
            } else {
                document.getElementById('monthlyTableBody').innerHTML = 
                    `<tr><td colspan="5" class="error-message">${data.message || 'Failed to load monthly data'}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error fetching monthly data:', error);
            document.getElementById('monthlyTableBody').innerHTML = 
                '<tr><td colspan="5" class="error-message">Error loading monthly data. Please try again.</td></tr>';
            
            // For demonstration purposes, load sample data if API fails
            loadSampleMonthlyData();
        });
}

function loadSampleMonthlyData() {
    // Sample data for demonstration
    const monthlyData = [
        { week: 'Week 1 (Feb 1-7)', totalSales: '₱15,000', totalSold: 495, stocksLeft: 155, bestSeller: 'Carbonara' },
        { week: 'Week 2 (Feb 8-14)', totalSales: '₱16,200', totalSold: 495, stocksLeft: 155, bestSeller: 'Carbonara' },
        { week: 'Week 3 (Feb 15-21)', totalSales: '₱14,500', totalSold: 480, stocksLeft: 170, bestSeller: 'Biko' },
        { week: 'Week 4 (Feb 22-28)', totalSales: '₱17,300', totalSold: 510, stocksLeft: 140, bestSeller: 'Cassava Cake' }
    ];
    
    displayMonthlyData(monthlyData);
}

function displayMonthlyData(data) {
    const tableBody = document.getElementById('monthlyTableBody');
    tableBody.innerHTML = '';
    
    if (data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="5" class="no-data">No monthly data found</td></tr>';
        return;
    }
    
    data.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.week}</td>
            <td>${item.totalSales}</td>
            <td>${item.totalSold}</td>
            <td>${item.stocksLeft}</td>
            <td>${item.bestSeller}</td>
        `;
        
        tableBody.appendChild(row);
    });
}

function loadYearlyData() {
    fetch('../../public/api/get_yearly_inventory.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayYearlyData(data.items);
            } else {
                document.getElementById('yearlyTableBody').innerHTML = 
                    `<tr><td colspan="3" class="error-message">${data.message || 'Failed to load yearly data'}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error fetching yearly data:', error);
            document.getElementById('yearlyTableBody').innerHTML = 
                '<tr><td colspan="3" class="error-message">Error loading yearly data. Please try again.</td></tr>';
            
            // For demonstration purposes, load sample data if API fails
            loadSampleYearlyData();
        });
}

function loadSampleYearlyData() {
    // Sample data for demonstration
    const yearlyData = [
        { year: '2023', totalSales: '₱750,000', bestSeller: 'Carbonara' },
        { year: '2024', totalSales: '₱820,000', bestSeller: 'Biko' },
        { year: '2025 (YTD)', totalSales: '₱63,000', bestSeller: 'Cassava Cake' }
    ];
    
    displayYearlyData(yearlyData);
}

function displayYearlyData(data) {
    const tableBody = document.getElementById('yearlyTableBody');
    tableBody.innerHTML = '';
    
    if (data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="3" class="no-data">No yearly data found</td></tr>';
        return;
    }
    
    data.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.year}</td>
            <td>${item.totalSales}</td>
            <td>${item.bestSeller}</td>
        `;
        
        tableBody.appendChild(row);
    });
}