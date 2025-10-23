<?php
include 'db.connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name   = $_POST['first_name'];
    $middle_name  = $_POST['middle_name'];
    $last_name    = $_POST['last_name'];
    $suffix       = $_POST['suffix'];
    $email        = $_POST['email'];
    $password     = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];
    $role         = $_POST['role'];

    // ✅ check confirm password
    if ($password !== $confirm_pass) {
        echo "<script>alert('Passwords do not match!'); window.location.href='register.php';</script>";
        exit;
    }

    // ✅ hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users 
        (first_name, middle_name, last_name, suffix, email, password, role) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $first_name, $middle_name, $last_name, $suffix, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        echo "<script>
                alert('Registration successful! You can now log in.');
                window.location.href = 'login.php';
              </script>";
        exit;
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SmartCanteen | Register</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/index.css">
  <link rel="stylesheet" href="assets/css/about.css">
  <style>
    /* Match login card layout */
    .register-content {
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
      max-width: 450px;
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

    .btn-register {
      background-color: #ffb703;
      border: none;
      color: #1e1e1e;
      font-weight: bold;
      width: 100%;
      padding: 0.75rem;
      border-radius: 30px;
      transition: all 0.3s ease;
    }

    .btn-register:hover {
      background-color: #e0a800;
      transform: scale(1.02);
    }
  </style>
</head>
<body>

<!-- Sidebar (same as login) -->
<div class="sidebar">
  <img src="assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo d-block mx-auto">

  <div class="links">
    <a href="guest/menu_view.php"><i class="bi bi-house-door"></i> Home</a>
    <a href="guest/about.php"><i class="bi bi-info-circle"></i> About</a>
    <a href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a>
    <a href="register.php" class="active"><i class="bi bi-person-plus"></i> Register</a>
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

<!-- Register Section -->
<div class="register-content">
  <div class="card">
    <img src="assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo">
    <h3>Create an Account</h3>
    <p>Join SmartCanteen today</p>

    <form action="register.php" method="POST" id="registerForm" novalidate>
      <div class="row g-2">
        <div class="col-md-6 mb-3">
          <label for="first_name" class="form-label text-start w-100">First Name</label>
          <input type="text" class="form-control" id="first_name" name="first_name" required>
        </div>

        <div class="col-md-6 mb-3">
          <label for="middle_name" class="form-label text-start w-100">Middle Name</label>
          <input type="text" class="form-control" id="middle_name" name="middle_name">
        </div>
      </div>

      <div class="row g-2">
        <div class="col-md-6 mb-3">
          <label for="last_name" class="form-label text-start w-100">Last Name</label>
          <input type="text" class="form-control" id="last_name" name="last_name" required>
        </div>

        <div class="col-md-6 mb-3">
          <label for="suffix" class="form-label text-start w-100">Suffix</label>
          <input type="text" class="form-control" id="suffix" name="suffix" placeholder="Jr, Sr, III">
        </div>
      </div>

      <div class="mb-3 text-start">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>

      <div class="row g-2">
        <div class="col-md-6 mb-3">
          <label for="password" class="form-label text-start w-100">Password</label>
          <input type="password" class="form-control" id="password" name="password" minlength="6" required>
        </div>

        <div class="col-md-6 mb-3">
          <label for="confirm_password" class="form-label text-start w-100">Confirm Password</label>
          <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
      </div>

      <div class="mb-3 text-start">
        <label for="role" class="form-label">Register as</label>
        <select class="form-select" id="role" name="role" required>
          <option value="">Select Role</option>
          <option value="Student">Student</option>
          <option value="Staff">Staff</option>
          <option value="Manager">Canteen Manager</option>
          <option value="Admin">Admin</option>
        </select>
      </div>

      <button type="submit" class="btn btn-register">Register</button>

      <div class="text-center mt-3">
        <p>Already have an account? <a href="login.php">Login</a></p>
      </div>
    </form>
  </div>
</div>

<script>
(() => {
  'use strict';
  const form = document.getElementById('registerForm');

  form.addEventListener('submit', function (event) {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    if (password.value !== confirmPassword.value) {
      confirmPassword.setCustomValidity("Passwords do not match");
    } else {
      confirmPassword.setCustomValidity("");
    }
    if (!form.checkValidity()) {
      event.preventDefault();
      event.stopPropagation();
    }
    form.classList.add('was-validated');
  }, false);
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
