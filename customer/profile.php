<?php
session_start();
$conn = new mysqli("localhost", "root", "password", "smart_canteen");

// Get posted data
$email = $_POST['email'];
$password = $_POST['password'];

// Check user in DB
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, role, joined_date, location FROM users WHERE email=? AND password=? LIMIT 1");
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['id']; // optional for PHP sessions

    // Send user info to JS to store in localStorage
    ?>
    <script>
    const user = {
        first_name: "<?= $user['first_name'] ?>",
        last_name: "<?= $user['last_name'] ?>",
        email: "<?= $user['email'] ?>",
        role: "<?= $user['role'] ?>",
        joined_date: "<?= $user['joined_date'] ?>",
        location: "<?= $user['location'] ?>"
    };

    localStorage.setItem("first_name", user.first_name);
    localStorage.setItem("last_name", user.last_name);
    localStorage.setItem("email", user.email);
    localStorage.setItem("role", user.role);
    localStorage.setItem("joined_date", user.joined_date);
    localStorage.setItem("location", user.location);

    // Redirect to customer dashboard
    window.location.href = '../customer/index.html';
    </script>
    <?php
    exit();
} else {
    echo "<script>alert('Invalid email or password'); window.location.href='../login.html';</script>";
}
?>
