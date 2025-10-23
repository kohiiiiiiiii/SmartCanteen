<?php
session_start();
include '../db.connect.php';

// 🔐 Redirect if not logged in
if (!isset($_SESSION['first_name'])) {
    header("Location: ../login.php");
    exit();
}

$first_name = $_SESSION['first_name'];

// Cart count
$cart_count = count($_SESSION['cart']);

// Notifications
$notif_sql = "SELECT notif_id, message, created_at FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC";
$stmt_notif = $conn->prepare($notif_sql);
$stmt_notif->bind_param("i", $user_id);
$stmt_notif->execute();
$notif_result = $stmt_notif->get_result();
$unread_count = $notif_result->num_rows;
$notifications = $notif_result->fetch_all(MYSQLI_ASSOC);
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
        <a href="profile.php" class="profile-link me-3">
                <img src="../assets/img/user_avatar.png" alt="Profile" class="rounded-circle" width="50" height="50">
            </a>
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
<div class="content py-4 px-3">
    <!-- Topbar -->
    <div class="topbar d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">Welcome, <?= htmlspecialchars($first_name) ?>!</h5>
        <div class="d-flex gap-3">
            <a href="cart.php" class="text-light fs-4 position-relative">
                <i class="bi bi-cart-fill"></i>
                <?php if($cart_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>

            <!-- Notification Bell -->
            <div class="dropdown">
                <a href="#" class="text-light fs-4 position-relative" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell-fill">
                    <?php if($unread_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $unread_count ?></span>
                    <?php endif; ?></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown" style="min-width: 250px;">
                    <?php if($unread_count > 0): ?>
                        <?php foreach($notifications as $notif): ?>
                            <li>
                                <a class="dropdown-item" href="order_history.php?notif_id=<?= $notif['notif_id'] ?>">
                                    <?= htmlspecialchars($notif['message']) ?><br>
                                    <small class="text-muted"><?= date('F j, Y h:i A', strtotime($notif['created_at'])) ?></small>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><span class="dropdown-item text-muted">No new notifications</span></li>
                    <?php endif; ?>
                </ul>
            </div>
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
