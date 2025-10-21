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
  <title>SmartCanteen</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/navbar.css">
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
      login.php
      <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
        <div class="navbar-nav gap-5 w-100 padding">
          <a href="customer/index.php" class="nav-link homecolor">Home</a>
          <a href="customer/about.php" class="nav-link aboutcolor">About</a>
        </div>
          <div class="navbar-nav position-relative d-flex align-items-center gap-3">
          <a href="login.php" class="nav-link logincolor loginbg">Login</a>
          <a href="register.php" class="nav-link logincolor registerbg">Register</a>
        </div>
      </div>
  </div>
</nav>

<div class="register d-flex justify-content-center align-items-center vh-200">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="card p-4">
          <img src="assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo d-block mx-auto">
          <h3 class="text-center registercolor">Create an Account</h3>

          <!-- Registration Form -->
          <form action="register.php" method="POST" id="registerForm" novalidate>
            <div class="mb-3">
              <label for="first-name" class="form-label">First Name:</label>
              <input type="text" class="form-control" name="first_name" id="first-name" required>
              <div class="invalid-feedback">First name is required.</div>
            </div>

            <div class="mb-3">
              <label for="middle-name" class="form-label">Middle Name:</label>
              <input type="text" class="form-control" name="middle_name" id="middle-name">
            </div>

            <div class="mb-3">
              <label for="last-name" class="form-label">Last Name:</label>
              <input type="text" class="form-control" name="last_name" id="last-name" required>
              <div class="invalid-feedback">Last name is required.</div>
            </div>

            <div class="mb-3">
              <label for="suffix" class="form-label">Suffix:</label>
              <input type="text" class="form-control" name="suffix" id="suffix" placeholder="e.g. Jr, Sr, III">
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Email Address:</label>
              <input type="email" class="form-control" name="email" id="email" required>
              <div class="invalid-feedback">Please enter a valid email.</div>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Password:</label>
              <input type="password" class="form-control" name="password" id="password" minlength="6" required>
              <div class="invalid-feedback">Password must be at least 6 characters long.</div>
            </div>

            <div class="mb-3">
              <label for="confirm-password" class="form-label">Confirm Password:</label>
              <input type="password" class="form-control" name="confirm_password" id="confirm-password" required>
              <div class="invalid-feedback">Passwords must match.</div>
            </div>

            <div class="mb-3">
              <label for="role" class="form-label">Register as</label>
              <select class="form-select" name="role" id="role" required>
                <option value="">Select Role</option>
                <option value="student">Student</option>
                <option value="staff">Staff</option>
                <option value="manager">Canteen Manager</option>
                <option value="admin">Admin</option>
              </select>
              <div class="invalid-feedback">Please select a role.</div>
            </div>

            <button type="submit" class="btn btn-warning d-block mx-auto registerbtn">Register</button>
          </form>

          <div class="text-center mt-3">
            <p>Already have an account? <a href="login.php" class="registercolor">Sign In</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  'use strict';
  const form = document.getElementById('registerForm');

  form.addEventListener('submit', function (event) {
    // Password match check
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm-password');
    
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
