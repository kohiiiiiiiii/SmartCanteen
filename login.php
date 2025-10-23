<?php
session_start();

$saved_email = isset($_COOKIE['email']) ? $_COOKIE['email'] : "";
$remember_checked = !empty($saved_email) ? "checked" : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SmartCanteen | Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/index.css">
  <link rel="stylesheet" href="assets/css/about.css">
  <style>
    /* Center login card using about.css layout */
    .login-content {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: calc(100vh - 70px);
      padding: 2rem;
    }

    .card {
      background: #ffffff;
      border: none;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      max-width: 400px;
      width: 100%;
      padding: 2rem;
      text-align: center;
      transition: transform 0.2s ease;
    }

    .card:hover {
      transform: translateY(-5px);
    }

    .card img.logo {
      width: 90px;
      margin-bottom: 1rem;
    }

    .btn-login {
      background-color: #ffb703;
      border: none;
      color: #1e1e1e;
      font-weight: bold;
      width: 100%;
      padding: 0.75rem;
      border-radius: 30px;
      transition: all 0.3s ease;
    }

    .btn-login:hover {
      background-color: #e0a800;
      transform: scale(1.02);
    }
  </style>
</head>
<body>

<!-- Sidebar (same as About page layout) -->
<div class="sidebar">
  <img src="assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo d-block mx-auto">

  <div class="links">
    <a href="guest/menu_view.php"><i class="bi bi-house-door"></i> Home</a>
    <a href="guest/about.php"><i class="bi bi-info-circle"></i> About</a>
    <a href="login.php" class="active"><i class="bi bi-box-arrow-in-right"></i> Login</a>
    <a href="register.php"><i class="bi bi-person-plus"></i> Register</a>
  </div>

  <div class="profile-bar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
      <img src="assets/img/user_avatar.png" alt="Profile" class="rounded-circle me-3" width="50" height="50">
      <div>
        <h6 class="mb-0">Guest</h6>
        <small class="text-muted">Visitor</small>
      </div>
    </div>
  </div>
</div>

  <!-- Login Section -->
  <div class="login-content">
    <div class="card">
      <img src="assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo">
      <h3>Welcome Back</h3>
      <p>Sign in to access your SmartCanteen account</p>

      <form action="login_process.php" method="POST">
        <div class="mb-3 text-start">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" class="form-control" id="email" name="email"
                 placeholder="Enter email" value="<?php echo htmlspecialchars($saved_email); ?>" required>
        </div>

        <div class="mb-3 text-start">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password"
                 placeholder="Enter password" required>
        </div>

        <div class="mb-3 form-check text-start">
          <input class="form-check-input" type="checkbox" id="remember" name="remember" <?php echo $remember_checked; ?>>
          <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn btn-login">Login</button>

        <div class="text-center mt-3">
          <p>Don’t have an account? <a href="register.php">Register</a></p>
          <a href="forgot.php" class="text-muted small">Forgot Password?</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
