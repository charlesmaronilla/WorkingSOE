<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id <= 0) {
    die('Invalid order ID.');
}

$user_id = $_SESSION['user_id'];
$user_query = "SELECT name, email FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);

if (!$user) {
    header('Location: login.php');
    exit();
}

$order_query = "SELECT o.*, s.stall_name FROM orders o LEFT JOIN seller s ON o.seller_id = s.id WHERE o.id = ? AND o.client_name = ?";
$stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($stmt, "is", $order_id, $user['name']);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    die('Order not found or you do not have permission to view this order.');
}

$items_query = "SELECT oi.*, m.name, m.price, s.stall_name FROM order_items oi JOIN menu_items m ON oi.menu_item_id = m.id JOIN seller s ON oi.seller_id = s.id WHERE oi.order_id = ? ORDER BY s.stall_name, m.name";
$stmt = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$items_result = mysqli_stmt_get_result($stmt);

$order_items = [];
while ($item = mysqli_fetch_assoc($items_result)) {
    $order_items[] = $item;
}

$total = 0;
foreach ($order_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - EZ-Order</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: rgb(227, 235, 235);
            min-height: 100vh;
            padding: 20px;
        }

        .admin-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background-color: #186479;
            color: white;
            display: flex;
            align-items: center;
            padding: 0 20px;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-brand {
            display: flex;
            align-items: center;
        }

        .header-logo {
            margin-left: -10px;
            margin-top: 5px;
            height: 60px;
            object-fit: contain;
        }

    .confirmation-container {
        max-width: 600px;
        margin: 100px auto 40px;
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        overflow: hidden;
    }

    .confirmation-header {
        background-color: #094d52;
        color: white;
        padding: 30px 20px;
        text-align: center;
        position: relative;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }

    .confirmation-header i {
             font-size: 90px;
            color:rgb(201, 198, 54);
            margin-bottom: 20px;
    }

    .confirmation-header h1 {
        font-size: 24px;
        margin: 10px 0 5px;
    }

    .confirmation-header p {
        font-size: 14px;
        opacity: 0.9;
    }

    .guest-notice {
        background: #fff3cd;
        color: #856404;
        border-radius: 8px;
        padding: 15px;
        font-size: 14px;
        margin: 20px;
        border: 1px solid #ffeeba;
    }

    .order-info {
        background: #f1f1f1;
        border-radius: 10px;
        padding: 20px;
        margin: 20px;
    }

    .order-info p {
        margin: 6px 0;
        font-size: 14px;
    }

    .status-badge {
        background: #ffe699;
        color: #856404;
        font-size: 13px;
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: bold;
        display: inline-block;
    }

    .order-summary {
        padding: 0 20px 20px;
    }

    .order-summary h2 {
        font-size: 18px;
        color: #134d5d;
        margin-bottom: 10px;
    }

    .order-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        font-size: 14px;
        border-bottom: 1px solid #eee;
    }

    .order-item:last-child {
        border-bottom: none;
    }

    .summary-total {
        background: #094d52;
        color: white;
        text-align: center;
        font-size: 20px;
        padding: 20px;
        border-radius: 10px;
        margin: 20px;
        font-weight: bold;
    }

    .summary-total span {
        display: block;
        font-size: 32px;
        margin-top: 5px;
    }

    .actions {
        display: flex;
        justify-content: space-around;
        padding: 0 20px 30px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .actions a {
        flex: 1 1 30%;
        text-align: center;
        padding: 12px 0;
        font-weight: bold;
        font-size: 14px;
        border-radius: 8px;
        text-decoration: none;
        border: none;
        transition: 0.2s ease;
    }

    .actions .home-btn {
        background: #134d5d;
        color: white;
    }

    .actions .track-btn {
        background: #fff;
        border: 2px solid #134d5d;
        color: #134d5d;
    }

    .actions .create-btn {
        background: #495057;
        color: white;
    }
        .back-link {
             display: inline-block;
            padding: 10px 20px;
            background-color: #45576b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            margin-bottom: 30px;
            margin-left: 10px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="header-brand">
            <img src="assets/logo1.png" alt="EZ-Order" class="header-logo">
        </div>
    </header>
    <div class="confirmation-container">
        <div class="confirmation-header">
                <i class="fas fa-check-circle"></i>
            <h1>Thank You for Your Order!</h1>
            <p>Your order has been placed successfully.</p>
        </div>
        <div class="order-info">
            <p><strong>Order Number:</strong> #<?php echo $order['id']; ?></p>
            <p><strong>Status:</strong> <span class="status-badge"><?php echo htmlspecialchars($order['status']); ?></span></p>
            <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['order_time'])); ?></p>
            <?php if (!empty($order['special_request'])): ?>
                <p><strong>Special Request:</strong> <?php echo htmlspecialchars($order['special_request']); ?></p>
            <?php endif; ?>
            <?php if ($order['is_reservation']): ?>
                <p><strong>Order Type:</strong> Advance Order</p>
                <p><strong>Pick-up Date:</strong> <?php echo date('F j, Y', strtotime($order['reservation_date'])); ?></p>
                <p><strong>Pick-up Time:</strong> <?php echo date('g:i A', strtotime($order['reservation_time'])); ?></p>
            <?php endif; ?>
            <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($order['payment_status']); ?></p>
        </div>
        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="order-items">
                <?php if (count($order_items) > 0): ?>
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                            <span>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="order-item">No items found for this order.</div>
                <?php endif; ?>
            </div>
            <div class="summary-row">
                <span>Total</span>
                <span>₱<?php echo number_format($total, 2); ?></span>
            </div>
        </div>
        <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</body>
</html>
