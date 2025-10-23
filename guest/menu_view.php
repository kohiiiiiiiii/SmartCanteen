<?php
session_start();
include '../db.connect.php';

// ✅ Fetch menu items (only in-stock and within time)
$currentTime = date('H:i:s');
$query = "
    SELECT * FROM menu
    WHERE stock > 0
      AND (availability_start IS NULL OR availability_start <= '$currentTime')
      AND (availability_end IS NULL OR availability_end >= '$currentTime')
    ORDER BY created_at DESC
";
$result = $conn->query($query);
if ($result === false) die('Error fetching menu: ' . $conn->error);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SmartCanteen | Guest Menu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/index.css">
</head>
<body>

<!-- ✅ Sidebar (identical to About page) -->
<div class="sidebar">
  <img src="../assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo d-block mx-auto">

  <div class="links">
    <a href="menu_view.php" class="active"><i class="bi bi-house-door"></i> Home</a>
    <a href="about.php"><i class="bi bi-info-circle"></i> About</a>
    <a href="../login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a>
    <a href="../register.php"><i class="bi bi-person-plus"></i> Register</a>
  </div>

  <div class="profile-bar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
      <img src="../assets/img/user_avatar.png" alt="Profile" class="rounded-circle me-3" width="50" height="50">
      <div>
        <h6 class="mb-0">Guest</h6>
        <small class="text-muted">Visitor</small>
      </div>
    </div>
  </div>
</div>

<!-- ✅ Main Content -->
<div class="content">
  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <h3 class="text-center mb-4 fw-bold">Available Menu</h3>
        <div class="row g-4">
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <div class="col-md-4 col-sm-6">
                <div class="card h-100 border-0 shadow-sm">
                  <img src="../uploads/<?php echo htmlspecialchars($row['image'] ?: 'no-image.png'); ?>" 
                       alt="<?php echo htmlspecialchars($row['name']); ?>" 
                       class="card-img-top" 
                       style="height: 180px; object-fit: cover;">
                  <div class="card-body text-center">
                    <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                    <p class="text-warning fw-bold">₱<?php echo number_format($row['price'], 2); ?></p>
                    <!-- ✅ Clickable button that navigates to login -->
                    <a href="../login.php" class="btn btn-secondary w-100">Login to Order</a>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-center text-muted">No menu items available right now.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
