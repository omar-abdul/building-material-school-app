<!DOCTYPE html>
<html lang="en">
<head>


  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <script src="login.js" defer></script>
  <title>Login - KuLan Buildings Material</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      display: flex;
      height: 100vh;
      background-color: #e6eff7;
    }

    .container {
      display: flex;
      flex: 1;
      background-color: white;
      border-radius: 12px;
      overflow: hidden;
      width: 90%;
      max-width: 960px;
      margin: auto;
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
    }

    .left-side, .right-side {
      flex: 1;
      padding: 40px;
    }

    .left-side {
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .logo {
      display: flex;
      align-items: center;
      font-size: 24px;
      font-weight: bold;
      color: #2a3f5f;
      margin-bottom: 30px;
    }

    .logo img {
      width: 40px;
      height: 40px;
      margin-right: 10px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: 500;
    }

    input, select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }

    button {
      width: 100%;
      padding: 10px;
      background-color: #306bff;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
    }

    button:hover {
      background-color: #3b82f6;
    }

    .form-footer {
      display: flex;
      justify-content: space-between;
      font-size: 13px;
      margin-top: 10px;
    }

    .right-side {
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      align-items: center;
      text-align: center;
      padding: 0;
      background: linear-gradient(to bottom, #3b82f6 0%, #3b82f6 70%, #000000 100%);
    }

    .top-half {
      flex: 1;
      width: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .top-content {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .top-content img {
      max-width: 70%;
      height: auto;
      border-radius: 8px;
      opacity: 0;
      animation: fadeZoom 1.2s ease-out forwards;
    }

    .top-content h2 {
      color: #ffffff;
      margin-top: 20px;
      font-size: 22px;
    }

    .bottom-half {
      color: #ffffff;
      width: 100%;
      padding: 40px;
      display: flex;
      flex-direction: column;
      align-items: center;
      background: transparent;
    }

    .bottom-half p {
      font-size: 14px;
      max-width: 320px;
    }
    .error {
      color: red;
      text-align: center;
      margin-top: 10px;
    }
    .footer-text {
      margin-top: 15px;
      font-size: 14px;
      color: var(--gray-dark);
    }

    @keyframes fadeZoom {
      0% {
        opacity: 0;
        transform: scale(0.8);
      }
      100% {
        opacity: 1;
        transform: scale(1);
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="left-side">
      <div class="logo">
        <img src="/backend/dashboard/image/logo.png" alt="kulan Logo">
        <span>KuLan<br><small>BUILDINGS MATERIAL</small></span>
      </div>
      <form id="loginForm">
        <?php
        require_once __DIR__ . '/../config/csrf.php';
        echo CSRF::getHiddenInput();
        ?>
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required />
        </div>
        <button type="submit">Login</button>
        <div class="error" id="loginError"></div>
      </form>
      <div class="footer-text">
        © 2025 Building Material Management System
      </div>
    </div>
    <div class="right-side">
      <div class="top-half">
        <div class="top-content">
          <img src="/backend/dashboard/image/111.png" alt="Qalab Dhisme">
          <h2>libso qalab dhisme tayo sare leh</h2>
        </div>
      </div>
      <div class="bottom-half">
        <p>Waxaan kaa caawineynaa inaad si sahlan u hesho dhammaan qalabka dhismaha aad u baahan tahay — si ammaan ah, degdeg ah, iyo qiimo jaban.</p>
      </div>
    </div>
  </div>
</body>
</html>
</body>
</html>