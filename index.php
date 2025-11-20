<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Sistem Sekolah</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    /* Background gradasi dengan overlay pattern */
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #3b6b52 0%, #4a7f5e 50%, #e89b3f 100%);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
      overflow: hidden;
    }
    
    /* Decorative circles */
    body::before {
      content: '';
      position: absolute;
      width: 500px;
      height: 500px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50%;
      top: -200px;
      left: -200px;
      animation: float 6s ease-in-out infinite;
    }
    
    body::after {
      content: '';
      position: absolute;
      width: 400px;
      height: 400px;
      background: rgba(0, 0, 0, 0.05);
      border-radius: 50%;
      bottom: -150px;
      right: -150px;
      animation: float 8s ease-in-out infinite reverse;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(20px); }
    }
    
    /* Container utama dengan glass effect */
    .login-container {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      padding: 50px 45px;
      border-radius: 25px;
      width: 100%;
      max-width: 420px;
      text-align: center;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3),
                  0 0 0 1px rgba(255, 255, 255, 0.1) inset;
      position: relative;
      z-index: 1;
      animation: slideIn 0.5s ease-out;
    }
    
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    /* Logo dengan efek shadow */
    .logo {
      width: 110px;
      height: 110px;
      object-fit: contain;
      margin-bottom: 20px;
      filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
      animation: logoFloat 3s ease-in-out infinite;
    }
    
    @keyframes logoFloat {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-8px); }
    }
    
    /* Judul dengan text shadow */
    h2 {
      font-size: 28px;
      margin-bottom: 35px;
      color: #fff;
      font-weight: 700;
      letter-spacing: 2px;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
      position: relative;
    }
    
    h2::after {
      content: '';
      position: absolute;
      width: 60px;
      height: 3px;
      background: linear-gradient(90deg, transparent, #e89b3f, transparent);
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
    }
    
    /* Label styling */
    label {
      display: block;
      text-align: left;
      font-size: 14px;
      margin: 20px 0 8px;
      color: #fff;
      font-weight: 500;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    }
    
    /* Input group dengan animasi */
    .input-group {
      position: relative;
      margin-bottom: 5px;
    }
    
    .input-group input {
      width: 100%;
      padding: 14px 45px;
      border: 2px solid rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      outline: none;
      background: rgba(255, 255, 255, 0.9);
      font-size: 15px;
      font-family: 'Poppins', sans-serif;
      transition: all 0.3s ease;
    }
    
    .input-group input:focus {
      background: rgba(255, 255, 255, 1);
      border-color: #e89b3f;
      box-shadow: 0 0 15px rgba(232, 155, 63, 0.3);
      transform: translateY(-2px);
    }
    
    .input-group input::placeholder {
      color: #999;
    }
    
    .input-group i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #3b6b52;
      font-size: 16px;
      transition: all 0.3s ease;
    }
    
    .input-group input:focus + i {
      color: #e89b3f;
    }
    
    /* Tombol dengan gradient dan hover effect */
    button {
      width: 100%;
      background: linear-gradient(135deg, #10264b 0%, #1c3f73 100%);
      color: white;
      border: none;
      padding: 14px;
      margin-top: 30px;
      border-radius: 12px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 600;
      font-family: 'Poppins', sans-serif;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(16, 38, 75, 0.4);
      position: relative;
      overflow: hidden;
    }
    
    button::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.2);
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }
    
    button:hover::before {
      width: 300px;
      height: 300px;
    }
    
    button:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(16, 38, 75, 0.5);
    }
    
    button:active {
      transform: translateY(-1px);
    }
    
    button span {
      position: relative;
      z-index: 1;
    }
    
    /* Link lupa sandi */
    .forgot {
      margin-top: 18px;
      font-size: 14px;
    }
    
    .forgot a {
      color: #fff;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
      position: relative;
    }
    
    .forgot a::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      background: #e89b3f;
      bottom: -2px;
      left: 0;
      transition: width 0.3s ease;
    }
    
    .forgot a:hover {
      color: #e89b3f;
    }
    
    .forgot a:hover::after {
      width: 100%;
    }
    
    /* Responsif */
    @media (max-width: 480px) {
      .login-container {
        width: 90%;
        padding: 40px 30px;
      }
      h2 {
        font-size: 24px;
      }
      .logo {
        width: 90px;
        height: 90px;
      }
    }
  </style>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="login-container">
    <img src="logo.png" alt="Logo" class="logo">
    <h2>MASUK</h2>
    <form action="proses_login.php" method="POST">
      <label>Masukkan Username</label>
      <div class="input-group">
        <input type="text" name="username" required>
        <i class="fas fa-user"></i>
      </div>
      <label>Masukkan Password</label>
      <div class="input-group">
        <input type="password" name="password" required>
        <i class="fas fa-lock"></i>
      </div>
      <button type="submit"><span>Masuk</span></button>
      <div class="forgot">
        <a href="#">Lupa kata sandi</a>
      </div>
    </form>
  </div>
</body>
</html>
