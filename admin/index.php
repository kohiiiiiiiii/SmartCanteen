<?php
// ✅ Start session at the very top (only once)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Include DB connection
include '../db.connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// ✅ Fetch total users
$userCount = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($result && $row = $result->fetch_assoc()) {
    $userCount = $row['total'];
}

// ✅ Fetch total active orders (Pending, Preparing, Ready)
$orderCount = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status IN ('Pending', 'Preparing', 'Ready')");
if ($result && $row = $result->fetch_assoc()) {
    $orderCount = $row['total'];
}

// ✅ Fetch total menu items
$menuCount = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM menu");
if ($result && $row = $result->fetch_assoc()) {
    $menuCount = $row['total'];
}

// ✅ Close DB connection
$conn->close();

// ✅ Prepare admin full name
$firstName  = $_SESSION['first_name'] ?? '';
$middleName = $_SESSION['middle_name'] ?? '';
$lastName   = $_SESSION['last_name'] ?? '';
$suffix     = $_SESSION['suffix'] ?? '';
$role       = $_SESSION['role'] ?? 'Admin';
$fullName = trim($firstName . ($middleName ? " $middleName" : "") . " $lastName" . ($suffix ? " $suffix" : ""));
$currentPage = basename($_SERVER['PHP_SELF']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SmartCanteen - Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/index_admin.css"> 
  <style>
    /* 🧭 Make the cards look clickable */
    .card-clickable {
      cursor: pointer;
      transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .card-clickable:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }
    .card-clickable a {
      text-decoration: none;
      color: inherit;
      display: block;
    }
  </style>
</head>
<body>
  
<div class="sidebar">
  <img src="../assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo d-block mx-auto">

  <div class="links">
    <a href="index.php" class="<?= $currentPage == 'index.php' ? 'active' : '' ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="users.php" class="<?= $currentPage == 'users.php' ? 'active' : '' ?>">
      <i class="bi bi-people"></i> Manage Users
    </a>
    <a href="menu.php" class="<?= $currentPage == 'menu.php' ? 'active' : '' ?>">
      <i class="bi bi-journal-text"></i> Manage Menu
    </a>
    <a href="orders.php" class="<?= $currentPage == 'orders.php' ? 'active' : '' ?>">
      <i class="bi bi-receipt"></i> Orders
    </a>
  </div>

  <div class="profile-bar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
      <a href="profile.php" class="profile-link">
        <img src="../assets/img/user_avatar.png" alt="Profile" class="rounded-circle me-3" width="50" height="50">
      </a>
      <div>
        <h6 class="mb-0"><?= htmlspecialchars($firstName) ?></h6>
        <small class="text-muted"><?= htmlspecialchars($role) ?></small>
      </div>
    </div>
    <a href="../logout.php" class="logout text-danger"><i class="bi bi-box-arrow-right fs-5"></i></a>
  </div>
</div>

<!-- Content -->
<div class="content">
  <div class="topbar">
    <h5>Welcome back, <span id="adminName"><?= htmlspecialchars($firstName) ?></span></h5>
    <i class="bi bi-bell-fill fs-4 text-light"></i>
  </div>

  <!-- Dashboard Stats -->
  <div class="row mt-4 g-4">
    <!-- 👤 Total Users -->
    <div class="col-md-4">
      <div class="card card-custom card-clickable text-center p-4">
        <a href="users.php">
          <h6>👤 Total Users</h6>
          <h3><?= $userCount ?></h3>
        </a>
      </div>
    </div>

    <!-- 🛒 Active Orders -->
    <div class="col-md-4">
      <div class="card card-custom card-clickable text-center p-4">
        <a href="orders.php">
          <h6>🛒 Active Orders</h6>
          <h3><?= $orderCount ?></h3>
        </a>
      </div>
    </div>

    <!-- 🍽️ Menu Items -->
    <div class="col-md-4">
      <div class="card card-custom card-clickable text-center p-4">
        <a href="menu.php">
          <h6>🍽️ Menu Items</h6>
          <h3><?= $menuCount ?></h3>
        </a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
