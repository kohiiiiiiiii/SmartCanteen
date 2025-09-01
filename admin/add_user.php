<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "smartcanteen", "smart_canteen");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $password, $role);

    if ($stmt->execute()) {
        echo "<script>alert('✅ User added successfully!'); window.location.href='users.html';</script>";
    } else {
        echo "<script>alert('❌ Error: Email might already exist.'); window.location.href='users.html';</script>";
    }

    $stmt->close();
}
$conn->close();
?>
