<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Fetch users who have placed orders
$users_query = "SELECT DISTINCT 
    o.client_name, 
    o.student_number, 
    o.order_time,
    o.special_request,
    s.stall_name as seller_name
FROM orders o
LEFT JOIN seller s ON o.seller_id = s.id
ORDER BY o.order_time DESC";

$users_result = $conn->query($users_query);
$users = [];
while ($row = $users_result->fetch_assoc()) {
    $users[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EZ-ORDER | Complaints</title>
    
    <link rel="stylesheet" href="assets/css/complaints.css">
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
            <a href="reports.php">📋 Reports</a>
            <div class="logout">
                <a href="logout.php">↩ Logout</a>
            </div>
        </div>

        <div class="main">
            <div class="back-button" onclick="window.location.href='reports.php'">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </div>

            <section class="complaints-section">
                <h2><i class="fas fa-store"></i> Reported Stalls</h2>
                <div class="complaints-grid">
                    <div class="complaint-card" onclick="openStallModal('Stall B', 'Too salty', '10:30 am', '01/09/2024', 'Poor food quality', 'food1.jpg', 'John Doe')">
                        <div class="complaint-icon">
                            <img src="stall-icon.png" alt="Stall Icon">
                        </div>
                        <div class="complaint-info">
                            <h3>Stall B</h3>
                            <p>Poor food quality complaint from customer</p>
                        </div>
                        <div class="complaint-actions">
                            <button class="dismiss-btn" onclick="event.stopPropagation()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

                    <div class="complaint-card" onclick="openStallModal('Stall C', 'Food was served cold and presentation was poor', '11:45 am', '01/09/2024', 'Service quality', 'food2.jpg', 'Mary Smith')">
                        <div class="complaint-icon">
                            <img src="stall-icon.png" alt="Stall Icon">
                        </div>
                        <div class="complaint-info">
                            <h3>Stall C</h3>
                            <p>Service quality issues reported</p>
                        </div>
                        <div class="complaint-actions">
                            <button class="dismiss-btn" onclick="event.stopPropagation()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

                    <div class="complaint-card" onclick="openStallModal('Stall D', 'Portion size is too small for the price charged', '2:15 pm', '01/09/2024', 'Price concern', 'food3.jpg', 'James Wilson')">
                        <div class="complaint-icon">
                            <img src="stall-icon.png" alt="Stall Icon">
                        </div>
                        <div class="complaint-info">
                            <h3>Stall D</h3>
                            <p>Price and portion size complaint</p>
                        </div>
                        <div class="complaint-actions">
                            <button class="dismiss-btn" onclick="event.stopPropagation()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

                    <div class="complaint-card" onclick="openStallModal('Stall E', 'Long waiting time and unfriendly service', '3:30 pm', '01/09/2024', 'Poor service', 'food4.jpg', 'Sarah Johnson')">
                        <div class="complaint-icon">
                            <img src="stall-icon.png" alt="Stall Icon">
                        </div>
                        <div class="complaint-info">
                            <h3>Stall E</h3>
                            <p>Customer service complaint</p>
                        </div>
                        <div class="complaint-actions">
                            <button class="dismiss-btn" onclick="event.stopPropagation()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="complaints-section">
                <h2><i class="fas fa-user"></i> Reported Users</h2>
                <div class="complaints-grid">
                    <?php foreach ($users as $user): ?>
                    <div class="complaint-card" onclick="openUserModal(
                        '<?php echo htmlspecialchars($user['client_name']); ?>', 
                        '<?php echo htmlspecialchars($user['special_request'] ?? 'No special request'); ?>', 
                        '<?php echo date('h:i a', strtotime($user['order_time'])); ?>', 
                        '<?php echo date('m/d/Y', strtotime($user['order_time'])); ?>', 
                        'Order Issue', 
                        '<?php echo htmlspecialchars($user['seller_name']); ?>'
                    )">
                        <div class="complaint-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="complaint-info">
                            <h3><?php echo htmlspecialchars($user['client_name']); ?></h3>
                            <p>Student Number: <?php echo htmlspecialchars($user['student_number']); ?></p>
                        </div>
                        <div class="complaint-actions">
                            <button class="dismiss-btn" onclick="event.stopPropagation()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>

    <div id="stallModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reported by <span id="stallReporter"></span></h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="complaint-details">
                    <div class="details-grid">
                        <div class="detail-row">
                            <label>Full Name</label>
                            <span id="stallName"></span>
                        </div>
                        <div class="detail-row">
                            <label>Time</label>
                            <span id="stallTime"></span>
                        </div>
                        <div class="detail-row">
                            <label>Date</label>
                            <span id="stallDate"></span>
                        </div>
                        <div class="detail-row">
                            <label>Selected Reason</label>
                            <span id="stallReason"></span>
                        </div>
                        <div class="detail-row">
                            <label>Description</label>
                            <div class="description-box">
                                <span id="stallDescription"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="reply-btn" onclick="openReplyModal('stall')">Reply</button>
            </div>
        </div>
    </div>

    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Details from <span id="userReporter"></span></h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="complaint-details">
                    <div class="details-grid">
                        <div class="detail-row">
                            <label>Customer Name</label>
                            <span id="userName"></span>
                        </div>
                        <div class="detail-row">
                            <label>Order Time</label>
                            <span id="userTime"></span>
                        </div>
                        <div class="detail-row">
                            <label>Order Date</label>
                            <span id="userDate"></span>
                        </div>
                        <div class="detail-row">
                            <label>Issue Type</label>
                            <span id="userReason"></span>
                        </div>
                        <div class="detail-row">
                            <label>Special Request</label>
                            <div class="description-box">
                                <span id="userDescription"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="reply-btn" onclick="openReplyModal('user')">Contact Customer</button>
            </div>
        </div>
    </div>

    <div id="replyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reply to <span id="replyTo"></span></h2>
                <span class="close-modal" onclick="closeModal('replyModal')">&times;</span>
            </div>
            <div class="modal-body">
                <textarea class="reply-textarea" placeholder="Type your reply here..."></textarea>
                <button class="send-btn" onclick="sendReply()">Reply</button>
            </div>
        </div>
    </div>

    <div id="successModal" class="modal">
        <div class="modal-content success-modal">
            <div class="success-animation">
                <div class="checkmark-circle">
                    <div class="checkmark"></div>
                </div>
            </div>
            <h2 class="success-title">Reply Sent Successfully!</h2>
            <button class="btn-ok" onclick="closeSuccessModal()">OK</button>
        </div>
    </div>

    <style>
    /* Add these styles for the success modal */
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

    .success-modal {
        text-align: center;
        padding: 30px;
        background: white;
        border-radius: 8px;
        max-width: 400px;
        width: 90%;
    }

    .success-animation {
        margin-bottom: 20px;
    }

    .checkmark-circle {
        width: 60px;
        height: 60px;
        position: relative;
        display: inline-block;
        background: #4CAF50;
        border-radius: 50%;
        margin-bottom: 20px;
    }

    .checkmark {
        border-right: 3px solid white;
        border-bottom: 3px solid white;
        width: 20px;
        height: 40px;
        position: absolute;
        left: 50%;
        top: 45%;
        transform: translate(-50%, -50%) rotate(45deg);
    }

    .success-title {
        color: #333;
        margin-bottom: 20px;
        font-size: 24px;
    }

    .btn-ok {
        padding: 10px 30px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }

    .btn-ok:hover {
        background-color: #45a049;
    }

    /* Add these styles for the modals */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    .modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 600px;
        position: relative;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .close-modal {
        font-size: 24px;
        cursor: pointer;
        color: #666;
    }

    .details-grid {
        display: grid;
        gap: 15px;
    }

    .detail-row {
        display: grid;
        grid-template-columns: 150px 1fr;
        align-items: start;
    }

    .detail-row label {
        font-weight: bold;
        color: #666;
    }

    .description-box {
        background-color: #f8f9fa;
        padding: 10px;
        border-radius: 4px;
        min-height: 60px;
    }

    .reply-textarea {
        width: 100%;
        min-height: 100px;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        resize: vertical;
    }

    .reply-btn, .send-btn {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }

    .reply-btn:hover, .send-btn:hover {
        background-color: #218838;
    }

    /* Add these additional styles */
    .complaint-info p {
        color: #666;
        font-size: 0.9em;
        margin-top: 5px;
    }

    .complaint-card {
        background: white;
        border-radius: 8px;
        padding: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .complaint-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .complaint-icon {
        width: 40px;
        height: 40px;
        background: #f8f9fa;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .complaint-icon i {
        font-size: 20px;
        color: #666;
    }
    </style>

    <script>
    let currentComplaintData = null;

    function openStallModal(name, description, time, date, reason, imageUrl, reporter) {
        closeAllModals();
        document.getElementById('stallName').textContent = name;
        document.getElementById('stallDescription').textContent = description;
        document.getElementById('stallTime').textContent = time;
        document.getElementById('stallDate').textContent = date;
        document.getElementById('stallReason').textContent = reason;
        document.getElementById('stallReporter').textContent = reporter;
        
        const stallModal = document.getElementById('stallModal');
        stallModal.style.display = 'block';
    }

    function openUserModal(name, description, time, date, reason, reporter) {
        closeAllModals();
        document.getElementById('userName').textContent = name;
        document.getElementById('userDescription').textContent = description;
        document.getElementById('userTime').textContent = time;
        document.getElementById('userDate').textContent = date;
        document.getElementById('userReason').textContent = reason;
        document.getElementById('userReporter').textContent = reporter;
        
        const userModal = document.getElementById('userModal');
        userModal.style.display = 'block';
    }

    function openReplyModal(type) {
        const replyModal = document.getElementById('replyModal');
        const name = type === 'stall' ? 
            document.getElementById('stallName').textContent : 
            document.getElementById('userName').textContent;
        
        document.getElementById('replyTo').textContent = name;
        currentComplaintData = { type, name };
        
        closeAllModals();
        replyModal.style.display = 'block';
    }

    function sendReply() {
        const replyText = document.querySelector('.reply-textarea').value.trim();
        if (replyText) {
            showSuccessModal();
            // Here you would typically send the reply to the server
            console.log('Sending reply:', {
                to: currentComplaintData.name,
                type: currentComplaintData.type,
                message: replyText
            });
        }
    }

    function showSuccessModal() {
        closeAllModals();
        const successModal = document.getElementById('successModal');
        successModal.style.display = 'flex';
        
        setTimeout(() => {
            closeSuccessModal();
        }, 2000);
    }

    function closeSuccessModal() {
        const successModal = document.getElementById('successModal');
        successModal.style.display = 'none';
        location.reload();
    }

    function closeAllModals() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => modal.style.display = 'none');
    }

    // Window click handler
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            closeAllModals();
        }
    }

    // Close button handlers
    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', function() {
            closeAllModals();
        });
    });

    // Dismiss button handlers
    document.querySelectorAll('.dismiss-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const card = this.closest('.complaint-card');
            card.style.opacity = '0';
            setTimeout(() => {
                card.remove();
            }, 300);
        });
    });
    </script>
</body>

</html> 