<?php
session_start();

// if not logged in, kick back to login
if (!isset($_SESSION['user_id'])) {
  header("Location: /../login.php");
  exit();
}

// fetch user info from session
$first_name  = $_SESSION['first_name'] ?? '';
$middle_name = $_SESSION['middle_name'] ?? '';
$last_name   = $_SESSION['last_name'] ?? '';
$suffix      = $_SESSION['suffix'] ?? '';
$email       = $_SESSION['email'] ?? '';
$role        = $_SESSION['role'] ?? '';
$joined      = $_SESSION['created_at'] ?? '';
$contact     = $_SESSION['contact'] ?? '';
$profile_pic = $_SESSION['profile_pic'] ?? ''; // only filename from DB

// ✅ decide what image to show
$profile_src = "../assets/img/" . ($profile_pic ?: "user_avatar.png");

// ✅ format full name with full middle name
$middle_display = $middle_name ? " " . strtoupper(substr($middle_name, 0, 1)) . "." : "";
$full_name = trim($first_name . $middle_display . " " . $last_name . " " . $suffix);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SmartCanteen - Manage Menu Items</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="../assets/css/index_admin.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <img src="../assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo d-block mx-auto">
  <a href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
  <a href="users.php" class="active"><i class="bi bi-people"></i> Manage Users</a>
  <a href="menu.php"><i class="bi bi-journal-text"></i> Manage Menu</a>
  <a href="orders.php"><i class="bi bi-receipt"></i> Orders</a>
  
  <div class="profile-bar d-flex justify-content-between align-items-center bg-light rounded shadow-sm">
    <div class="d-flex align-items-center">
      <a href="profile.php" class="profile-link"><img src="../assets/img/user_avatar.png" alt="Profile" class="rounded-circle me-3" width="50" height="50"></a>
      <div>
        <h6 class="mb-0"><span id="profileName"><?= htmlspecialchars($_SESSION['first_name'] ?? '') ?></span></h6>
        <small class="text-muted" id="profileRole"><?= htmlspecialchars($_SESSION['role'] ?? '') ?></small>
      </div>
    </div>
    <a href="../logout.php" class="logout text-danger"><i class="bi bi-box-arrow-right"></i></a>
  </div>
</div>

<section class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card p-4 bg-light rounded shadow-sm">
        <div class="text-center">
          <!-- ✅ Clean profile picture -->
          <img src="<?= htmlspecialchars($profile_src) ?>" 
               alt="User Avatar" class="rounded-circle mb-3 avatar"/>
          <br>
          <h3 class="text-brand d-inline-block me-2">
            <?= htmlspecialchars($full_name) ?>
          </h3>
          <a href="#" data-bs-toggle="modal" data-bs-target="#editProfileModal">
            <i class="bi bi-pencil-square text-warning fs-5"></i>
          </a>
          <p class="text-muted"><?= htmlspecialchars($role) ?></p>
        </div>
        <hr />
        <div class="row">
          <div class="col-md-6">
            <h5 class="text-highlight">📧 Email</h5>
            <p><?= htmlspecialchars($email) ?: "Not provided" ?></p>
          </div>
          <div class="col-md-6">
            <h5 class="text-highlight">📞 Contact Number</h5>
            <p><?= htmlspecialchars($contact) ?: "Not provided" ?></p>
            <h5 class="text-highlight">📅 Joined</h5>
            <p><?= $joined ? date("F j, Y", strtotime($joined)) : "Not provided" ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Edit Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editProfileForm" method="POST" action="update_profile.php" enctype="multipart/form-data">

          <!-- ✅ Show name fields but disabled -->
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($full_name) ?>" disabled>
          </div>

          <!-- ✅ Editable fields -->
          <div class="mb-3">
            <label for="editEmail" class="form-label">Email</label>
            <input type="email" class="form-control" id="editEmail" name="email" value="<?= htmlspecialchars($email) ?>" required>
          </div>
          <div class="mb-3">
            <label for="editContact" class="form-label">Contact Number</label>
            <input type="text" class="form-control" id="editContact" name="contact" value="<?= htmlspecialchars($contact) ?>">
          </div>
          <div class="mb-3">
            <label for="editProfilePic" class="form-label">Profile Picture</label>
            <input type="file" class="form-control" id="editProfilePic" name="profile_pic" accept="image/*">
          </div>

          <div class="text-end">
            <button type="submit" class="btn btn-warning">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

</body>
</html>
