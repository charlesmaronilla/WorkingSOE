<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Function to fetch user report data
function getUserReportData($conn, $dateFrom = null, $dateTo = null) {
    $query = "SELECT 
        o.client_name as 'Name',
        o.student_number as 'Student Number',
        MIN(o.order_time) as 'First Order',
        COUNT(*) as 'Orders Made'
    FROM orders o
    GROUP BY o.client_name, o.student_number";

    if ($dateFrom && $dateTo) {
        $query = "SELECT 
            o.client_name as 'Name',
            o.student_number as 'Student Number',
            MIN(o.order_time) as 'First Order',
            COUNT(*) as 'Orders Made'
        FROM orders o
        WHERE o.order_time BETWEEN ? AND ?
        GROUP BY o.client_name, o.student_number";
    }

    try {
        $stmt = $conn->prepare($query);
        if ($dateFrom && $dateTo) {
            $stmt->bind_param("ss", $dateFrom, $dateTo);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return json_encode($data);
    } catch (Exception $e) {
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Function to fetch seller report data
function getSellerReportData($conn, $dateFrom = null, $dateTo = null) {
    $query = "SELECT 
        s.name as 'Owner',
        s.stall_name as 'Stall Name',
        s.email as 'Email',
        s.created_at as 'Registration Date',
        (SELECT COUNT(*) FROM orders o WHERE o.seller_id = s.id) as 'Total Orders'
    FROM seller s
    WHERE s.role = 'seller'";

    if ($dateFrom && $dateTo) {
        $query = "SELECT 
            s.name as 'Owner',
            s.stall_name as 'Stall Name',
            s.email as 'Email',
            s.created_at as 'Registration Date',
            (SELECT COUNT(*) FROM orders o WHERE o.seller_id = s.id) as 'Total Orders'
        FROM seller s
        WHERE s.role = 'seller' 
        AND s.created_at BETWEEN ? AND ?";
    }

    try {
        $stmt = $conn->prepare($query);
        if ($dateFrom && $dateTo) {
            $stmt->bind_param("ss", $dateFrom, $dateTo);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return json_encode($data);
    } catch (Exception $e) {
        return json_encode(['error' => $e->getMessage()]);
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    $dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : null;
    $dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : null;
    
    if ($_GET['action'] == 'getUserData') {
        echo getUserReportData($conn, $dateFrom, $dateTo);
        exit();
    } elseif ($_GET['action'] == 'getSellerData') {
        echo getSellerReportData($conn, $dateFrom, $dateTo);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EZ-ORDER | Reports</title>
    <link rel="stylesheet" href="assets/css/reports.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <img src="../uploads/logo.png" alt="EZ-ORDER Logo" width="150">
            <div class="search">
                <input type="text" placeholder="Search...">
            </div>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="seller.php">👤 Seller</a>
            <a href="order.php">📦 Order</a>
            <a href="reports.php"><strong>📋 Reports</strong></a>
            <div class="logout">
                <a href="logout.php">↩ Logout</a>
            </div>
        </div>

        <div class="main">
            <h1>Reports</h1>
            
            <div class="header-actions">
                <button class="complaints-btn" onclick="window.location.href='complaints.php'">
                    <i class="fas fa-flag"></i> Complaints
                </button>
            </div>

            <div class="reports-grid">
                <div class="report-card" onclick="showReport('user')">
                    <div class="report-header">
                        <div class="report-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h2 class="report-title">User</h2>
                    </div>
                    <div class="report-content">
                        <p class="report-description">View and analyze user activity, registration trends, and engagement metrics.</p>
                    </div>
                </div>

                <div class="report-card" onclick="showReport('seller')">
                    <div class="report-header">
                        <div class="report-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <h2 class="report-title">Seller</h2>
                    </div>
                    <div class="report-content">
                        <p class="report-description">Track seller performance, sales analytics, and stall management statistics.</p>
                    </div>
                </div>
            </div>

            <div id="reportDetails" class="report-details" style="display: none;">
                <div class="report-filters">
                    <div class="date-filter">
                        <label>From:</label>
                        <input type="date" id="dateFrom">
                        <label>To:</label>
                        <input type="date" id="dateTo">
                    </div>
                    <button class="filter-btn" onclick="applyFilters()">Apply Filters</button>
                </div>

                <div class="report-table-container">
                    <table class="report-table">
                        <thead>
                            <tr id="tableHeaders">
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                        </tbody>
                    </table>
                </div>

                <button class="export-btn" onclick="exportReport()">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentReportType = '';

        function showReport(type) {
            currentReportType = type;
            const reportDetails = document.getElementById('reportDetails');
            reportDetails.style.display = 'block';
            
            const headers = type === 'user' 
                ? ['Name', 'Student Number', 'First Order', 'Orders Made']
                : ['Owner', 'Stall Name', 'Email', 'Registration Date', 'Total Orders'];
            
            setTableHeaders(headers);
            loadReportData(type);
        }

        function setTableHeaders(headers) {
            const headerRow = document.getElementById('tableHeaders');
            headerRow.innerHTML = headers.map(header => `<th>${header}</th>`).join('');
        }

        function loadReportData(type) {
            if (type === 'user') {
                fetchUserData();
            } else {
                fetchSellerData();
            }
        }

        function fetchUserData() {
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            
            let url = 'reports.php?action=getUserData';
            if (dateFrom && dateTo) {
                url += `&dateFrom=${dateFrom}&dateTo=${dateTo}`;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.error);
                        return;
                    }
                    
                    const formattedData = data.map(row => [
                        row['Name'],
                        row['Student Number'],
                        new Date(row['First Order']).toLocaleDateString(),
                        row['Orders Made']
                    ]);
                    
                    displayTableData(formattedData);
                })
                .catch(error => console.error('Error:', error));
        }

        function fetchSellerData() {
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            
            let url = 'reports.php?action=getSellerData';
            if (dateFrom && dateTo) {
                url += `&dateFrom=${dateFrom}&dateTo=${dateTo}`;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.error);
                        return;
                    }
                    
                    const formattedData = data.map(row => [
                        row['Owner'],
                        row['Stall Name'],
                        row['Email'],
                        new Date(row['Registration Date']).toLocaleDateString(),
                        row['Total Orders']
                    ]);
                    
                    displayTableData(formattedData);
                })
                .catch(error => console.error('Error:', error));
        }

        function displayTableData(data) {
            const tableBody = document.getElementById('tableBody');
            tableBody.innerHTML = data.map(row => 
                `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`
            ).join('');
        }

        function applyFilters() {
            if (currentReportType === 'user') {
                fetchUserData();
            } else {
                fetchSellerData();
            }
        }

        function exportReport() {
            const table = document.querySelector('.report-table');
            const rows = Array.from(table.querySelectorAll('tr'));
            
            let csvContent = "data:text/csv;charset=utf-8,";
            
            rows.forEach(row => {
                const cells = Array.from(row.querySelectorAll('th, td'));
                const rowData = cells.map(cell => cell.textContent).join(',');
                csvContent += rowData + "\r\n";
            });
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `${currentReportType}_report.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>

    <style>
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .report-table th,
        .report-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .report-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .report-table tr:hover {
            background-color: #f9f9f9;
        }

        .export-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .export-btn:hover {
            background-color: #45a049;
        }

        .report-filters {
            margin: 20px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 4px;
        }

        .date-filter {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .date-filter input[type="date"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .filter-btn {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }

        .filter-btn:hover {
            background-color: #0056b3;
        }
    </style>
</body>

</html> 