<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name   = $_POST['first_name'] ?? '';
    $middle_name  = $_POST['middle_name'] ?? '';
    $last_name    = $_POST['last_name'] ?? '';
    $suffix       = $_POST['suffix'] ?? '';
    $email        = $_POST['email'] ?? '';
    $password     = $_POST['password'] ?? '';
    $role         = $_POST['role'] ?? '';

    // ✅ connect to DB
    $conn = new mysqli("localhost", "root","smartcanteen"," Smart_Canteen");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // ✅ hash password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // ✅ insert into table
    $sql = "INSERT INTO users (first_name, middle_name, last_name, suffix, email, password, role) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $first_name, $middle_name, $last_name, $suffix, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        echo "<h2>Registration Successful!</h2>";
        echo "<p>Name: " . htmlspecialchars($first_name) . " " . htmlspecialchars($middle_name) . " " . htmlspecialchars($last_name) . " " . htmlspecialchars($suffix) . "</p>";
        echo "<p>Email: " . htmlspecialchars($email) . "</p>";
        echo "<p>Role: " . htmlspecialchars($role) . "</p>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
