<?php
session_start();
include '../db.connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$cart_items = $_SESSION['cart'] ?? [];

if (empty($cart_items)) {
    echo "<script>alert('Your cart is empty.'); window.location.href='index.php';</script>";
    exit();
}

// 🟢 Calculate subtotal and total
$subtotal = 0;
foreach ($cart_items as $item) {
    $quantity = $item['quantity'] ?? 1;
    $item_price = $item['price'];
    $addons_total = 0;

    if (!empty($item['items'])) {
        foreach ($item['items'] as $unit) {
            $unit_total = $item['price'];
            if (!empty($unit['selected_addons'])) {
                foreach ($unit['selected_addons'] as $addon) {
                    $parts = explode('|', $addon);
                    $unit_total += (float)$parts[1];
                }
            }
            $subtotal += $unit_total;
        }
    } else {
        $subtotal += $item_price * $quantity;
    }
}

$service_fee = 5;
$total = $subtotal + $service_fee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SmartCanteen - Checkout</title>
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
    <h5 class="mb-0">🧾 Checkout</h5>
    <a href="cart.php" class="btn btn-secondary">← Back to Cart</a>
  </div>

  <div class="row">
    <!-- 🛍 ORDER DETAILS -->
    <div class="col-lg-8 mb-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-3">Your Order</h5>
          <ul class="list-group list-group-flush">
            <?php foreach ($cart_items as $item): ?>
              <li class="list-group-item d-flex justify-content-between align-items-start">
                <div>
                  <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)</h6>
                  <?php if (!empty($item['items'])): ?>
                    <?php foreach ($item['items'] as $unit): ?>
                      <small class="text-muted d-block">
                        <?= !empty($unit['selected_addons'])
                            ? implode(", ", array_map(fn($a) => explode("|", $a)[0], $unit['selected_addons']))
                            : "No add-ons" ?>
                      </small>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <span class="fw-semibold text-success">
                  ₱<?= number_format($item['price'] * $item['quantity'], 2) ?>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>

    <!-- 💳 ORDER SUMMARY -->
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-3">Order Summary</h5>
          <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>₱<?= number_format($subtotal, 2) ?></span></div>
          <div class="d-flex justify-content-between mb-2"><span>Service Fee</span><span>₱<?= number_format($service_fee, 2) ?></span></div>
          <hr>
          <div class="d-flex justify-content-between fw-bold mb-3"><span>Total</span><span>₱<?= number_format($total, 2) ?></span></div>

          <!-- 🟢 CHECKOUT FORM -->
          <form method="POST" action="place_order.php">
            <div class="mb-3">
              <label class="form-label">Pickup Option</label>
              <select name="pickup_option" class="form-select" required>
                <option value="">-- Select Option --</option>
                <option value="dine-in">Dine-In</option>
                <option value="take-out">Take-Out</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Scheduled Time (optional)</label>
              <input type="time" class="form-control" name="scheduled_time">
            </div>

            <button type="submit" class="btn btn-success w-100">Place Order</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
