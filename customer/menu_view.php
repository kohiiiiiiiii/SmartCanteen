<?php
session_start();
include '../db.connect.php';

$currentTime = date('H:i:s');

// ✅ Fetch only currently available menu items
$menu_sql = "
    SELECT *
    FROM menu
    WHERE stock > 0
      AND (availability_start IS NULL OR availability_start <= '$currentTime')
      AND (availability_end IS NULL OR availability_end >= '$currentTime')
    ORDER BY created_at DESC
";
$menu_result = $conn->query($menu_sql);
if ($menu_result === false) die("Error fetching menu: " . $conn->error);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SmartCanteen - Menu (View Only)</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
body { background-color: #f8f9fa; }
.navbar { background-color: #0c2340; }
.navbar .nav-link { color: white !important; }
.navbar .nav-link:hover { color: #ffc107 !important; }
.menu-card {
  border: none;
  border-radius: 15px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  background-color: white;
  transition: 0.3s;
}
.menu-card:hover { transform: translateY(-5px); }
.menu-card img {
  border-top-left-radius: 15px;
  border-top-right-radius: 15px;
  height: 180px;
  object-fit: cover;
}
.view-only { color: gray; font-style: italic; }
</style>
</head>
<body>

<!-- 🔹 NAVBAR -->
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
      <a href="../login.php" class="nav-link active d-flex align-items-center">
        <img src="../assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo me-2" style="width:40px;">
        <span class="fw-bold text-white">SmartCanteen</span>
      </a>
      <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end gap-3" id="navbarCollapse">
        <a href="menu_view.php" class="nav-link text-warning fw-bold">Home</a>
        <a href="about.php" class="nav-link">About</a>
        <a href="../login.php" class="nav-link">Login</a>
        <a href="../register.php" class="nav-link">Register</a>
      </div>
  </div>
</nav>

<!-- 🔹 MENU LIST -->
<div class="container mt-5">
  <h2 class="text-center mb-4 text-primary">Available Menu</h2>
  <div class="row">
    <?php if ($menu_result && $menu_result->num_rows > 0): ?>
      <?php while ($row = $menu_result->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
          <div class="card menu-card">
            <img src="../uploads/<?php echo htmlspecialchars($row['image'] ?: 'no-image.png'); ?>" 
                 alt="<?php echo htmlspecialchars($row['name']); ?>" 
                 class="card-img-top">
            <div class="card-body text-center">
              <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
              <p class="card-text">₱<?php echo number_format($row['price'], 2); ?></p>
              <p class="view-only">Login to order</p>
              <button class="btn btn-secondary btn-sm" disabled>Order</button>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="text-center text-muted">No menu items available right now.</p>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
