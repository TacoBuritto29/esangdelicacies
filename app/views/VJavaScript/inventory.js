document.addEventListener('DOMContentLoaded', () => {
    // Hardcoded data to simulate a database
    const dailyData = [
        { date: '2025-02-28', itemName: 'Biko', stock: 50, remaining: 40, sold: 10, status: 'High' },
        { date: '2025-02-28', itemName: 'Cassava Cake', stock: 30, remaining: 25, sold: 5, status: 'High' },
        { date: '2025-02-28', itemName: 'Carbonara', stock: 100, remaining: 90, sold: 10, status: 'High' },
        { date: '2025-02-28', itemName: 'Carbonara w/puto', stock: 75, remaining: 65, sold: 10, status: 'High' },
        { date: '2025-02-28', itemName: 'Maja Blanca', stock: 40, remaining: 35, sold: 5, status: 'High' },
        { date: '2025-02-28', itemName: 'Kalamay', stock: 25, remaining: 20, sold: 5, status: 'Medium' },
        { date: '2025-02-28', itemName: 'Lumpiang Sariwa', stock: 20, remaining: 5, sold: 15, status: 'Low' },
        { date: '2025-02-28', itemName: 'Turon Bites', stock: 15, remaining: 2, sold: 13, status: 'Low' },
        { date: '2025-02-28', itemName: 'Ube Macapuno turon', stock: 10, remaining: 1, sold: 9, status: 'Low' },
        { date: '2025-02-28', itemName: 'Puto', stock: 60, remaining: 10, sold: 50, status: 'Low' },
        { date: '2025-02-28', itemName: 'Siopao', stock: 45, remaining: 5, sold: 40, status: 'Low' },
        { date: '2025-02-28', itemName: 'Baked Ube Halaya', stock: 35, remaining: 3, sold: 32, status: 'Low' },
        { date: '2025-02-28', itemName: 'Leche Flan', stock: 50, remaining: 5, sold: 45, status: 'Low' },
        { date: '2025-02-28', itemName: 'bibingka sa Latik', stock: 25, remaining: 8, sold: 17, status: 'Low' },
        { date: '2025-02-28', itemName: 'Yema Ube Biko', stock: 18, remaining: 4, sold: 14, status: 'Low' },
        { date: '2025-02-28', itemName: 'Palitaw with yema', stock: 40, remaining: 35, sold: 5, status: 'High' },
        { date: '2025-02-28', itemName: 'Sapin-sapin Bites', stock: 30, remaining: 25, sold: 5, status: 'High' },
        { date: '2025-02-28', itemName: 'Palabok', stock: 60, remaining: 50, sold: 10, status: 'High' },
        { date: '2025-02-28', itemName: 'Palabok w/puto', stock: 55, remaining: 45, sold: 10, status: 'High' },
        { date: '2025-02-28', itemName: 'Pichi -pichi', stock: 45, remaining: 40, sold: 5, status: 'High' },
        { date: '2025-02-28', itemName: 'Suman Lihliya', stock: 35, remaining: 30, sold: 5, status: 'High' },
        { date: '2025-02-28', itemName: 'Pichi -pichi', stock: 40, remaining: 35, sold: 5, status: 'High' },
        { date: '2025-02-28', itemName: 'Panict Canton', stock: 30, remaining: 25, sold: 5, status: 'Medium' },
        { date: '2025-02-28', itemName: 'Pancit Bihon', stock: 25, remaining: 20, sold: 5, status: 'Medium' },
        { date: '2025-02-28', itemName: 'Baked Macaroni', stock: 20, remaining: 15, sold: 5, status: 'Medium' }
    ];

    const weeklyData = [
        { week: 'Jan 1 - Jan 4', itemName: 'Turon Bites', startingStock: 55, endingStock: 25, totalSold: 1650 },
        { week: 'Jan 5 - Jan 11', itemName: 'Yema ube biko', startingStock: 55, endingStock: 25, totalSold: 1950 },
        { week: 'Jan 12 - Jan 18', itemName: 'Biko', startingStock: 55, endingStock: 25, totalSold: 1650 },
        { week: 'Jan 19 - Jan 25', itemName: 'Maja Blanca', startingStock: 55, endingStock: 25, totalSold: 1650 },
        { week: 'Jan 26 - Jan 31', itemName: 'Pichi- pichi', startingStock: 50, endingStock: 30, totalSold: 1650 }
    ];

    const monthlyData = [
        { week: '1', totalSales: '1950.00', totalSold: '30', stocksLeft: '0', bestSeller: 'Turon Bites' },
        { week: '2', totalSales: '2550.00', totalSold: '30', stocksLeft: '0', bestSeller: 'Yema ube biko' },
        { week: '3', totalSales: '6500.00', totalSold: '100', stocksLeft: '5', bestSeller: 'Biko' },
        { week: '4', totalSales: '2500.00', totalSold: '40', stocksLeft: '3', bestSeller: 'Maja Blanca' },
        { week: '5', totalSales: '4500.00', totalSold: '45', stocksLeft: '3', bestSeller: 'puto' }
    ];

    const yearlyData = [
        { year: 'January', totalSales: '30,000.00', bestSeller: 'Turon Bites' },
        { year: 'January', totalSales: '15,000.00', bestSeller: 'Yema ube biko' },
        { year: 'January', totalSales: '30,000.00', bestSeller: 'Biko' },
        { year: 'January', totalSales: '25,000.00', bestSeller: 'Maja Blanca' },
        { year: 'January', totalSales: '40,000.00', bestSeller: 'Pichi- pichi' },
        { year: 'January', totalSales: '30,000.00', bestSeller: 'Palitaw with yema' },
        { year: 'January', totalSales: '20,000.00', bestSeller: 'ube macapuno turon' },
        { year: 'January', totalSales: '30,000.00', bestSeller: 'Sapin- sapin bites' },
        { year: 'January', totalSales: '30,000.00', bestSeller: 'Kalamay sa latik' },
        { year: 'January', totalSales: '30,000.00', bestSeller: 'suman lihiya' },
        { year: 'January', totalSales: '50,000.00', bestSeller: 'carbonara' },
        { year: 'January', totalSales: '50,000.00', bestSeller: 'palabok' },
        { year: 'January', totalSales: '40,000.00', bestSeller: 'puto' }
    ];

    const dailyTableBody = document.getElementById('dailyTableBody');
    const weeklyTableBody = document.getElementById('weeklyTableBody');
    const monthlyTableBody = document.getElementById('monthlyTableBody');
    const yearlyTableBody = document.getElementById('yearlyTableBody');

    const dailyBtn = document.getElementById('dailyBtn');
    const weeklyBtn = document.getElementById('weeklyBtn');
    const monthlyBtn = document.getElementById('monthlyBtn');
    const yearlyBtn = document.getElementById('yearlyBtn');

    const dailyTable = document.getElementById('dailyTable');
    const weeklyTable = document.getElementById('weeklyTable');
    const monthlyTable = document.getElementById('monthlyTable');
    const yearlyTable = document.getElementById('yearlyTable');

    const searchBar = document.getElementById('searchBar');
    const yearFilter = document.getElementById('yearFilter');
    const monthFilter = document.getElementById('monthFilter');
    const categoryFilter = document.getElementById('categoryFilter');

    let activeTable = 'daily';

    // Function to render table rows
    const renderTable = (data, tableBody) => {
        tableBody.innerHTML = ''; // Clear existing rows
        data.forEach(item => {
            const row = document.createElement('tr');
            if (activeTable === 'daily') {
                const statusClass = `status-${item.status.toLowerCase().replace(' ', '-')}`;
                row.innerHTML = `
                    <td>${item.date}</td>
                    <td>${item.itemName}</td>
                    <td>${item.stock}</td>
                    <td>${item.remaining}</td>
                    <td>${item.sold}</td>
                    <td><span class="status ${statusClass}">${item.status}</span></td>
                `;
            } else if (activeTable === 'weekly') {
                row.innerHTML = `
                    <td>${item.week}</td>
                    <td>${item.itemName}</td>
                    <td>${item.startingStock}</td>
                    <td>${item.endingStock}</td>
                    <td>₱${item.totalSold}</td>
                `;
            } else if (activeTable === 'monthly') {
                row.innerHTML = `
                    <td>${item.week}</td>
                    <td>₱${item.totalSales}</td>
                    <td>${item.totalSold}</td>
                    <td>${item.stocksLeft}</td>
                    <td>${item.bestSeller}</td>
                `;
            } else if (activeTable === 'yearly') {
                row.innerHTML = `
                    <td>${item.year}</td>
                    <td>₱${item.totalSales}</td>
                    <td>${item.bestSeller}</td>
                `;
            }
            tableBody.appendChild(row);
        });
    };

    // Function to filter and search data
    const filterData = () => {
        const searchTerm = searchBar.value.toLowerCase();
        const year = yearFilter.value;
        const month = monthFilter.value;
        const category = categoryFilter.value;

        let filteredData = [];
        if (activeTable === 'daily') {
            filteredData = dailyData.filter(item =>
                (item.itemName.toLowerCase().includes(searchTerm))
            );
        } else if (activeTable === 'weekly') {
            filteredData = weeklyData.filter(item =>
                (item.itemName.toLowerCase().includes(searchTerm))
            );
        } else if (activeTable === 'monthly') {
            filteredData = monthlyData.filter(item =>
                (item.bestSeller.toLowerCase().includes(searchTerm))
            );
        } else if (activeTable === 'yearly') {
            filteredData = yearlyData.filter(item =>
                (item.bestSeller.toLowerCase().includes(searchTerm)) &&
                (year === 'all' || item.year === year) &&
                (category === 'all' || item.category === category)
            );
        }

        if (activeTable === 'daily') {
            renderTable(filteredData, dailyTableBody);
        } else if (activeTable === 'weekly') {
            renderTable(filteredData, weeklyTableBody);
        } else if (activeTable === 'monthly') {
            renderTable(filteredData, monthlyTableBody);
        } else if (activeTable === 'yearly') {
            renderTable(filteredData, yearlyTableBody);
        }
    };

    // Event listeners for view buttons
    dailyBtn.addEventListener('click', () => {
        activeTable = 'daily';
        dailyBtn.classList.add('active');
        weeklyBtn.classList.remove('active');
        monthlyBtn.classList.remove('active');
        yearlyBtn.classList.remove('active');
        dailyTable.classList.add('active');
        weeklyTable.classList.remove('active');
        monthlyTable.classList.remove('active');
        yearlyTable.classList.remove('active');
        filterData();
    });

    weeklyBtn.addEventListener('click', () => {
        activeTable = 'weekly';
        weeklyBtn.classList.add('active');
        dailyBtn.classList.remove('active');
        monthlyBtn.classList.remove('active');
        yearlyBtn.classList.remove('active');
        weeklyTable.classList.add('active');
        dailyTable.classList.remove('active');
        monthlyTable.classList.remove('active');
        yearlyTable.classList.remove('active');
        filterData();
    });

    monthlyBtn.addEventListener('click', () => {
        activeTable = 'monthly';
        monthlyBtn.classList.add('active');
        dailyBtn.classList.remove('active');
        weeklyBtn.classList.remove('active');
        yearlyBtn.classList.remove('active');
        monthlyTable.classList.add('active');
        dailyTable.classList.remove('active');
        weeklyTable.classList.remove('active');
        yearlyTable.classList.remove('active');
        filterData();
    });

    yearlyBtn.addEventListener('click', () => {
        activeTable = 'yearly';
        yearlyBtn.classList.add('active');
        dailyBtn.classList.remove('active');
        weeklyBtn.classList.remove('active');
        monthlyBtn.classList.remove('active');
        yearlyTable.classList.add('active');
        dailyTable.classList.remove('active');
        weeklyTable.classList.remove('active');
        monthlyTable.classList.remove('active');
        filterData();
    });

    // Event listeners for filters and search bar
    searchBar.addEventListener('input', filterData);
    yearFilter.addEventListener('change', filterData);
    monthFilter.addEventListener('change', filterData);
    categoryFilter.addEventListener('change', filterData);

    // Initial render of the daily table
    renderTable(dailyData, dailyTableBody);
});