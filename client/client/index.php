<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>EZ-ORDER - Welcome</title>
   <link href="https://fonts.googleapis.com/css2?family=Macondo&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <style>
    :root {
      --primary-color: #186479;
      --primary-hover: #134d5d;
      --primary-light: rgba(24, 100, 121, 0.1);
      --text-color: #2d3748;
      --border-color: #e2e8f0;
        background: rgb(227, 235, 235);
    }

            * {
              margin: 0;
              padding: 0;
              box-sizing: border-box;
            }

            body {
              margin: 0;
              font-family: 'Poppins', sans-serif;
              display: flex;
              justify-content: center;
              align-items: center;
              min-height: 100vh;
              background: var(--background-light);
              padding: 20px;
            }

            .container {
              display: flex;
              flex-direction: row;
              width: 100%;
              max-width: 900px;
              background: #ffffff;
              border-radius: 20px;
              box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
              overflow: hidden;
            }

            .left-panel {
              background: var(--primary-color);
              color: white;
              padding: 40px;
              display: flex;
              flex-direction: column;
              align-items: center;
              justify-content: center;
              flex: 1;
              text-align: center;
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
                    padding: 40px;
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    text-align: center;
                  }

                  .right-panel p.description {
                    font-size: 15px;
                    color: var(--text-color);
                    margin-bottom: 20px;
                  }

                  .guest-notice {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    color: #856404;
                    padding: 12px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                    font-size: 14px;
                  }

                  .guest-notice i {
                    margin-right: 8px;
                  }

                  .button {
                    display: block;
                    width: 100%;
                    padding: 16px;
                    margin-bottom: 15px;
                    border-radius: 12px;
                    text-decoration: none;
                    font-weight: 600;
                    font-size: 16px;
                    transition: all 0.3s ease;
                    position: relative;
                    overflow: hidden;
                  }

                  .button::after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(255, 255, 255, 0.1);
                    transform: translateX(-100%);
                    transition: transform 0.3s ease;
                  }

                  .button:hover::after {
                    transform: translateX(0);
                  }

                  .button.primary {
                    background-color: var(--primary-color);
                    color: #fff;
                    border: none;
                  }

                  .button.primary:hover {
                    background-color: var(--primary-hover);
                    transform: translateY(-2px);
                  }

                  .button.secondary {
                    background-color: white;
                    color: var(--primary-color);
                    border: 2px solid var(--primary-color);
                  }

                  .button.secondary:hover {
                    background-color: var(--primary-light);
                    transform: translateY(-2px);
                  }

                  .button.guest {
                    background-color: #28a745;
                    color: #fff;
                  }

                  .button.guest:hover {
                    background-color: #218838;
                    transform: translateY(-2px);
                  }

                  .button.track {
                    background-color: #17a2b8;
                    color: #fff;
                  }

                  .button.track:hover {
                    background-color: #138496;
                    transform: translateY(-2px);
                  }

                  .divider {
                    display: flex;
                    align-items: center;
                    margin: 20px 0;
                    color: var(--text-color);
                    font-size: 14px;
                  }

                  .divider::before,
                  .divider::after {
                    content: '';
                    flex: 1;
                    height: 1px;
                    background: var(--border-color);
                  }

                  .divider span {
                    padding: 0 15px;
                  }

                  .features {
                    text-align: left;
                    margin-top: 20px;
                    font-size: 15px;
                    color: #333;
                  }

                  .features h3 {
                    font-size: 18px;
                    margin-bottom: 10px;
                    color: var(--primary-color);
                  }

                  .features ul {
                    list-style: none;
                    padding: 0;
                  }

                  .features li {
                    margin-bottom: 8px;
                  }

                  .features li i {
                    margin-right: 8px;
                    color: var(--primary-color);
                  }

                  .testimonials {
                    margin-top: 30px;
                    font-style: italic;
                    font-size: 14px;
                    color: #555;
                  }

                  @media (max-width: 768px) {
                    .container {
                      flex-direction: column;
                    }

                    .left-panel, .right-panel {
                      padding: 30px 20px;
                    }

                    .left-panel .logo {
                      width: 120px;
                    }

                    .features {
                      text-align: center;
                    }
                  }
  </style>
</head>
<body>
  <div class="container">
    <div class="left-panel">
      <img src="assets/logo.png" alt="EZ-ORDER Logo">
      <h1>EZ-ORDER</h1>
      <p>Order. Grab. Eat.</p>
    </div>

    <div class="right-panel">
      <p class="description">
        Welcome to EZ-ORDER! The fastest way to order food from your school canteen.
        Browse menus, order ahead, and enjoy a contactless experience no sign-up needed!
      </p>

      <div class="guest-notice">
        <i class="fas fa-info-circle"></i>
        <strong>New:</strong> Browse and order as a guest! 
        <i class="fas fa-question-circle" style="cursor:pointer;" onclick="alert('Guest Mode lets you order without logging in. Perfect for quick and easy access.')"></i>
      </div>

      <a href="dashboard.php" class="button guest">
        <i class="fas fa-utensils"> </i> Browse Menu (Guest)
      </a>

      <a href="guest_track_order.php" class="button track">
        <i class="fas fa-search"></i> Track Guest Order
      </a>

      <div class="divider"><span>or</span></div>

      <a href="login.php" class="button primary">SIGN IN</a>
      <a href="register.php" class="button secondary">SIGN UP</a>

      <div class="features">
        <h3>Why use EZ-ORDER?</h3>
        <ul>
          <li><i class="fas fa-bolt"></i> Fast & contactless ordering</li>
          <li><i class="fas fa-users"></i> No account needed for guests</li>
          <li><i class="fas fa-map-marker-alt"></i> Track your order in real time</li>
        </ul>
      </div>
    </div>
  </div>
</body>
</html>
