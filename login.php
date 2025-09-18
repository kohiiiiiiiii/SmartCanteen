<?php
session_start();

// ✅ connect to DB
$conn = new mysqli("localhost", "root", "smartcanteen", "smart_canteen");

// check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, role, password 
                            FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $first_name, $last_name, $user_email, $role, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // ✅ store session
            $_SESSION['user_id']    = $id;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name']  = $last_name;
            $_SESSION['email']      = $user_email;
            $_SESSION['role']       = $role;

            // ✅ redirect based on role
            if ($role === "admin") {
                header("Location: admin/index.html");
            } elseif ($role === "manager") {
                header("Location: manager/index.html");
            } else {
                header("Location: customer/index.html");
            }
            exit;
        } else {
            echo "<script>alert('Invalid password!'); window.location.href='login.html';</script>";
        }
    } else {
        echo "<script>alert('No account found with that email!'); window.location.href='login.html';</script>";
    }

    $stmt->close();
}
$conn->close();
?>
