<?php
include '../includes/db_connect.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || 
               !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "Email already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $name, $email, $hashedPassword);
            
            if ($stmt->execute()) {
                header("Location: login.php?registered=true");
                exit();
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - EZ-ORDER</title>
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
            margin: 20px;
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
            margin-top: -60px;
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
            margin-bottom: 35px;
            margin-top: -15px;
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
        }

        .form-group a{
            position: absolute;
            top: 35%;
            left: 18px;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .form-group a:hover {
            color: var(--primary-color);
        }

        .form-group b{
            position: absolute;
            top: 12%;
            left: 18px;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .form-group b:hover {
            color: var(--primary-color);
        }

        .toggle-password {
            margin-left: 340px;
            cursor: pointer;
        }

        .toggle-password:hover {
            color: var(--primary-color);
        }

        button {
            width: 70%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: -40px;
            margin-left: 68px;
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
            margin-top: 20px;
            text-align: center;
        }

        .bottom-text a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .bottom-text a:hover {
            color: var(--primary-hover);
        }

        .error-message {
            background-color: #FED7D7;
            color: #C53030;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #FC8181;
        }

        form {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .password-requirements {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 13px;
            color: #495057;
        }

        .password-requirements p {
            margin: 0 0 8px 0;
            font-weight: 600;
            color: #495057;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }

        .password-requirements li {
            color: #6c757d;
            margin-bottom: 2px;
        }

        .password-requirements li.valid {
            color:rgb(34, 93, 182);
        }

        .password-requirements li.valid:before {
            content: "✓ ";
            color:rgb(34, 93, 182);
        }

        .password-strength {
            height: 4px;
            background-color: #e9ecef;
            margin-top: 8px;
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .confirm-message {
            margin-top: 5px;
            font-size: 13px;
            min-height: 20px;
        }

        .match {
            color: #28a745;
        }

        .no-match {
            color: #dc3545;
        }

        @media screen and (max-width: 768px) {
            .container {
                flex-direction: column;
                margin: 10px;
                max-height: none;
            }

            .left-panel {
                width: 100%;
                padding: 30px 20px;
            }

            .right-panel {
                width: 100%;
                padding: 30px 20px;
            }

            form {
                max-width: 100%;
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
        <h2>Sign Up</h2>
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
            </div>
        <?php endif; ?>
        <form id="registerForm" method="post" onsubmit="return validateForm()">
            <div class="form-group">
                <i class="fas fa-user"></i>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="form-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="form-group">
                <b class="fas fa-lock"></b>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <b class="fas fa-eye toggle-password" onclick="togglePassword('password')"></b>
                <div id="password-requirements" class="password-requirements">
                    <p>Password must contain:</p>
                    <ul>
                        <li id="length">At least 8 characters</li>
                        <li id="uppercase">One uppercase letter (A-Z)</li>
                        <li id="lowercase">One lowercase letter (a-z)</li>
                        <li id="number">One number (0-9)</li>
                        <li id="special">One special character (!@#$%^&*)</li>
                    </ul>
                </div>
            </div>
            <div class="form-group">
                <a class="fas fa-lock"></a>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                <a class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password')"></a>
                <div id="confirm-message" class="confirm-message"></div>
            </div>
            <button type="submit">Register</button>
        </form>
        <div class="bottom-text">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</div>

<script>
    function togglePassword(fieldId) {
        const pwd = document.getElementById(fieldId);
        const eye = pwd.nextElementSibling;
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

    function checkPasswordStrength() {
        const password = document.getElementById('password').value;
        const strengthBar = document.querySelector('.strength-bar');
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^A-Za-z0-9]/.test(password)
        };

        // Update requirement indicators
        document.getElementById('length').className = requirements.length ? 'valid' : '';
        document.getElementById('uppercase').className = requirements.uppercase ? 'valid' : '';
        document.getElementById('lowercase').className = requirements.lowercase ? 'valid' : '';
        document.getElementById('number').className = requirements.number ? 'valid' : '';
        document.getElementById('special').className = requirements.special ? 'valid' : '';

        // Calculate strength
        let strength = 0;
        const requirementsMet = Object.values(requirements).filter(Boolean).length;
        
        if (requirements.length) strength += 20;
        if (requirements.uppercase) strength += 20;
        if (requirements.lowercase) strength += 20;
        if (requirements.number) strength += 20;
        if (requirements.special) strength += 20;

        // Update strength bar
        strengthBar.style.width = strength + '%';
        
        if (strength < 40) {
            strengthBar.style.backgroundColor = '#dc3545';
        } else if (strength < 80) {
            strengthBar.style.backgroundColor = '#ffc107';
        } else {
            strengthBar.style.backgroundColor = '#28a745';
        }
    }

    function checkPasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const message = document.getElementById('confirm-message');

        if (confirmPassword === '') {
            message.textContent = '';
            message.className = 'confirm-message';
        } else if (password === confirmPassword) {
            message.textContent = 'Passwords match!';
            message.className = 'confirm-message match';
        } else {
            message.textContent = 'Passwords do not match!';
            message.className = 'confirm-message no-match';
        }
    }

    function validateForm() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password.length < 8) {
            alert('Password must be at least 8 characters long.');
            return false;
        }
        
        if (!/[A-Z]/.test(password) || !/[a-z]/.test(password) || 
            !/[0-9]/.test(password) || !/[^A-Za-z0-9]/.test(password)) {
            alert('Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.');
            return false;
        }
        
        if (password !== confirmPassword) {
            alert('Passwords do not match.');
            return false;
        }
        
        return true;
    }
</script>

</body>
</html>