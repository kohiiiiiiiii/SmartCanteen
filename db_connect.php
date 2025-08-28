<?php
$host = "localhost";
$user = "root";
$password = "smartcanteen"; // or your root password
$database = "Smart_Canteen";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully!";
?>
