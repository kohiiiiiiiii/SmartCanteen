<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // ✅ connect to DB
    $conn = new mysqli("localhost", "root", "", "your_database_name");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // ✅ check if email exists
    $sql = "SELECT id, first_name, last_name, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // ✅ verify password
        if (password_verify($password, $user['password'])) {
            // store session
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . " " . $user['last_name'];
            $_SESSION['role']      = $user['role'];

            echo "<h2>Welcome, " . htmlspecialchars($_SESSION['user_name']) . "!</h2>";
            echo "<p>Role: " . htmlspecialchars($_SESSION['role']) . "</p>";
            echo "<a href='logout.php'>Logout</a>";
        } else {
            echo "<p style='color:red;'>Invalid password.</p>";
        }
    } else {
        echo "<p style='color:red;'>No account found with that email.</p>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
