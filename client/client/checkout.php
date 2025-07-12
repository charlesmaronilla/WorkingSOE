<?php
session_start();
include '../includes/db_connect.php';

define('PAYMONGO_PUBLIC_KEY', 'pk_test_7HMZjMgQ1Ct9dkNkQ9AugX99');

$is_logged_in = isset($_SESSION['user_id']);

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
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

error_log("Checkout: Session at page load: " . print_r($_SESSION, true));

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

$is_reservation = isset($_SESSION['is_reservation']) ? $_SESSION['is_reservation'] : false;
$reservation_date = isset($_SESSION['reservation_date']) ? $_SESSION['reservation_date'] : '';
$reservation_time = isset($_SESSION['reservation_time']) ? $_SESSION['reservation_time'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EZ-Order | Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
              background: rgb(227, 235, 235);
            margin: 0;
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

        .sidebar {
            width: 240px;
            background-color: rgba(0, 43, 92, 0.9);
            border-radius: 10px;
            margin-top: 10px;
            color: white;
            height: 100vh;
            padding: 20px;
            position: fixed;
            top: 60px;
            left: 0;
        }

        .sidebar .divider {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin: 1rem auto;
            width: 90%;
        }

        .logoo-wrapper {
            text-align: center;
            padding: 20px 0;
        }

        .logoo-wrapper img {
            position: relative;
            display: inline-block;
            margin-left: -10px;
            height: 150px;
            width: 190px;
        }

        .logoo {
            color: white;
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }

        .tagline {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            font-style: italic;
            margin-bottom: 20px;
        }

        .sidebar h2 {
            font-size: 20px;
            margin: 20px 0 15px 0;
            color: white;
        }
        .menu-title {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            font-weight: 600;
            margin: 25px 0 15px;
            padding: 0 10px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 5px 0;
            transition: all 0.3s;
        }

        .menu-item i {
            margin-right: 12px;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        .menu-item:hover, .menu-item.active {
            background: rgba(255, 255, 255, 0.1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: white;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
             width: 1300px;
             margin-top: 70px;
             margin-left: 80px;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .back-button {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            background: #186479;
            color: white;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }

        .back-button:hover {
            background: #1e7b94;
        }

        .back-button i {
            margin-right: 8px;
        }

        h1 {
            color: #186479;
            font-size: 24px;
            margin: 0;
        }

        .cart-section {
           background: rgb(207, 205, 205);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            width: 1300px;
            margin-left: 80px;

        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }

        .checkout-form {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section h2 {
            color: #186479;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #186479;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #186479;
        }

        .order-summary {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .order-summary h2 {
            color: #186479;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .order-items {
            margin-bottom: 20px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 500;
            color: #186479;
            margin-bottom: 5px;
        }

        .item-price {
            color: #666;
            font-size: 14px;
        }

        .item-quantity {
            color: #186479;
            font-weight: 500;
            margin-left: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .summary-row:last-child {
            border-bottom: none;
            font-weight: bold;
        }

        .reservation-info {
            color: #186479;
            font-size: 14px;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .reservation-info:last-child {
            border-bottom: none;
        }

        .submit-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: #186479;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 20px;
        }

        .submit-btn:hover {
            background: #1e7b94;
        }

        .error-message {
            color: #ff4444;
            font-size: 14px;
            margin-top: 5px;
        }

        .success-message {
            color: #186479;
            font-size: 14px;
            margin-top: 5px;
        }

        .reservation-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .reservation-details p {
            margin: 8px 0;
            color: #186479;
        }

        .reservation-details strong {
            color: #186479;
            font-weight: 600;
        }

        .payment-section {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .payment-method {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            background: white;
        }

        .payment-method:hover {
            border-color: #186479;
            background: #f0f7fa;
        }

        .payment-method.selected {
            border-color: #186479;
            background: #e6f3f7;
        }

        .payment-method i {
            font-size: 24px;
            color: #186479;
            margin-bottom: 10px;
        }

        .payment-method p {
            margin: 0;
            color: #186479;
            font-size: 14px;
        }

        #paymongo-payment-form {
            margin-top: 20px;
            display: none;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        #card-element {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }

        #card-errors {
            color: #dc3545;
            margin-top: 10px;
            font-size: 14px;
        }

        .submit-button {
            background: #186479;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            width: 100%;
            margin-top: 20px;
            transition: background 0.3s ease;
        }

        .submit-button:hover {
            background: #1e7b94;
        }

        .submit-button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }

        .payment-options {
            margin-top: 15px;
        }

        .payment-option {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            transition: all 0.3s ease;
        }

        .payment-option:hover {
            border-color: #186479;
            background: #f8f9fa;
        }

        .payment-option input[type="radio"] {
            display: none;
        }

        .payment-option label {
            display: flex;
            align-items: center;
            cursor: pointer;
            margin: 0;
            color: #186479;
        }

        .payment-option i {
            font-size: 24px;
            margin-right: 15px;
            color: #186479;
        }

        .payment-description {
            display: block;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            margin-left: 39px;
        }

        .payment-option input[type="radio"]:checked + label {
            font-weight: bold;
        }

        .payment-option input[type="radio"]:checked + label i {
            color: #186479;
        }
    </style>
    <script src="https://js.paymongo.com/v1"></script>
</head>
<body>
      <header class="admin-header">
    <div class="header-brand">
      <img src="assets/logo1.png" alt="EZ-Order" class="header-logo">
    </div>
  </header>

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logoo-wrapper">
      <img src="assets/logo.png" alt="EZ-Order Logo" class="sidebar-logo">
      <div class="logoo">EZ-ORDER</div>
      <div class="divider"></div>
      <div class="tagline">"easy orders, zero hassle"</div>
    </div>

    <div class="menu-title"></div>
    <h2>🍽 Stalls</h2>
   <?php $sidebar_stalls_query = mysqli_query($conn, "SELECT id, stall_name FROM seller LIMIT 2");
    if ($sidebar_stalls_query && mysqli_num_rows($sidebar_stalls_query) > 0) {
      while ($sidebar_stall = mysqli_fetch_assoc($sidebar_stalls_query)) {
        echo '<a href="menu.php?stall_id=' . (int)$sidebar_stall['id'] . '" class="menu-item">';
        echo '    <i class="fas fa-utensils"></i>';
        echo '    <span>' . htmlspecialchars($sidebar_stall['stall_name']) . '</span>';
        echo '</a>';
      }
    } else {
      echo '<div class="menu-item"><i class="fas fa-info-circle"></i> No stalls available</div>';
    }?>

   <?php if ($is_logged_in): ?>
    <div class="menu-title"></div>
    <h2>📋 Orders</h2>
    <a href="order_history.php" class="menu-item">
      <i class="fas fa-history"></i>
      <span>Order History</span>
    </a>
    <?php endif; ?>
  </div>

    <div class="container">
        <div class="top-nav">
            <div class="nav-left">
                <a href="cart.php" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    Back to Cart
                </a>
                <h1>Checkout</h1>
            </div>
        </div>
        
        <div class="cart-section">
        <div class="checkout-grid">
            <div class="checkout-form">
                <form id="checkout-form" method="POST" action="submit_order.php">
                    <div class="form-section">
                        <h2>Order Details</h2>
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="special_request">Special Requests (Optional)</label>
                            <textarea id="special_request" name="special_request" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="payment-section">
                        <h2>Payment Method</h2>
                        <div class="payment-options">
                            <div class="payment-option">
                                <input type="radio" id="pay_online" name="payment_type" value="online" checked>
                                <label for="pay_online">
                                    <i class="fas fa-credit-card"></i>
                                    Pay Online
                                    <span class="payment-description">Pay securely through PayMongo</span>
                                </label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" id="pay_counter" name="payment_type" value="counter">
                                <label for="pay_counter">
                                    <i class="fas fa-store"></i>
                                    Pay at Counter
                                    <span class="payment-description">Pay when you receive your order</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="submit-button" id="submit-button">Place Order</button>
                </form>
            </div>

            <div class="order-summary">
                <h2>Order Summary</h2>
                <div class="order-items">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="order-item">
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-price">₱<?php echo number_format($item['price'], 2); ?></div>
                        </div>
                        <div class="item-quantity">x<?php echo $item['quantity']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="summary-row">
                    <span>Total</span>
                    <span>₱<?php echo number_format($total, 2); ?></span>
                </div>
            </div>
        </div>
     </div>
    </div>

    <script>
        const submitButton = document.getElementById('submit-button');
        document.getElementById('checkout-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';

            const paymentType = document.querySelector('input[name="payment_type"]:checked').value;

            try {
                if (paymentType === 'online') {
                    const formData = new FormData(e.target);
                    formData.set('payment_type', 'online');
                    
                    const orderResponse = await fetch('submit_order.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (!orderResponse.ok) {
                        throw new Error('Network response was not ok');
                    }
                    
                    const orderResult = await orderResponse.json();
                    
                    if (!orderResult.success) {
                        throw new Error(orderResult.message || 'Failed to create order');
                    }
                    
                    const paymentResponse = await fetch('payment_process.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `amount=${<?php echo $total; ?>}&order_id=${orderResult.order_id}`
                    });
                    
                    if (!paymentResponse.ok) {
                        throw new Error('Network response was not ok');
                    }
                    
                    const paymentData = await paymentResponse.json();
                    
                    if (!paymentData.success) {
                        throw new Error(paymentData.message || 'Failed to create payment link');
                    }
                    
                    window.location.href = paymentData.payment_link;
                } else {
                    const formData = new FormData(e.target);
                    formData.set('payment_type', 'counter');
                    
                    const response = await fetch('submit_order.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    
                    const result = await response.json();
                    
                    if (!result.success) {
                        throw new Error(result.message || 'Failed to place order');
                    }
                    
                    window.location.href = 'order_confirmation.php?id=' + result.order_id;
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'An error occurred while processing your order. Please try again or contact support.');
                submitButton.disabled = false;
                submitButton.textContent = 'Place Order';
            }
        });
    </script>
</body>
</html>
