<?php
session_start();
include '../includes/db_connect.php';

$stall_id = isset($_GET['stall_id']) ? intval($_GET['stall_id']) : 0;
if ($stall_id <= 0) {
    die("Invalid stall selected.");
}
$is_logged_in = isset($_SESSION['user_id']);

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

$review_success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stall_id = isset($_POST['stall_id']) ? intval($_POST['stall_id']) : 0;
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';

    $item_id = null;

    if ($stall_id <= 0 || $rating <= 0 || empty($feedback)) {
        $error_message = "Invalid input.";
    } else {
        $stmt = $conn->prepare("INSERT INTO stall_reviews (stall_id, item_id, rating, feedback, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiis", $stall_id, $item_id, $rating, $feedback);

        if ($stmt->execute()) {
            $review_success = true;
        } else {
            $error_message = "Error saving review: " . $stmt->error;
        }

        $stmt->close();
    }
}



// Fetch stall name
$stall_query = mysqli_query($conn, "SELECT stall_name FROM seller WHERE id = $stall_id");
$stall = mysqli_fetch_assoc($stall_query);
if (!$stall) {
    die("Stall not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($stall['stall_name']); ?> Menu</title>
  <link rel="stylesheet" href="assets/css/reviews.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script src="/Kiosk-System/client/assets/js/quantity.js" defer></script>
</head>
<style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f5ff;
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

        .content {
                    padding: 90px 30px 30px;
                    margin-left: 280px;
                    background: #f8fafc;
                    
                }

                .reviews-container {
                 
                    max-width: 1200px;
                    margin: 0 auto;
                }

                .page-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 70px;
                }

                .back-button {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    text-decoration: none;
                    color: #1e3c72;
                    font-weight: 600;
                    padding: 8px 16px;
                    border-radius: 8px;
                    transition: all 0.3s ease;
                    background: rgba(30, 60, 114, 0.05);
                }

                .back-button:hover {
                    transform: translateX(-5px);
                    background: rgba(30, 60, 114, 0.1);
                }

                .stall-title {
                    color: #1e3c72;
                    font-size: 32px;
                    margin: 0;
                    font-weight: 700;
                }

                .menu-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                    gap: 25px;
                    margin-top: 30px;
                }

                .menu-card {
                    background: white;
                    border-radius: 15px;
                    overflow: hidden;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                    transition: transform 0.3s ease;
                }

                .menu-card:hover {
                    transform: translateY(-5px);
                }

               
                .reviews-grid {
                  display: flex;
                  flex-wrap: wrap;
                  gap: 25px;
                  justify-content: left;
                }

                /* Review Card */
                .review-card {
                  background: #ffffff;
                  width: 300px;
                  padding: 20px;
                  border-radius: 15px;
                  box-shadow: 0 8px 16px rgba(0,0,0,0.05);
                  display: flex;
                  flex-direction: column;
                  gap: 10px;
                }

                .review-rating .star {
                  font-size: 20px;
                  color: #ccc;
                }

                .review-rating .star.filled {
                  color: #ffc107;
                }

                .review-feedback {
                  font-size: 14px;
                  color: #444;
                }

                .review-date {
                  font-size: 12px;
                  color: #888;
                }

                /* No review text */
                .no-reviews {
                  text-align: center;
                  color: #777;
                  font-style: italic;
                  margin-top: 30px;
                }

                .write-review-btn {
                  display: inline-block;
                  margin: 20px auto;
                  background-color: #1a237e;
                  color: white;
                  padding: 12px 20px;
                  border: none;
                  border-radius: 8px;
                  font-weight: 600;
                  cursor: pointer;
                  transition: background 0.2s;
                }

                .write-review-btn:hover {
                  background-color: #0d1b4c;
                }

                .modal {
                  display: none;
                  position: fixed;
                  z-index: 999;
                  left: 0; top: 0;
                  width: 100%; height: 100%;
                  background: rgba(0,0,0,0.5);
                  justify-content: center;
                  align-items: center;
                }

                .modal-content {
                  background: white;
                  padding: 30px 25px;
                  border-radius: 15px;
                  width: 90%;
                  max-width: 500px;
                  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                  position: relative;
                }

                .modal h3 {
                  margin-bottom: 15px;
                  font-size: 22px;
                  color: #1a237e;
                  text-align: center;
                }

                /* Modal Form */
                .modal form label {
                  font-weight: 600;
                  margin-bottom: 6px;
                  display: block;
                }

                .modal textarea {
                  width: 100%;
                  padding: 10px;
                  border-radius: 8px;
                  border: 1px solid #ccc;
                  resize: vertical;
                  font-size: 14px;
                }

                /* Star Rating Input */
                .star-rating {
                  display: flex;
                  flex-direction: row-reverse;
                  justify-content: center;
                  gap: 5px;
                  margin-bottom: 15px;
                }

                .star-rating input {
                  display: none;
                }

                .star-rating label {
                  font-size: 22px;
                  color: #ccc;
                  cursor: pointer;
                }

                .star-rating input:checked ~ label,
                .star-rating label:hover,
                .star-rating label:hover ~ label {
                  color: #ffc107;
                }

                /* Submit Button */
                .submit-review-btn {
                  background-color: #1a237e;
                  color: white;
                  padding: 10px 20px;
                  border: none;
                  border-radius: 8px;
                  margin-top: 10px;
                  font-weight: 600;
                  width: 100%;
                  cursor: pointer;
                }

                .submit-review-btn:hover {
                  background-color: #0d1b4c;
                }

                /* Modal Close Button */
                .modal .close-btn {
                  position: absolute;
                  top: 10px;
                  right: 15px;
                  background: none;
                  border: none;
                  font-size: 20px;
                  color: #888;
                  cursor: pointer;
                }

                .modal .close-btn:hover {
                  color: #333;
                }

                .success-popup {
                  position: fixed;
                  top: 50%;
                  left: 50%;
                  transform: translate(-50%, -50%);
                  background-color: rgb(227, 230, 54);
                  color: rgb(59, 68, 78);
                  padding: 15px 30px;
                  border-radius: 10px;
                  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                  font-weight: bold;
                  z-index: 9999;
                  opacity: 1;
                  transition: opacity 0.5s ease;
                  text-align: center;
                  font-size: 1rem;
                }
                .success-popup.hide {
                  opacity: 0;
                }


</style>
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
      <h2>📋 Orders</h2>
      <a href="order_history.php" class="menu-item">
        <i class="fas fa-history"></i>
        <span>Order History</span>
      </a>
    <?php endif; ?>
  </div>

  <div class="content">
    <div class="reviews-section">
      <div class="page-header">
        <h1 class="stall-title">Reviews & Ratings - <?= htmlspecialchars($stall['stall_name']) ?></h1>
        <a href="menu.php?stall_id=<?= $stall_id ?>" class="back-button">
          <i class="fas fa-arrow-left"></i> Back to Stall
        </a>
      </div>

      <div class="reviews-grid">
        <?php
        $reviews_query = mysqli_query($conn, "SELECT * FROM stall_reviews WHERE stall_id = $stall_id ORDER BY created_at DESC");
        if (mysqli_num_rows($reviews_query) > 0) {
          while ($review = mysqli_fetch_assoc($reviews_query)) {
            echo '<div class="review-card">'
              . '<div class="review-rating">';
            for ($i = 1; $i <= 5; $i++) {
              echo '<span class="star ' . ($i <= $review['rating'] ? 'filled' : '') . '">★</span>';
            }
            echo '</div>'
              . '<p class="review-feedback">' . htmlspecialchars($review['feedback']) . '</p>'
              . '<p class="review-date">' . date('F j, Y', strtotime($review['created_at'])) . '</p>'
              . '</div>';
          }
        } else {
          echo '<p class="no-reviews">No reviews yet. Be the first to review!</p>';
        }
        ?>
      </div>

      <button class="write-review-btn">Leave a Review</button>

        <?php if ($review_success): ?>
          <div id="success-popup" class="success-popup">
            Review submitted successfully!
          </div>
        <?php endif; ?>

        <div class="modal">
          <div class="modal-content">
            <button class="close-btn">&times;</button>
            <h3>Leave a Review</h3>


            <?php if (isset($error_message)): ?>
              <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
            <?php endif; ?>

            <form method="POST">
              <input type="hidden" name="stall_id" value="<?= $stall_id ?>">
              <div class="star-rating">
                <?php for($i = 5; $i >= 1; $i--): ?>
                  <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required>
                  <label for="star<?= $i ?>">★</label>
                <?php endfor; ?>
              </div>
              <label for="feedback">Your Feedback:</label>
              <textarea name="feedback" id="feedback" rows="4" required placeholder="Share your experience..."></textarea>
              <button type="submit" class="submit-review-btn">Submit Review</button>
            </form>
          </div>
        </div>


  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const openModalBtn = document.querySelector('.write-review-btn');
    const modal = document.querySelector('.modal');
    const closeModalBtn = document.querySelector('.close-btn');

    if (openModalBtn && modal && closeModalBtn) {
      openModalBtn.addEventListener('click', () => {
        modal.style.display = 'flex';
      });

      closeModalBtn.addEventListener('click', () => {
        modal.style.display = 'none';
      });

      window.addEventListener('click', function (e) {
        if (e.target === modal) {
          modal.style.display = 'none';
        }
      });
    }


    const popup = document.getElementById('success-popup');
    if (popup) {
      setTimeout(() => {
        popup.classList.add('hide');
        setTimeout(() => popup.remove(), 500); 
      }, 4000);
    }
  });
</script>

</body>
</html>
