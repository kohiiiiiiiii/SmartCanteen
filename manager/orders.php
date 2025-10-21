<?php
session_start();
include '../db.connect.php';
include __DIR__ . '/../includes/order_functions.php'; // shared functions

// Allow both Admin and Manager
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Manager'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch user info
$firstName  = $_SESSION['first_name'] ?? '';
$middleName = $_SESSION['middle_name'] ?? '';
$lastName   = $_SESSION['last_name'] ?? '';
$suffix     = $_SESSION['suffix'] ?? '';
$role       = $_SESSION['role'] ?? 'Manager';
$fullName   = trim($firstName . ($middleName ? " $middleName" : "") . " $lastName" . ($suffix ? " $suffix" : ""));
$currentPage = basename($_SERVER['PHP_SELF']);

// ✅ Mark as Completed
if (isset($_GET['complete'])) {
    $orderId = intval($_GET['complete']);
    $conn->query("UPDATE orders SET status = 'Completed' WHERE order_id = $orderId");
    header("Location: orders.php");
    exit;
}

// Fetch all orders
$orders = getAllOrders($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SmartCanteen - Orders</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/index_admin.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <img src="../assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo d-block mx-auto">

  <div class="links">
    <a href="index.php" class="<?= $currentPage == 'index.php' ? 'active' : '' ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
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
    <h5>Welcome back, <span id="userName"><?= htmlspecialchars($firstName) ?></span></h5>
    <i class="bi bi-bell-fill fs-4 text-light"></i>
  </div>

  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="text-white">Orders List</h4>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addOrderModal">
        <i class="bi bi-plus-circle"></i> Add Order
      </button>
    </div>

    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-warning">
          <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Pickup Option</th>
            <th>Status</th>
            <th>Scheduled</th>
            <th>Created At</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td><?= $order['order_id'] ?></td>
                <td><?= htmlspecialchars($order['user_name']) ?></td>
                <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                <td><?= ucfirst($order['pickup_option']) ?></td>
                <td>
                  <?php if ($order['status'] === 'Pending'): ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                  <?php elseif ($order['status'] === 'Completed'): ?>
                    <span class="badge bg-success">Completed</span>
                  <?php else: ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($order['status']) ?></span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($order['scheduled_time']) ?></td>
                <td><?= htmlspecialchars($order['created_at']) ?></td>
                <td class="text-center">
                  <!-- ✅ Mark as Completed button -->
                  <?php if ($order['status'] === 'Pending'): ?>
                    <a href="?complete=<?= $order['order_id'] ?>" class="btn btn-sm btn-success mb-1">
                      <i class="bi bi-check-circle"></i>
                    </a>
                  <?php endif; ?>

                  <!-- ✏️ Edit button -->
                  <button class="btn btn-sm btn-primary mb-1"
                    onclick="openEditModal(
                      <?= $order['order_id'] ?>,
                      <?= $order['user_id'] ?>,
                      '<?= $order['pickup_option'] ?>',
                      '<?= $order['scheduled_time'] ?>',
                      <?= $order['total_amount'] ?>,
                      '<?= $order['status'] ?>'
                    )">
                    <i class="bi bi-pencil-square"></i>
                  </button>

                  <!-- 🗑️ Delete -->
                  <form action="../includes/order_functions.php" method="POST" class="d-inline">
                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                    <button type="submit" name="delete_order" class="btn btn-sm btn-danger" onclick="return confirm('Delete this order?')">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center">No orders found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add & Edit Modals -->
<?php include __DIR__ . '/../includes/order_modals.php'; ?>

<script>
function openEditModal(id, user, pickup, time, total, status) {
  document.getElementById('edit_order_id').value = id;
  document.getElementById('edit_user_id').value = user;
  document.getElementById('edit_pickup_option').value = pickup;
  document.getElementById('edit_scheduled_time').value = time;
  document.getElementById('edit_total_amount').value = total;
  document.getElementById('edit_status').value = status;
  new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
