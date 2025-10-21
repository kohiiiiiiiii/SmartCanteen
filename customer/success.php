<?php
session_start();
include '../db.connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    header("Location: index.php");
    exit();
}

// 🟢 Fetch order details
$order_sql = $conn->prepare("
    SELECT o.*, u.first_name, u.last_name 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.order_id = ? AND o.user_id = ?
");
$order_sql->bind_param("ii", $order_id, $user_id);
$order_sql->execute();
$order = $order_sql->get_result()->fetch_assoc();

if (!$order) {
    echo "<script>alert('Order not found.'); window.location.href='index.php';</script>";
    exit();
}

// 🟢 Fetch order items
$items_sql = $conn->prepare("
    SELECT oi.*, m.name 
    FROM order_items oi
    JOIN menu m ON oi.item_id = m.item_id
    WHERE oi.order_id = ?
");
$items_sql->bind_param("i", $order_id);
$items_sql->execute();
$order_items = $items_sql->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SmartCanteen - Order Success</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/index.css">
</head>
<body>

<!-- 🟢 SIDEBAR -->
<div class="sidebar">
  <img src="../assets/img/smartcanteenLogo.png" class="logo d-block mx-auto my-4">
  <div class="links mb-4">
    <a href="index.php"><i class="bi bi-house-door"></i> Home</a>
    <a href="about.php"><i class="bi bi-info-circle"></i> About</a>
  </div>
  <div class="profile-bar d-flex align-items-center justify-content-between p-3 border-top">
    <div class="d-flex align-items-center">
      <img src="../assets/img/user_avatar.png" alt="Profile" class="rounded-circle" width="50" height="50">
      <div>
        <h6 class="mb-0"><?= htmlspecialchars($first_name) ?></h6>
        <small class="text-muted">Customer</small>
      </div>
    </div>
    <a href="../logout.php" class="logout text-danger fs-5">
      <i class="bi bi-box-arrow-right"></i>
    </a>
  </div>
</div>

<!-- 🟢 MAIN CONTENT -->
<div class="content py-4 px-3">
  <div class="topbar d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">✅ Order Confirmation</h5>
  </div>

  <div class="card shadow-sm border-success">
    <div class="card-body text-center py-5">
      <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
      <h3 class="mt-3 mb-1 text-success">Order Placed Successfully!</h3>
      <p class="text-muted mb-4">Thank you for ordering, <?= htmlspecialchars($first_name) ?>!</p>
      <h5 class="fw-bold mb-1">Order #<?= $order['order_id'] ?></h5>
      <p class="text-secondary mb-4">
        <?= ucfirst($order['pickup_option']) ?> • 
        ₱<?= number_format($order['total_amount'], 2) ?> • 
        <?= $order['status'] ?>
      </p>

      <a href="index.php" class="btn btn-success px-4"><i class="bi bi-house"></i> Back to Home</a>
    </div>
  </div>

  <div class="card shadow-sm mt-4">
    <div class="card-body">
      <h5 class="card-title mb-3">Order Details</h5>
      <ul class="list-group">
        <?php while ($item = $order_items->fetch_assoc()): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)
            <span>₱<?= number_format($item['price'], 2) ?></span>
          </li>
        <?php endwhile; ?>
      </ul>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
