<?php
session_start();
include '../db.connect.php';

// 🔐 Redirect if not logged in
if (!isset($_SESSION['first_name'])) {
    header("Location: ../login.php");
    exit();
}

$first_name = $_SESSION['first_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SmartCanteen - About</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/index.css"> 
  <link rel="stylesheet" href="../assets/css/about.css"> 
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <img src="../assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo d-block mx-auto">

  <div class="links">
    <a href="index.php"><i class="bi bi-house-door"></i> Home</a>
    <a href="about.php" class="active"><i class="bi bi-info-circle"></i> About</a>
  </div>

  <div class="profile-bar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
      <a href="profile.php" class="profile-link">
        <img src="../assets/img/user_avatar.png" alt="Profile" class="rounded-circle me-3" width="50" height="50">
      </a>
      <div>
        <h6 class="mb-0"><?= htmlspecialchars($_SESSION['first_name'] ?? '') ?></h6>
        <small class="text-muted">Customer</small>
      </div>
    </div>
    <a href="../logout.php" class="logout text-danger"><i class="bi bi-box-arrow-right fs-5"></i></a>
  </div>
</div>

<!-- Content -->
<div class="content">
  <div class="topbar d-flex justify-content-between align-items-center">
    <h5>About</h5>
    <div class="d-flex gap-4">
      <a href="cart.php"><i class="bi bi-cart-fill fs-4 text-light"></i></a>
      <a href="#"><i class="bi bi-bell-fill fs-4 text-light"></i></a>
    </div>
  </div>
  
  <!-- About Content -->
  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card p-4 shadow-sm border-0">
          <img src="../assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo1 d-block mx-auto mb-4">
          <p class="lead text-center textcolor">
            SmartCanteen is more than just a food ordering system—it’s a purpose-driven platform thoughtfully crafted to transform the way students, staff, and canteen operators experience daily meals.
          </p>
          <br><br>
          <p class="size">
            It goes beyond convenience, aiming to foster a sense of comfort, community, and care in every interaction. Whether you're rushing between classes, managing a busy office schedule, or preparing meals behind the counter, SmartCanteen is designed to make the process smoother, smarter, and more human. From intuitive menu browsing and personalized meal customization to real-time notifications and backend inventory support, every feature is built with empathy and efficiency in mind. 
          </p>
          <p class="size">
            It’s not just about ordering food—it’s about creating a dining experience that feels welcoming, organized, and emotionally attuned to the needs of its users. SmartCanteen empowers schools and workplaces to embrace digital solutions that promote wellness, reduce waste, and encourage meaningful connections around shared meals.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
