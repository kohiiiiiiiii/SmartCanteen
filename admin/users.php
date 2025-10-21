<?php
session_start();
include '../db.connect.php';

// ✅ Handle Delete User
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_user'])) {
    $user_id = intval($_POST['delete_user']);

    // Optional: Prevent deleting own account
    if ($user_id == ($_SESSION['user_id'] ?? 0)) {
        echo "<script>alert('❌ You cannot delete your own account!'); window.location.href='users.php';</script>";
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('🗑️ User deleted successfully!'); window.location.href='users.php';</script>";
        exit();
    } else {
        echo "<script>alert('❌ Error deleting user: " . addslashes($conn->error) . "'); window.location.href='users.php';</script>";
        exit();
    }
    $stmt->close();
}

// ✅ Handle Add User
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_user'])) {
    $first_name  = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name   = trim($_POST['last_name'] ?? '');
    $suffix      = trim($_POST['suffix'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $role        = $_POST['role'] ?? 'Student';
    $password    = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $profile_pic = ''; // Optional placeholder for now

    $stmt = $conn->prepare("
        INSERT INTO users (first_name, middle_name, last_name, suffix, email, password, role, profile_pic)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        die('SQL prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("ssssssss", $first_name, $middle_name, $last_name, $suffix, $email, $password, $role, $profile_pic);

    if ($stmt->execute()) {
        echo "<script>alert('✅ User added successfully!'); window.location.href='users.php';</script>";
        exit();
    } else {
        echo "<script>alert('❌ Error: Email already exists or invalid input.'); window.location.href='users.php';</script>";
        exit();
    }
    $stmt->close();
}

// ✅ Handle Update User
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_user'])) {
    $id          = intval($_POST['edit_user_id']);
    $first_name  = trim($_POST['edit_first_name']);
    $middle_name = trim($_POST['edit_middle_name']);
    $last_name   = trim($_POST['edit_last_name']);
    $suffix      = trim($_POST['edit_suffix']);
    $email       = trim($_POST['edit_email']);
    $role        = $_POST['edit_role'];
    $contact     = trim($_POST['edit_contact'] ?? '');

    $stmt = $conn->prepare("
        UPDATE users 
        SET first_name=?, middle_name=?, last_name=?, suffix=?, email=?, role=?, contact=? 
        WHERE id=?
    ");
    if (!$stmt) {
        die('SQL prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("sssssssi", $first_name, $middle_name, $last_name, $suffix, $email, $role, $contact, $id);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('✏️ User updated successfully!'); window.location.href='users.php';</script>";
    exit();
}

// ✅ Handle Update User
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_user'])) {
    $id          = intval($_POST['edit_user_id']);
    $first_name  = trim($_POST['edit_first_name']);
    $middle_name = trim($_POST['edit_middle_name']);
    $last_name   = trim($_POST['edit_last_name']);
    $suffix      = trim($_POST['edit_suffix']);
    $email       = trim($_POST['edit_email']);
    $role        = $_POST['edit_role'];
    $contact     = trim($_POST['edit_contact'] ?? "");

    $stmt = $conn->prepare("UPDATE users SET first_name=?, middle_name=?, last_name=?, suffix=?, email=?, role=?, contact=? WHERE id=?");
    $stmt->bind_param("sssssssi", $first_name, $middle_name, $last_name, $suffix, $email, $role, $contact, $id);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('✏️ User updated successfully!'); window.location.href='users.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SmartCanteen - Admin Dashboard</title>
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

<!-- Content -->
<div class="content">
  <div class="topbar d-flex justify-content-between align-items-center">
    <h5>Manage Users</h5>
    <div>
      <i class="bi bi-bell-fill fs-4 text-light"></i>
    </div>
  </div>

  <!-- User Table Layout -->
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
      <thead class="table recentcard">
        <tr>
          <th>ID</th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Created At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
       <?php
        $result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { 
                $full_name = htmlspecialchars(
                    $row['first_name'] . 
                    (!empty($row['middle_name']) ? ' ' . $row['middle_name'] : '') . 
                    ' ' . $row['last_name'] . 
                    (!empty($row['suffix']) ? ' ' . $row['suffix'] : '')
                );
                echo "<tr>
                        <td>" . htmlspecialchars($row['id']) . "</td>
                        <td>$full_name</td>
                        <td>" . htmlspecialchars($row['email']) . "</td>
                        <td>" . htmlspecialchars(ucfirst($row['role'])) . "</td>
                        <td>" . htmlspecialchars($row['created_at']) . "</td>
                        <td>
                          <!-- View Button -->
                          <button class='btn btn-success btn-sm me-1' 
                                  data-bs-toggle='modal' data-bs-target='#viewUserModal'
                                  onclick='viewUser(
                                    ".json_encode($row['id']).",
                                    ".json_encode($full_name).",
                                    ".json_encode($row['email']).",
                                    ".json_encode(ucfirst($row['role'])).",
                                    ".json_encode($row['created_at']).",
                                    ".json_encode($row['contact'] ?? "").",
                                    ".json_encode($row['profile_pic'] ?? "")."
                                  )'>
                            <i class='bi bi-eye'></i>
                          </button>

                          <!-- Edit Button -->
                          <button class='btn btn-primary btn-sm me-1'
                                  data-bs-toggle='modal' data-bs-target='#editUserModal'
                                  onclick='editUser(
                                    ".json_encode($row['id']).",
                                    ".json_encode($row['first_name']).",
                                    ".json_encode($row['last_name']).",
                                    ".json_encode($row['email']).",
                                    ".json_encode($row['role']).",
                                    ".json_encode($row['contact'] ?? "").",
                                    ".json_encode($row['profile_pic'] ?? "")."
                                  )'>
                            <i class='bi bi-pencil-square'></i>
                          </button>

                          <!-- Delete Button -->
                          <form method='POST' action='' style='display:inline-block;' onsubmit='return confirm(\"Delete this user?\")'>
                            <input type='hidden' name='delete_user' value='".htmlspecialchars($row['id'])."'>
                            <button type='submit' class='btn btn-danger btn-sm'>
                              <i class='bi bi-trash'></i>
                            </button>
                          </form>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='7' class='text-center'>No users found.</td></tr>";
        }
        $conn->close();
        ?>  
      </tbody>
    </table>
  </div>

  <!-- Add User Button -->
  <button class="btn btn-warning mt-3 mb-3 mx-auto d-block" data-bs-toggle="modal" data-bs-target="#addUserModal">
    <i class="bi bi-person-plus"></i> Add User
  </button>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="">
      <div class="modal-header">
        <h5 class="modal-title">Add New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">First Name</label>
          <input type="text" class="form-control" name="first_name" id="firstname">
        </div>
        <div class="mb-3">
          <label class="form-label">Middle Name</label>
          <input type="text" class="form-control" name="middle_name" id="middlename">
        </div>
        <div class="mb-3">
          <label class="form-label">Last Name</label>
          <input type="text" class="form-control" name="last_name" id="lastname">
        </div>
        <div class="mb-3">
          <label class="form-label">Suffix</label>
          <input type="text" class="form-control" name="suffix" id="suffix">
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email">
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" name="password">
        </div>
        <div class="mb-3">
          <label class="form-label">Role</label>
          <select class="form-select" name="role">
            <option value="student">Student</option>
            <option value="staff">Staff</option>
            <option value="manager">Manager</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Contact</label>
          <input type="text" class="form-control" name="contact">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" name="add_user" class="btn btn-dashboard">Add User</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="">
      <div class="modal-header">
        <h5 class="modal-title">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="edit_user_id" id="editUserId">
        <div class="mb-3">
          <label class="form-label">First Name</label>
          <input type="text" class="form-control" name="edit_first_name" id="editFirstName">
        </div>
        <div class="mb-3">
          <label class="form-label">Middle Name</label>
          <input type="text" class="form-control" name="edit_middle_name" id="editMiddleName">
        </div>
        <div class="mb-3">
          <label class="form-label">Last Name</label>
          <input type="text" class="form-control" name="edit_last_name" id="editLastName">
        </div>
        <div class="mb-3">
          <label class="form-label">Suffix</label>
          <input type="text" class="form-control" name="edit_suffix" id="editSuffix">
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="edit_email" id="editEmail">
        </div>
        <div class="mb-3">
          <label class="form-label">Role</label>
          <select class="form-select" name="edit_role" id="editRole">
            <option value="student">Student</option>
            <option value="staff">Staff</option>
            <option value="manager">Manager</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Contact</label>
          <input type="text" class="form-control" name="edit_contact" id="editContact">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" name="update_user" class="btn btn-dashboard">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">User Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <!-- Avatar -->
        <img id="viewUserAvatar" src="../assets/img/user_avatar.png" 
             alt="User Avatar" class="rounded-circle mb-3" width="100" height="100">

        <p><strong>ID:</strong> <span id="viewUserId"></span></p>
        <p><strong>Full Name:</strong> <span id="viewUserName"></span></p>
        <p><strong>Email:</strong> <span id="viewUserEmail"></span></p>
        <p><strong>Role:</strong> <span id="viewUserRole"></span></p>
        <p><strong>Contact:</strong> <span id="viewUserContact"></span></p>
        <p><strong>Created At:</strong> <span id="viewUserCreated"></span></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-dashboard" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
function viewUser(id, fullName, email, role, createdAt, contact, profilePic) {
  document.getElementById("viewUserId").textContent = id;
  document.getElementById("viewUserName").textContent = fullName;
  document.getElementById("viewUserEmail").textContent = email;
  document.getElementById("viewUserRole").textContent = role;
  document.getElementById("viewUserContact").textContent = contact || '';
  document.getElementById("viewUserCreated").textContent = createdAt;

  // Set avatar image
  let avatar = profilePic ? "../uploads/" + profilePic : "../assets/img/user_avatar.png";
  document.getElementById("viewUserAvatar").src = avatar;
}

function editUser(id, firstName, middle_name, lastName, suffix, email, role, contact) {
  document.getElementById("editUserId").value = id;
  document.getElementById("editFirstName").value = firstName;
  document.getElementById("editMiddleName").value = middleName;
  document.getElementById("editLastName").value = lastName;
  document.getElementById("editSuffix").value = suffix;
  document.getElementById("editEmail").value = email;
  document.getElementById("editRole").value = role;
  document.getElementById("editContact").value = contact || "";
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
