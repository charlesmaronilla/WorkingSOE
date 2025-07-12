<?php
session_start();
include '../includes/db_connect.php';

$is_logged_in = isset($_SESSION['user_id']);

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

$stall_id = isset($_GET['stall_id']) ? intval($_GET['stall_id']) : 0;
if ($stall_id <= 0) {
    die("Invalid stall selected.");
}

// Fetch stall name
$stall_query = mysqli_query($conn, "SELECT stall_name FROM seller WHERE id = $stall_id");
$stall = mysqli_fetch_assoc($stall_query);
if (!$stall) {
    die("Stall not found.");
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $item_id = (int)$_POST['item_id'];
    $name = $_POST['name'];
    $price = (float)$_POST['price'];
    $seller_id = (int)$_POST['seller_id'];
    $quantity = (int)$_POST['quantity'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$item_id] = [
            'name' => $name,
            'price' => $price,
            'seller_id' => $seller_id,
            'quantity' => $quantity
        ];
    }
  
   $_SESSION['add_to_cart_message'] = "Successfully added $quantity $name to cart!";

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$cart_count = 0;
if (!empty($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $item) {
    if (is_array($item) && isset($item['quantity'])) {
      $cart_count += $item['quantity'];
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($stall['stall_name']); ?> Menu</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script src="/Kiosk-System/client/assets/js/quantity.js" defer></script>
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

  </div>

   <div class="content">
 <div class="header">
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

<div class="top-nav">
  <div class="nav-left">
      <div class="user-section">
        <a href="cart.php" class="cart-button">
            <i class="fas fa-shopping-cart cart-icon"></i>
          <span class="cart-count"><?= $cart_count ?></span></a>
      </div>
   


    
    <div class="stall-info-container">
     <h1 class="stall-name">
        <i class="fas fa-store stalls-icon"></i>
       <?php echo htmlspecialchars($stall['stall_name']); ?></h1>
      <a href="ReviewRatings.php?stall_id=<?= $stall_id ?>" class="review-btn">Review & Ratings</a>
    </div>
  </div>
</div>

<div class="content-container">
      <section class="featured-section">
        <div class="featured-card">
          <?php

        $stmt = $conn->prepare("
        SELECT m.id, m.name, m.description, m.image, m.price, m.category, s.stall_name, s.id AS stall_id 
        FROM menu_items m
        JOIN seller s ON m.seller_id = s.id
        WHERE m.available = 1 
          AND m.image IS NOT NULL 
          AND s.id = ?
        ORDER BY RAND()
        LIMIT 10
      ");

        $stmt->bind_param("i", $stall_id);
        $stmt->execute();
        $menu_items_query = $stmt->get_result();


          if ($menu_items_query && mysqli_num_rows($menu_items_query) > 0) {
          ?>
            <div class="slideshow-container">
              <?php
              $slide_dashboard = 0;
              while ($item = mysqli_fetch_assoc($menu_items_query)) {
                if (!isset($item['image']) || !isset($item['name']) || !isset($item['price']) || !isset($item['stall_name'])) {
                    continue; 
                }
                $image_path = $item['image'];
                if ($image_path && strpos($image_path, 'uploads/') === 0) {
                  $image_path = '../' . $image_path;
                }
                $slide_dashboard++;
              ?>
                <div class="slide fade" style="display: <?= $slide_dashboard === 1 ? 'block' : 'none' ?>;">
                  <img src="<?= htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 100%; height: 400px; object-fit: cover;">
                  <div class="slide-caption">
                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                    <p><?= htmlspecialchars($item['description']) ?> - <span class="category"><?= htmlspecialchars($item['category']) ?></span></p>
                    <p class="price">₱<?= number_format($item['price'], 2) ?></p>
                  </div>
                </div>
              <?php } ?>


              <a class="prev" onclick="changeSlide(-1)">❮</a>
              <a class="next" onclick="changeSlide(1)">❯</a>
            </div>
            
            <style>
              .featured-section {
                padding: 20px;
                max-width: 1200px;
                margin: 0 auto;
              }

              .featured-card {
                background: #fff;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                height: 400px;
              }

              .slideshow-container {
                position: relative;
                width: 100%;
                height: 400px;
                margin: auto;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
              }
              
              .slide {
                display: none;
                position: relative;
                width: 100%;
                height: 400px;
                animation: fade 1.5s ease-in-out;
              }

              .slide img {
                width: 100%;
                height: 100%;
                object-fit: cover;
              }
              
              .slide-caption {
                position: absolute;
                bottom: 20px;
                left: 20px;
                max-width: 60%;
                color: white;
                padding: 15px 20px;
                text-align: left;
                border-radius: 8px;
                background: linear-gradient(135deg, rgba(0,0,0,0.7), transparent);
              }
              
              .slide-caption h3 {
                margin: 0 0 5px 0;
                font-size: 2em;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.9),
                           -1px -1px 0 rgba(0,0,0,0.7),  
                           1px -1px 0 rgba(0,0,0,0.7),
                           -1px 1px 0 rgba(0,0,0,0.7),
                           1px 1px 0 rgba(0,0,0,0.7);
                font-weight: bold;
                letter-spacing: 0.5px;
              }
              
              .slide-caption .price {
                color: #ffd700;
                font-size: 1.8em;
                margin: 8px 0;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.9),
                           -1px -1px 0 rgba(0,0,0,0.7),
                           1px -1px 0 rgba(0,0,0,0.7),
                           -1px 1px 0 rgba(0,0,0,0.7),
                           1px 1px 0 rgba(0,0,0,0.7);
                font-weight: bold;
              }
              
              .slide-caption .stall {
                font-size: 1.2em;
                margin: 5px 0 0 0;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.9);
                opacity: 0.9;
              }
              

              .slide-caption h3,
              .slide-caption .price {
                animation: textGlow 2s ease-in-out infinite alternate;
              }
              
              @keyframes textGlow {
                from {
                  filter: drop-shadow(0 0 2px rgba(255,255,255,0.3));
                }
                to {
                  filter: drop-shadow(0 0 5px rgba(255,255,255,0.5));
                }
              }
              
              .prev, .next {
                cursor: pointer;
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                padding: 16px;
                color: white;
                font-weight: bold;
                font-size: 18px;
                transition: 0.6s ease;
                border-radius: 0 3px 3px 0;
                user-select: none;
                background: rgba(0, 0, 0, 0.3);
                text-decoration: none;
              }
              
              .next {
                right: 0;
                border-radius: 3px 0 0 3px;
              }
              
              .prev {
                left: 0;
              }
              
              .prev:hover, .next:hover {
                background-color: rgba(0, 0, 0, 0.8);
              }
              
              @keyframes fade {
                from {opacity: 0.4}
                to {opacity: 1}
              }
            </style>
            
            <script>
              let slidedashboard = 0;
              const slides = document.getElementsByClassName("slide");
              let slideTimer;
              
              function changeSlide(n) {

                clearTimeout(slideTimer);
                

                for (let i = 0; i < slides.length; i++) {
                  slides[i].style.display = "none";
                }
                

                slidedashboard += n;
                if (slidedashboard > slides.length) {
                  slidedashboard = 1;
                }
                if (slidedashboard < 1) {
                  slidedashboard = slides.length;
                }
                

                slides[slidedashboard - 1].style.display = "block";
                

                slideTimer = setTimeout(() => changeSlide(1), 3000);
              }
              
              function showSlides() {
                changeSlide(1);
              }
              

              document.addEventListener('DOMContentLoaded', showSlides);
            </script>
          <?php } else { ?>
            <div class="no-featured">
              <p>No items available at the moment.</p>
            </div>
          <?php } ?>
        </div>
      </section>

      <style>
        .featured-section {
          padding: 20px;
        }
        .featured-card {
          background: #fff;
          border-radius: 8px;
          overflow: hidden;
          box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .featured-image {
          position: relative;
        }
      </style>


<section class="menu-section">
        <h2 class="section-title">Menu Items</h2>
        <div class="category-filters">
          <button class="category-btn active" data-category="all">All</button>
          <button class="category-btn" data-category="meal">Meals</button>
          <button class="category-btn" data-category="drinks">Drinks</button>

          <form method="GET" action="" class="search-form">
  <div class="search-container menu-search">
        <i class="fas fa-search search-icon"></i>
        <input type="text" name="search" placeholder="Search food..." class="search-input"
          value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        
        <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
          <a href="?stall_id=<?= (int)$stall_id ?>" class="clear-search" title="Clear search">
            <i class="fas fa-times"></i>
          </a>
        <?php endif; ?>
        </div>

      <input type="hidden" name="category" value="<?= isset($_GET['category']) ? htmlspecialchars($_GET['category']) : 'all' ?>">
      <input type="hidden" name="stall_id" value="<?= isset($_GET['stall_id']) ? (int)$_GET['stall_id'] : (int)$stall_id ?>">
    </form>
   </div>

        <div class="menu-grid">
          <?php
          $category_condition = isset($_GET['category']) && $_GET['category'] !== 'all' ? "AND m.category = '" . mysqli_real_escape_string($conn, $_GET['category']) . "'" : "";
          $search_condition = '';
          if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = mysqli_real_escape_string($conn, $_GET['search']);
            $search_condition = "AND (m.name LIKE '%$search%' OR m.description LIKE '%$search%' OR m.category LIKE '%$search%' OR s.stall_name LIKE '%$search%')";
          }

         $menu_query = mysqli_query($conn, "
          SELECT m.id, m.name, m.description, m.image, m.price, m.category, s.stall_name, s.id AS stall_id 
          FROM menu_items m
          JOIN seller s ON m.seller_id = s.id
          WHERE m.available = 1 
            AND s.id = " . intval($stall_id) . "
            $category_condition 
            $search_condition
          ORDER BY m.name
          LIMIT 50
        ");


          if (!$menu_query) {
            echo "<!-- Menu query error: " . mysqli_error($conn) . " -->";
          }

          if ($menu_query && mysqli_num_rows($menu_query) > 0) {
            while ($item = mysqli_fetch_assoc($menu_query)) {
              $item_image_path = $item['image'];
              

              echo "<!-- Menu Item Debug:
              ID: " . $item['id'] . "
              Name: " . $item['name'] . "
              Image from DB: " . $item_image_path . "
              -->";
              

              if (strpos($item_image_path, 'uploads/') === 0) {
                  $item_image_path = basename($item_image_path);
              }
              

              if (empty($item_image_path) || !file_exists('../uploads/menu_items/' . $item_image_path)) {
                  $item_image_path = 'assets/images/placeholder.png';
                  echo "<!-- Using placeholder image. Image not found in uploads/menu_items/" . $item_image_path . " -->";
              } else {
                  $item_image_path = '../uploads/menu_items/' . $item_image_path;
                  echo "<!-- Using image from: " . $item_image_path . " -->";
              }
          ?>
              <div class="menu-card" data-category="<?= strtolower($item['category']) ?>">
                <div class="menu-card-image" style="background-image: url('<?= htmlspecialchars($item_image_path) ?>')"></div>
                <div class="menu-card-content">
                  <div class="menu-card-header">
                    <h4><?= htmlspecialchars($item['name']) ?></h4>
                    <span class="category-tag"><?= ucfirst(htmlspecialchars($item['category'])) ?></span>
                  </div>
                  <p class="menu-description"><?= htmlspecialchars($item['description']) ?></p>
                  <div class="menu-location">
                    <i class="fas fa-location-dot"></i>
                    <span><?= htmlspecialchars($item['stall_name']) ?></span>
                  </div>
                  <div class="menu-card-footer">
                    <span class="menu-price">₱<?= number_format($item['price'], 2) ?></span>
                    <form method="POST" action="" class="add-to-cart-form">
                      <input type="hidden" name="add_to_cart" value="1">
                      <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                      <input type="hidden" name="name" value="<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>">
                      <input type="hidden" name="price" value="<?= (float)$item['price'] ?>">
                      <input type="hidden" name="seller_id" value="<?= (int)$item['stall_id'] ?>">
                      <div class="quantity-control">
                        <button type="button" class="quantity-btn minus">-</button>
                        <input type="number" name="quantity" value="1" min="1" class="quantity-input">
                        <button type="button" class="quantity-btn plus">+</button>
                      </div>
                      <button type="submit" class="add-to-cart-btn small" title="Add to cart">
                        <i class="fas fa-cart-plus"></i>
                      </button>
                    </form>
                  </div>
                </div>
              </div>
          <?php
            }
          } else {
            echo '<p class="no-items">No menu items found matching your search.</p>';
          }
          ?>
        </div>
      </section>
    </div>
    <?php
                          
    $suggested_query = mysqli_query($conn, "
        SELECT m.id, m.name, m.image, m.price, m.category, s.stall_name, s.id AS seller_id 
        FROM menu_items m
        JOIN seller s ON m.seller_id = s.id
        WHERE m.available = 1 AND m.is_visible = 1 $category_condition 
        ORDER BY RAND() 
        LIMIT 4
    ");
    
    if ($suggested_query && mysqli_num_rows($suggested_query) > 0):
      while ($sugg = mysqli_fetch_assoc($suggested_query)):
        if (!isset($sugg['image']) || !isset($sugg['name']) || !isset($sugg['price']) || 
            !isset($sugg['category']) || !isset($sugg['stall_name'])) {
            continue; 
        }
        
        $image_path = $sugg['image'];
        if (!$image_path || !file_exists('../' . $image_path)) {
          $image_path = 'assets/images/placeholder.png';
        } else {
          $image_path = '../' . $image_path;
        }
    ?>
      <?php endwhile;
    else: ?>
    <?php endif; ?>
  </div>
  </section>
  </div>
  </div>
</body>

</html>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const categoryButtons = document.querySelectorAll('.category-btn');
    const menuCards = document.querySelectorAll('.menu-card');
    const menuGrid = document.querySelector('.menu-grid');

    let noItemsMsg = document.querySelector('.no-items');
    if (!noItemsMsg) {
      noItemsMsg = document.createElement('p');
      noItemsMsg.className = 'no-items';
      noItemsMsg.style.display = 'none';
      noItemsMsg.textContent = 'No menu items found in this category.';
      menuGrid.appendChild(noItemsMsg);
    }

    function filterByCategory(category) {
      let hasVisibleItems = false;

      menuCards.forEach(card => {
        const cardCategory = card.getAttribute('data-category');
        const showCard = category === 'all' || cardCategory === category;
        card.style.display = showCard ? 'block' : 'none';
        if (showCard) hasVisibleItems = true;
      });

      noItemsMsg.style.display = hasVisibleItems ? 'none' : 'block';

      categoryButtons.forEach(btn => btn.classList.remove('active'));
      const activeBtn = [...categoryButtons].find(btn => btn.getAttribute('data-category') === category);
      activeBtn?.classList.add('active');

      const url = new URL(window.location);
      if (category === 'all') {
        url.searchParams.delete('category');
      } else {
        url.searchParams.set('category', category);
      }
      window.history.pushState({}, '', url);
    }

    categoryButtons.forEach(button => {
      button.addEventListener('click', () => {
        const category = button.getAttribute('data-category');
        filterByCategory(category);
      });
    });

    const urlParams = new URLSearchParams(window.location.search);
    const initialCategory = urlParams.get('category') || 'all';
    filterByCategory(initialCategory);
  });

  document.addEventListener('DOMContentLoaded', function () {
    <?php if (isset($_SESSION['add_to_cart_message'])): ?>
      showCartMessage(<?= json_encode($_SESSION['add_to_cart_message']) ?>);
      <?php unset($_SESSION['add_to_cart_message']); ?>
    <?php endif; ?>
  });

  function showCartMessage(message) {
    const cartMessage = document.getElementById('cart-message');
    const messageText = document.getElementById('cart-message-text');

    if (cartMessage && messageText) {
      messageText.textContent = message;
      cartMessage.style.display = 'flex';
      cartMessage.classList.add('show');

      // Automatically hide after 4s
      setTimeout(() => {
        cartMessage.style.animation = 'fadeOut 0.4s ease-out forwards';
        setTimeout(() => {
          cartMessage.style.display = 'none';
          cartMessage.classList.remove('show');
          cartMessage.style.animation = '';
        }, 400);
      }, 4000);
    }
  }


</script>

<style>


  /* Cart Message */
  .cart-message {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background:rgb(173, 201, 48);
    color: rgb(86, 90, 70);
    padding: 15px 25px;
    border-radius: 4px;
    font-size: 16px;
    z-index: 99999;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: none;
    align-items: center;
    gap: 10px;
    animation: slideIn 0.5s ease-out forwards;
  }
  
  @keyframes slideIn {
    from { top: -100px; opacity: 0; }
    to { top: 20px; opacity: 1; }
  }
  
  @keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; top: -100px; }
  }
  
  .cart-message i {
    font-size: 20px;
  }
</style>

<!-- Simple Cart Message -->
<div id="cart-message" class="cart-message">
  <i class="fas fa-check-circle"></i>
  <span id="cart-message-text"></span>
</div>

<script>
// Quantity control functionality
document.addEventListener('DOMContentLoaded', function() {
    function handleQuantityChange(input, change) {
        const currentValue = parseInt(input.value) || 0;
        const newValue = currentValue + change;
        const min = parseInt(input.getAttribute('min') || 1);
        
        if (newValue >= min) {
            input.value = newValue;
        }
    }

    // Quantity control buttons
    document.addEventListener('click', function(e) {
        // Handle minus button
        if (e.target.matches('.quantity-btn.minus')) {
            const input = e.target.closest('.quantity-control').querySelector('.quantity-input');
            if (input) handleQuantityChange(input, -1);
        }
        // Handle plus button
        else if (e.target.matches('.quantity-btn.plus')) {
            const input = e.target.closest('.quantity-control').querySelector('.quantity-input');
            if (input) handleQuantityChange(input, 1);
        }
    });

    // Ensure quantity doesn't go below minimum
    document.addEventListener('change', function(e) {
        if (e.target.matches('.quantity-input')) {
            const min = parseInt(e.target.getAttribute('min') || 1);
            if (e.target.value < min) {
                e.target.value = min;
            }
        }
    });
});
</script>

<!-- Order Status Notification Script -->
<script>
function showNotification(message) {
    if (document.getElementById('order-ready-notif')) return;
    
    const notification = document.createElement('div');
    notification.id = 'order-ready-notif';
    notification.className = 'order-ready-notification';
    notification.innerHTML = '<i class="fas fa-bell"></i> ' + message;
    
    // Add styles if not already added
    if (!document.getElementById('order-notification-styles')) {
        const style = document.createElement('style');
        style.id = 'order-notification-styles';
        style.textContent = `
            @keyframes slideDown {
                from { transform: translate(-50%, -100%); opacity: 0; }
                to { transform: translate(-50%, 0); opacity: 1; }
            }
            @keyframes slideUp {
                from { transform: translate(-50%, 0); opacity: 1; }
                to { transform: translate(-50%, -100%); opacity: 0; }
            }
            .order-ready-notification {
                position: fixed;
                top: 0;
                left: 50%;
                transform: translateX(-50%);
                background: #4caf50;
                color: white;
                padding: 16px 32px;
                border-radius: 0 0 8px 8px;
                font-size: 18px;
                z-index: 99999;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                gap: 10px;
                animation: slideDown 0.3s ease-out;
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideUp 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

function checkOrderStatus() {
    fetch('check_order_status.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.status === 'Ready') {
                showNotification('Your order is ready to be claimed!');
            }
        })
        .catch(error => console.error('Error checking order status:', error));
}

// Check order status every 5 seconds
setInterval(checkOrderStatus, 5000);
</script>
</body>

</html>



