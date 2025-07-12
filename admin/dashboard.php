<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

require_once '../includes/db_connect.php';

// Get total orders count
$total_orders_query = "SELECT COUNT(*) as total FROM orders";
$total_orders_result = mysqli_query($conn, $total_orders_query);
$total_orders = mysqli_fetch_assoc($total_orders_result)['total'];

// Get pending orders count
$pending_orders_query = "SELECT COUNT(*) as pending FROM orders WHERE status = 'Pending'";
$pending_orders_result = mysqli_query($conn, $pending_orders_query);
$pending_orders = mysqli_fetch_assoc($pending_orders_result)['pending'];

// Get completed orders count
$completed_orders_query = "SELECT COUNT(*) as completed FROM orders WHERE status = 'Claimed'";
$completed_orders_result = mysqli_query($conn, $completed_orders_query);
$completed_orders = mysqli_fetch_assoc($completed_orders_result)['completed'];

// Get active sellers count
$active_sellers_query = "SELECT COUNT(*) as active FROM seller WHERE role = 'seller'";
$active_sellers_result = mysqli_query($conn, $active_sellers_query);
$active_sellers = mysqli_fetch_assoc($active_sellers_result)['active'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EZ-ORDER | Dashboard</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <img src="../uploads/logo.png" alt="EZ-ORDER Logo" width="150">
            <div class="search">
                <input type="text" placeholder="Search...">
            </div>
            <a href="dashboard.php"><strong>📊 Dashboard</strong></a>
            <a href="seller.php">👤 Seller</a>
            <a href="order.php">📦 Order</a>
           
            <a href="reports.php">📋 Reports</a>
            <div class="logout">
                <a href="logout.php">↩ Logout</a>
            </div>
        </div>

        <div class="main">
            <div class="dashboard-section">
                <h2>Live Orders</h2>
                <div class="dashboard-cards">
                    <div class="card">
                        <div class="icon">🛍️</div>
                        <div class="content">
                            <h3><?php echo $total_orders; ?></h3>
                            <p>Orders</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="icon">🕒</div>
                        <div class="content">
                            <h3><?php echo $pending_orders; ?></h3>
                            <p>Pending</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="icon">✅</div>
                        <div class="content">
                            <h3><?php echo $completed_orders; ?></h3>
                            <p>Completed</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-section">
                <h2>Active Sellers</h2>
                <div class="card">
                    <div class="icon">⭐</div>
                    <div class="content">
                        <h3><?php echo $active_sellers; ?></h3>
                        <p>Total Active Sellers</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-section">
                <h2>Top Stalls</h2>
                <table class="top-stalls">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Ratings</th>
                            <th>Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get top 5 stalls by sales
                        $top_stalls_query = "
                            SELECT 
                                s.stall_name,
                                COUNT(DISTINCT o.id) as total_orders,
                                COALESCE(AVG(sr.rating), 0) as avg_rating,
                                COUNT(DISTINCT o.id) * 100.0 / (SELECT COUNT(*) FROM orders) as sales_percentage
                            FROM seller s
                            LEFT JOIN order_items oi ON s.id = oi.seller_id
                            LEFT JOIN orders o ON oi.order_id = o.id
                            LEFT JOIN stall_reviews sr ON s.id = sr.stall_id
                            WHERE s.role = 'seller'
                            GROUP BY s.id, s.stall_name
                            ORDER BY total_orders DESC
                            LIMIT 5";
                        
                        $top_stalls_result = mysqli_query($conn, $top_stalls_query);
                        $rank = 1;
                        
                        while ($stall = mysqli_fetch_assoc($top_stalls_result)) {
                            $stars = str_repeat('★', round($stall['avg_rating'])) . str_repeat('☆', 5 - round($stall['avg_rating']));
                            echo "<tr>";
                            echo "<td>" . str_pad($rank, 2, '0', STR_PAD_LEFT) . "</td>";
                            echo "<td>" . htmlspecialchars($stall['stall_name']) . "</td>";
                            echo "<td class='stars'>" . $stars . "</td>";
                            echo "<td><span class='badge'>" . number_format($stall['sales_percentage'], 1) . "%</span></td>";
                            echo "</tr>";
                            $rank++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="dashboard-section">
                <h2>Top Products</h2>
                <table class="top-products">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Name</th>
                            <th>Ratings</th>
                            <th>Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get top 5 products by sales
                        $top_products_query = "
                            SELECT 
                                mi.name as product_name,
                                s.stall_name,
                                COUNT(oi.order_id) as total_orders,
                                COALESCE(AVG(sr.rating), 0) as avg_rating,
                                COUNT(oi.order_id) * 100.0 / (SELECT COUNT(*) FROM order_items) as sales_percentage
                            FROM menu_items mi
                            JOIN seller s ON mi.seller_id = s.id
                            LEFT JOIN order_items oi ON mi.id = oi.menu_item_id
                            LEFT JOIN stall_reviews sr ON mi.id = sr.item_id
                            GROUP BY mi.id, mi.name, s.stall_name
                            ORDER BY total_orders DESC
                            LIMIT 5";
                        
                        $top_products_result = mysqli_query($conn, $top_products_query);
                        
                        while ($product = mysqli_fetch_assoc($top_products_result)) {
                            $stars = str_repeat('★', round($product['avg_rating'])) . str_repeat('☆', 5 - round($product['avg_rating']));
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($product['product_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($product['stall_name']) . "</td>";
                            echo "<td class='stars'>" . $stars . "</td>";
                            echo "<td><span class='badge'>" . number_format($product['sales_percentage'], 1) . "%</span></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>