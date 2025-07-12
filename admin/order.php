<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

require_once '../includes/db_connect.php';

$preparing_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Preparing'")->fetch_assoc()['count'];
$claimed_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Claimed'")->fetch_assoc()['count'];
$pending_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'")->fetch_assoc()['count'];

$orders_query = "SELECT o.*, s.stall_name 
                FROM orders o 
                LEFT JOIN seller s ON o.seller_id = s.id 
                ORDER BY o.order_time DESC";
                
$orders_result = $conn->query($orders_query);
$orders = [];
while($row = $orders_result->fetch_assoc()) {
    $orders[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EZ-ORDER | Order Management</title>

    <link rel="stylesheet" href="assets/css/order.css">
    <script>
        function filterOrders(status) {
            const rows = document.querySelectorAll('.order-table tbody tr');
            const preparingBtn = document.querySelector('.filter-btn.preparing');
            const claimedBtn = document.querySelector('.filter-btn.claimed');
            const pendingBtn = document.querySelector('.filter-btn.pending');
            
            preparingBtn.classList.remove('selected');
            claimedBtn.classList.remove('selected');
            pendingBtn.classList.remove('selected');

            if (status === 'Preparing') {
                preparingBtn.classList.add('selected');
            } else if (status === 'Claimed') {
                claimedBtn.classList.add('selected');
            } else if (status === 'Pending') {
                pendingBtn.classList.add('selected');
            }

            rows.forEach(row => {
                const statusCell = row.querySelector('.status-badge');
                if (status === 'all' || statusCell.textContent === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function searchOrders() {
            const searchInput = document.querySelector('.search-box input');
            const filter = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('.order-table tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function viewDetails(orderId) {
            const modal = document.getElementById('orderModal');
            const orderData = <?php echo json_encode($orders); ?>;
            const order = orderData.find(o => o.id == orderId);
            
            if (order) {
                document.getElementById('orderImage').src = '../images/products/default.jpg';
                document.getElementById('productName').textContent = order.product_name || '-';
                document.getElementById('orderQuantity').textContent = order.quantity || '-';
                document.getElementById('orderPrice').textContent = order.price ? `₱${order.price}` : '-';
                
                document.getElementById('orderBy').textContent = order.client_name;
                document.getElementById('orderTime').textContent = new Date(order.order_time).toLocaleTimeString();
                document.getElementById('orderDate').textContent = new Date(order.order_time).toLocaleDateString();
                document.getElementById('paymentStatus').textContent = order.payment_status || 'Pending';
                document.getElementById('specialRequest').textContent = order.special_request || 'None';
                
                const total = (order.price * order.quantity) || 0;
                document.getElementById('totalPrice').textContent = total.toFixed(2);
                
                modal.style.display = 'flex';
            }
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) {
                modal.style.display = 'none';
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
            <a href="seller.php">👤 Seller</a>
            <a href="order.php"><strong>📦 Order</strong></a>
            <a href="reports.php">📋 Reports</a>
            <div class="logout">
                <a href="logout.php">↩ Logout</a>
            </div>
        </div>

        <div class="main">
            <div class="dashboard-section">
                <h2>Orders</h2>

                <div class="order-controls">
                    <div class="filter-buttons">
                        <button class="filter-btn preparing" onclick="filterOrders('Preparing')">Preparing</button>
                        <button class="filter-btn claimed" onclick="filterOrders('Claimed')">Claimed</button>
                        <button class="filter-btn pending" onclick="filterOrders('Pending')">Pending</button>
                    </div>
                    <div class="search-box">
                        <input type="text" placeholder="Search....." oninput="searchOrders()">
                    </div>
                </div>

                <div class="order-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Client Name</th>
                                <th>Student Number</th>
                                <th>Stall Name</th>
                                <th>Order Status</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['client_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['student_number']); ?></td>
                                <td><?php echo htmlspecialchars($order['stall_name']); ?></td>
                                <td><span class="status-badge <?php echo strtolower($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                <td><button class="details-btn" onclick="viewDetails(<?php echo $order['id']; ?>)">ℹ️</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Product</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="order-info">
                    <table class="details-table">
                        <tr>
                            <th>Product</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                        <tr>
                            <td>
                                <div class="product-image">
                                    <img id="orderImage" src="" alt="Product" width="50" height="50">
                                </div>
                            </td>
                            <td id="productName">-</td>
                            <td id="orderQuantity">-</td>
                            <td id="orderPrice">-</td>
                        </tr>
                    </table>
                    
                    <div class="order-details">
                        <p><strong>Ordered by:</strong> <span id="orderBy"></span></p>
                        <p><strong>Time:</strong> <span id="orderTime"></span></p>
                        <p><strong>Date:</strong> <span id="orderDate"></span></p>
                        <p><strong>Payment Status:</strong> <span id="paymentStatus"></span></p>
                        <p class="special-request"><strong>Special Request:</strong> <span id="specialRequest"></span></p>
                        <p class="total-price"><strong>Total Price:</strong> ₱<span id="totalPrice">0.00</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h2 {
            margin: 0;
            color: #333;
        }

        .close {
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .details-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .details-table th,
        .details-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .product-image {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .order-details {
            padding: 15px 0;
        }

        .order-details p {
            margin: 8px 0;
            color: #666;
        }

        .order-details strong {
            color: #333;
            min-width: 120px;
            display: inline-block;
        }

        .total-price {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-size: 1.1em;
            color: #333;
        }

        .special-request {
            margin: 10px 0;
            padding: 10px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }
    </style>
</body>

</html> 