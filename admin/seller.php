<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stall_name = mysqli_real_escape_string($conn, $_POST['stall_name']);
    $owner_name = mysqli_real_escape_string($conn, $_POST['owner_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $query = "INSERT INTO stall_applications (name, email, password, stall_name, created_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $owner_name, $email, $password, $stall_name);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: seller.php?success=1");
        exit();
    } else {
        $error = "Error submitting application: " . mysqli_error($conn);
    }
}

$total_count = $conn->query("SELECT COUNT(*) as count FROM seller WHERE role = 'seller'")->fetch_assoc()['count'];
$active_count = $conn->query("SELECT COUNT(*) as count FROM seller WHERE role = 'seller'")->fetch_assoc()['count'];
$new_count = $conn->query("SELECT COUNT(*) as count FROM seller WHERE role = 'seller' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'];

$sellers_result = $conn->query("SELECT * FROM seller WHERE role = 'seller' ORDER BY stall_name ASC");
$sellers = [];
while($row = $sellers_result->fetch_assoc()) {
    $sellers[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EZ-ORDER | Seller Management</title>
    <link rel="stylesheet" href="assets/css/seller.css">
    <script>
        function filterSellers(status) {
            const rows = document.querySelectorAll('.seller-table tbody tr');
            const activeBtn = document.querySelector('.filter-btn.active');
            const inactiveBtn = document.querySelector('.filter-btn.inactive');
            
            if (status === 'active') {
                activeBtn.classList.add('selected');
                inactiveBtn.classList.remove('selected');
            } else if (status === 'inactive') {
                inactiveBtn.classList.add('selected');
                activeBtn.classList.remove('selected');
            }

            rows.forEach(row => {
                const statusCell = row.querySelector('.status-badge');
                if (status === 'all' || statusCell.classList.contains(status)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function searchSellers() {
            const searchInput = document.querySelector('.search-box input');
            const filter = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('.seller-table tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function showAddModal() {
            document.getElementById('addSellerModal').style.display = 'block';
        }

        function closeAddModal() {
            document.getElementById('addSellerModal').style.display = 'none';
            document.getElementById('addSellerForm').reset();
        }

        function handleSubmit(event) {
            event.preventDefault();
            const form = document.getElementById('addSellerForm');
            const formData = new FormData(form);

            fetch('seller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                closeAddModal();
                showSuccessModal();
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function showSuccessModal() {
            document.getElementById('successModal').style.display = 'flex';
        }

        function closeSuccessModal() {
            document.getElementById('successModal').style.display = 'none';
            window.location.reload();
        }

        window.onclick = function(event) {
            const addModal = document.getElementById('addSellerModal');
            const successModal = document.getElementById('successModal');
            
            if (event.target == addModal) {
                closeAddModal();
            }
            if (event.target == successModal) {
                closeSuccessModal();
            }
        }

        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                showSuccessModal();
            }
        }
    </script>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <img src="../uploads/logo.png" alt="EZ-ORDER Logo" width="150">
            <div class="search">
                <input type="text" placeholder="Search...">
            </div>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="seller.php"><strong>👤 Seller</strong></a>
            <a href="order.php">📦 Order</a>
            <a href="reports.php">📋 Reports</a>
            <div class="logout">
                <a href="logout.php">↩ Logout</a>
            </div>
        </div>

        <div class="main">
            <div class="dashboard-section">
                <div id="successMessage" class="success-message">
                    Seller added successfully!
                </div>

                <h2>Accounts</h2>
                <div class="dashboard-cards">
                    <div class="card">
                        <div class="icon">
                            <span style="font-size: 2em;">👥</span>
                        </div>
                        <div class="content">
                            <h3><?php echo $total_count; ?></h3>
                            <p>Total Sellers</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="icon">
                            <span style="font-size: 2em;">🆕</span>
                        </div>
                        <div class="content">
                            <h3><?php echo $new_count; ?></h3>
                            <p>New Sellers</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="icon">
                            <span style="font-size: 2em;">✅</span>
                        </div>
                        <div class="content">
                            <h3><?php echo $active_count; ?></h3>
                            <p>Active Sellers</p>
                        </div>
                    </div>
                </div>

                <div class="seller-controls">
                    <div class="left-controls">
                        <button class="filter-btn active" onclick="filterSellers('active')">Active</button>
                        <button class="filter-btn inactive" onclick="filterSellers('inactive')">Inactive</button>
                    </div>
                    <div class="right-controls">
                        <button class="filter-btn add" onclick="showAddModal()">Add</button>
                        <div class="search-box">
                            <input type="text" placeholder="Search....." oninput="searchSellers()">
                        </div>
                    </div>
                </div>

                <div class="seller-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Stall Name</th>
                                <th>Owner's Name</th>
                                <th>Contact No.</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sellers as $seller): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($seller['stall_name']); ?></td>
                                <td><?php echo htmlspecialchars($seller['name']); ?></td>
                                <td><?php echo htmlspecialchars($seller['email']); ?></td>
                                <td><span class="status-badge active">Active</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="addSellerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <h2>Seller</h2>
            <h3>New Seller</h3>
            
            <form method="POST" action="seller.php" id="addSellerForm" onsubmit="handleSubmit(event)">
                <div class="form-group">
                    <label for="stall_name">Stall Name</label>
                    <input type="text" id="stall_name" name="stall_name" required>
                </div>

                <div class="form-group">
                    <label for="owner_name">Owner's Name</label>
                    <input type="text" id="owner_name" name="owner_name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">SAVE</button>
                    <button type="button" class="btn-cancel" onclick="closeAddModal()">CANCEL</button>
                </div>
            </form>
        </div>
    </div>

    <div id="successModal" class="modal">
        <div class="modal-content success-modal">
            <div class="success-animation">
                <div class="checkmark-circle">
                    <div class="checkmark"></div>
                </div>
            </div>
            <h2 class="success-title">Application Submitted Successfully!</h2>
            <p class="success-message">Your application has been submitted and is pending approval.</p>
            <button class="btn-ok" onclick="closeSuccessModal()">OK</button>
        </div>
    </div>
</body>

</html> 