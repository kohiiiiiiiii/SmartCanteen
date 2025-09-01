<?php
session_start();

// ✅ Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SmartCanteen | Profile</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
  <link rel="stylesheet" href="assets/css/profile.css" />
  <link rel="stylesheet" href="assets/css/navbar.css">
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
      <a href="index.php" class="nav-link active"><img src="assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo"></a>
      <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-between gap-3" id="navbarCollapse">
        <div class="navbar-nav gap-5 w-100 padding">
          <a href="index.php" class="nav-link homecolor">Home</a>
          <a href="about.html" class="nav-link aboutcolor">About</a>
        </div>
        <div class="navbar-nav d-flex align-items-center gap-3">
          <a href="#" class="nav-link" id="notifBell" data-bs-toggle="dropdown">
            <i class="bi bi-bell-fill color icon-large"></i>
          </a>
          <span class="nav-link">👋 <?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
          <a href="logout.php" class="nav-link logincolor loginbg">Logout</a>
        </div>
      </div>
  </div>
</nav>

<section class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card p-4 profile-card shadow-sm">
        <div class="text-center">
          <img src="assets/img/user_avatar.png" alt="User Avatar" class="rounded-circle mb-3 avatar"/>
          <h5 class="text-highlight">👤 Name</h5>
            <p>
            <?php 
                echo htmlspecialchars($_SESSION['first_name'] . ' ' . ($_SESSION['last_name'] ?? '')); 
            ?>
            </p>

            <h5 class="text-highlight">💼 Role</h5>
            <p><?php echo htmlspecialchars($_SESSION['role'] ?? "User"); ?></p>
        </div>
        <hr />
        <div class="row">
          <div class="col-md-6">
            <h5 class="text-highlight">📧 Email</h5>
            <p><?php echo htmlspecialchars($_SESSION['email']); ?></p>

            <h5 class="text-highlight">📍 Location</h5>
            <p><?php echo htmlspecialchars($_SESSION['location'] ?? "Not set"); ?></p>

            <h5 class="text-highlight">📞 Contact Number</h5>
            <p><?php echo htmlspecialchars($_SESSION['contact'] ?? "Not set"); ?></p>

            <h5 class="text-highlight">📅 Joined</h5>
            <p><?php echo date("F Y", strtotime($_SESSION['joined_at'])); ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<footer class="bg-light text-center py-3 mt-5">
  <span class="text-muted">&copy; 2025 SmartCanteen. All rights reserved.</span>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title" id="editProfileLabel">Edit Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="update_profile.php" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label for="email" class="form-label">📧 Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ""); ?>" required>
          </div>
          <div class="mb-3">
            <label for="contact" class="form-label">📱 Contact Number</label>
            <input type="tel" class="form-control" id="contact" name="contact" value="+63 912 345 6789">
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">🔒 New Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password">
          </div>
          <div class="mb-3">
            <label for="confirm_password" class="form-label">🔒 Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
</html>
