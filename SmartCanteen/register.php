<?php

    // ✅ connect to DB
    $conn = new mysqli("localhost", "root", "smartcanteen", "smart_canteen");

    if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $suffix = $_POST['suffix'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];  

    // hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users 
        (first_name, middle_name, last_name, suffix, email, password, role) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssssss", $first_name, $middle_name, $last_name, $suffix, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        echo "<script>
                alert('Registration successful! You can now log in.');
                window.location.href = 'login.html';
              </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
