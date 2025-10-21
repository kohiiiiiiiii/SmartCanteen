<?php
// ✅ Make session available across all folders (root, admin, manager, customer)
ini_set('session.cookie_path', '/');
session_start();

include 'db.connect.php'; // adjust path if needed

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']);

    // ✅ Find user by email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // ✅ Verify password
        if (password_verify($password, $user['password'])) {

            // ✅ Save session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['middle_name'] = $user['middle_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['suffix'] = $user['suffix'];
            $_SESSION['role'] = $user['role'];

            // ✅ Remember me
            if ($remember) {
                setcookie('email', $email, time() + (86400 * 30), "/"); // 30 days
            } else {
                setcookie('email', '', time() - 3600, "/");
            }

            // ✅ Redirect based on role
            switch ($user['role']) {
                case 'Admin':
                    header("Location: admin/index.php");
                    break;
                case 'Manager':
                    header("Location: manager/index.php");
                    break;
                default:
                    header("Location: customer/index.php");
                    break;
            }
            exit();
        } else {
            echo "<script>alert('Invalid password'); window.location='login.php';</script>";
        }
    } else {
        echo "<script>alert('Email not found'); window.location='login.php';</script>";
    }
}
?>
