<?php
session_start();
include '../db.connect.php';

// ✅ Only allow Admin or Manager
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Manager'])) {
    header("Location: ../login.php");
    exit();
}

$order_id = $_GET['id'] ?? 0;
$order = null;

// Fetch order data
if($order_id){
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
}

if(!$order){
    echo "Order not found.";
    exit();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $status = $_POST['status'] ?? $order['status'];

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $stmt->close();

    header("Location: orders.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Order #<?= htmlspecialchars($order_id) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/index_manager.css">
</head>
<body>
<div class="container mt-5">
  <h3>Edit Order #<?= htmlspecialchars($order_id) ?></h3>
  <form method="POST">
    <div class="mb-3">
      <label>Customer Name</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($order['customer_name']) ?>" disabled>
    </div>
    <div class="mb-3">
      <label>Total Amount</label>
      <input type="text" class="form-control" value="₱<?= number_format($order['total_amount'],2) ?>" disabled>
    </div>
    <div class="mb-3">
      <label>Status</label>
      <select name="status" class="form-control">
        <option value="Active" <?= $order['status']=='Active'?'selected':'' ?>>Active</option>
        <option value="Completed" <?= $order['status']=='Completed'?'selected':'' ?>>Completed</option>
        <option value="Cancelled" <?= $order['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
      </select>
    </div>
    <button class="btn btn-success">Update Order</button>
    <a href="orders.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
