<?php
session_start();
include '../db.connect.php';

// Set timezone
date_default_timezone_set('Asia/Manila');

// Fetch session info
$firstName  = $_SESSION['first_name'] ?? '';
$middleName = $_SESSION['middle_name'] ?? '';
$lastName   = $_SESSION['last_name'] ?? '';
$suffix     = $_SESSION['suffix'] ?? '';
$role       = $_SESSION['role'] ?? 'manager';

$fullName = trim($firstName . ($middleName ? " $middleName" : "") . " $lastName" . ($suffix ? " $suffix" : ""));

// ------------------
// Fetch dashboard data
// ------------------

// Pending orders
$result = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status = 'Pending'");
$pendingOrders = $result ? $result->fetch_assoc()['total'] : 0;

// ✅ Sales today (Completed orders only)
$result = $conn->query("
  SELECT IFNULL(SUM(total_amount), 0) AS total
  FROM orders
  WHERE status = 'Completed'
    AND DATE(created_at) = CURDATE()
");
$salesToday = $result ? $result->fetch_assoc()['total'] : 0;

// Recent orders (latest 5)
$recentOrdersQuery = "
SELECT o.order_id, m.name AS meal_name, u.first_name, u.last_name, o.status, o.created_at
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
JOIN menu m ON oi.item_id = m.item_id
JOIN users u ON o.user_id = u.id
ORDER BY o.created_at DESC
LIMIT 5
";
$recentOrders = $conn->query($recentOrdersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SmartCanteen - Manager Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/index_manager.css">

  <style>
    /* Hover effect for clickable cards */
    .hover-shadow:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.15);
      transition: all 0.2s ease-in-out;
      cursor: pointer;
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <img src="../assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo d-block mx-auto">

  <div class="links">
    <a href="index.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="menu.php"><i class="bi bi-journal-text"></i> Manage Menu</a>
    <a href="orders.php"><i class="bi bi-receipt"></i> Orders</a>
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
    <h5>Welcome back, <span id="managerName"><?= htmlspecialchars($firstName) ?></span></h5>
    <i class="bi bi-bell-fill fs-4 text-light"></i>
  </div>

  <!-- Dashboard cards -->
  <div class="row mt-4 g-4">
    <!-- Pending Orders -->
    <div class="col-md-6">
      <a href="orders.php?status=Pending" class="text-decoration-none text-dark">
        <div class="card card-custom text-center p-4 hover-shadow">
          <h6>🛒 Pending Orders</h6>
          <h3><?= $pendingOrders ?></h3>
        </div>
      </a>
    </div>

    <!-- Sales Today -->
    <div class="col-md-6">
      <a href="orders.php?filter=today&status=Completed" class="text-decoration-none text-dark">
        <div class="card card-custom text-center p-4 hover-shadow">
          <h6>💰 Sales Today</h6>
          <h3>₱<?= number_format($salesToday, 2) ?></h3>
        </div>
      </a>
    </div>
  </div>

  <!-- Recent Orders Table -->
  <div class="card card-custom mt-4">
    <div class="card-header bg-warning text-dark fw-bold">
      Recent Orders
    </div>
    <div class="card-body">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Meal</th>
            <th>Customer</th>
            <th>Status</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody>
          <?php if($recentOrders && $recentOrders->num_rows > 0): ?>
            <?php while($row = $recentOrders->fetch_assoc()): ?>
            <tr>
              <td><?= $row['order_id'] ?></td>
              <td><?= htmlspecialchars($row['meal_name']) ?></td>
              <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
              <td>
                <?php if($row['status'] === 'Pending'): ?>
                  <span class="badge bg-warning text-dark">Pending</span>
                <?php elseif($row['status'] === 'Completed'): ?>
                  <span class="badge bg-success">Completed</span>
                <?php else: ?>
                  <span class="badge bg-secondary"><?= htmlspecialchars($row['status']) ?></span>
                <?php endif; ?>
              </td>
              <td><?= date('h:i A', strtotime($row['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center">No recent orders found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
