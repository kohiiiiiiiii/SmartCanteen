<?php
// ✅ Database connection only
$conn = new mysqli("localhost", "root", "smartcanteen", "smart_canteen");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
