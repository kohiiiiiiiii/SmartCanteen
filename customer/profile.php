<?php
session_start();
include '../db.connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'] ?? '';
$middle_name = $_SESSION['middle_name'] ?? '';
$last_name = $_SESSION['last_name'] ?? '';
$suffix = $_SESSION['suffix'] ?? '';
$role = $_SESSION['role'] ?? 'Customer';

// Fetch user info from database safely
$sql = "SELECT email, profile_pic, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Database prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc() ?? [];

// Assign variables with fallback values
$email = $user['email'] ?? '';
$contact = $user['contact'] ?? ''; // keep it if exists in DB, else empty
$profile_pic = $user['profile_pic'] ?? '';
$joined = $user['created_at'] ?? '';

// Decide which profile picture to show
$profile_src = "../assets/img/" . ($profile_pic ?: "user_avatar.png");

// Format full name with middle initial
$middle_display = $middle_name ? " " . strtoupper(substr($middle_name, 0, 1)) . "." : "";
$full_name = trim($first_name . $middle_display . " " . $last_name . " " . $suffix);

// Cart count
$cart_count = count($_SESSION['cart'] ?? []);

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
<title>SmartCanteen | Profile</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="../assets/css/index.css">
<link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <img src="../assets/img/smartcanteenLogo.png" class="logo d-block mx-auto my-4">
    <div class="links mb-4">
        <a href="index.php"><i class="bi bi-house-door"></i> Home</a>
        <a href="about.php"><i class="bi bi-info-circle"></i> About</a>
    </div>
    <div class="profile-bar d-flex align-items-center justify-content-between p-3 border-top">
        <div class="d-flex align-items-center">
            <a href="profile.php" class="profile-link me-3">
                <img src="<?= htmlspecialchars($profile_src) ?>" alt="Profile" class="rounded-circle" width="50" height="50">
            </a>
            <div>
                <h6 class="mb-0"><?= htmlspecialchars($first_name) ?></h6>
                <small class="text-muted"><?= htmlspecialchars($role) ?></small>
            </div>
        </div>
        <a href="../logout.php" class="logout text-danger fs-5"><i class="bi bi-box-arrow-right"></i></a>
    </div>
</div>

<!-- Content -->
<div class="content py-4 px-3">
    <section class="container my-5" id="profile">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card p-4 bg-light rounded shadow-sm">
            <div class="text-center">
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
</div>

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
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($full_name) ?>" disabled>
          </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
