<?php
include '../includes/db_connect.php';
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $errors[] = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $name, $hashedPassword);
            $stmt->fetch();

            if (password_verify($password, $hashedPassword)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['email'] = $email;
                
                if (isset($_GET['redirect'])) {
                    header("Location: " . $_GET['redirect']);
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "No user found with that email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login - EZ-ORDER</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Icons (Font Awesome) -->
    <link href="https://fonts.googleapis.com/css2?family=Macondo&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #186479;
            --primary-hover: #134d5d;
            --primary-light: rgba(24, 100, 121, 0.1);
            --text-color: #2d3748;
            --border-color: #e2e8f0;
               background: rgb(227, 235, 235);
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            min-height: 100vh;
            background: var(--background-light);
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .container {
            display: flex;
            width: 100%;
            max-width: 900px;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            max-height: 90vh;
        }

        .left-panel {
            background-color: var(--primary-color);
            color: white;
            padding: 40px 20px;
            text-align: center;
            width: 40%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .left-panel img {
            width: 350px;
            height: 300px;
            margin-bottom: 10px;
            margin-top: -20px;
        }

        .left-panel h1 {
            font-family: 'Macondo', cursive;
            font-weight: 200;
            font-size: 60px;
            line-height: 120%;
            letter-spacing: -2%;
            text-align: center;
            margin: 0; 
            color: white; 
        }

        .left-panel p {
            font-family: 'IBM Plex Sans', sans-serif;
            font-weight: 200;
            font-size: 27px;
            line-height: 120%;
            letter-spacing: -2px;
            text-align: center;
            margin: 0;
            color:rgb(143, 140, 140); 
        }

        .right-panel {
            padding: 40px 30px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        h2 {
            text-align: center;
            color: var(--primary-color);
            font-size: 35px;
            margin-bottom: 70px;
        }

        h2::after {
            content: "";
            display: block;
            width: 40%;
            height: 3px;
            background-color: var(--primary-hover);
            margin-top: 10px;
            margin-left: auto;
            margin-right: auto;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
            width: 100%;
        }

        .form-group input {
            width: 100%;
            padding: 14px 60px 14px 50px;
            font-size: 15px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            transition: all 0.3s ease;
            background-color: #f8fafc;
            color: var(--text-color);
            box-sizing: border-box;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px var(--primary-light);
            outline: none;
            background-color: white;
        }

        .form-group input::placeholder {
            color:rgb(106, 114, 126);
            font-size: 14px;
        }

        .form-group i {
            position: absolute;
            top: 50%;
            left: 18px;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .form-group input:focus + i {
            color: var(--primary-color);
            left: 12px;
        }

        .toggle-password {
            margin-left: 388px;
            cursor: pointer;
        }

        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px;
            width: 50%;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 40px;
            margin-left: 115px;
        }

        button:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(24, 100, 121, 0.2);
        }

        button:active {
            transform: translateY(0);
        }
        .bottom-text {
            text-align: center;
            margin-top: 20px;
            color: var(--text-color);
        }

        .bottom-text a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .bottom-text a:hover {
            text-decoration: underline;
        }

        .error-message {
            background-color: #fed7d7;
            border: 1px solid #f56565;
            color: #c53030;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error-message p {
            margin: 0;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                margin: 20px;
            }

            .left-panel {
                width: 100%;
                padding: 20px;
            }

            .right-panel {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="left-panel">
        <img src="assets/logo.png" alt="EZ-ORDER Logo">
        <h1>EZ-ORDER</h1>
        <p>Order. Grab. Eat</p>
    </div>
    <div class="right-panel">
        <h2>Sign In</h2>
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <i class="fas fa-user"></i>
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
            </div>
            <button type="submit">SIGN IN</button>
        </form>
        <div class="bottom-text">
            Don't have an account? <a href="register.php">Sign Up</a>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const pwd = document.getElementById("password");
        const eye = document.querySelector(".toggle-password");
        if (pwd.type === "password") {
            pwd.type = "text";
            eye.classList.remove("fa-eye");
            eye.classList.add("fa-eye-slash");
        } else {
            pwd.type = "password";
            eye.classList.remove("fa-eye-slash");
            eye.classList.add("fa-eye");
        }
    }
</script>

</body>
</html> 