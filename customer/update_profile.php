<?php
include '../db.connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get form data
$email   = trim($_POST['email']);
$contact = trim($_POST['contact']);

// Default: keep old profile picture filename
$profile_pic = $_SESSION['profile_pic'] ?? "user_avatar.png";

// ✅ Handle new upload
if (!empty($_FILES['profile_pic']['name'])) {
    $targetDir  = "../assets/img/";
    $fileName   = basename($_FILES['profile_pic']['name']);
    $fileExt    = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = "profile_" . uniqid() . "." . $fileExt;
    $targetFilePath = $targetDir . $newFileName;

    // Allowed file types
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($fileExt, $allowedTypes)) {
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFilePath)) {
            // ✅ Delete old profile picture (but NOT the default)
            $oldPic = "../assets/img/" . $profile_pic;
            if ($profile_pic !== "user_avatar.png" && file_exists($oldPic)) {
                unlink($oldPic);
            }

            // Save only filename for DB/session
            $profile_pic = $newFileName;
        }
    }
}

// ✅ Update DB (adjust column names if different!)
$sql = "UPDATE users 
        SET email = ?, contact = ?, profile_pic = ?
        WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL error in UPDATE: " . $conn->error);
}

// bind params: email (string), contact (string), profile_pic (string), id (int)
$stmt->bind_param("sssi", $email, $contact, $profile_pic, $user_id);

if ($stmt->execute()) {
    // ✅ Update session so changes reflect immediately
    $_SESSION['email']       = $email;
    $_SESSION['contact']     = $contact;
    $_SESSION['profile_pic'] = $profile_pic;

    echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
} else {
    echo "<script>alert('Error updating profile: " . $stmt->error . "'); window.location.href='profile.php';</script>";
}
?>
