<?php
session_start();

// If not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

// Store user details
$first_name = $_SESSION['first_name'];
$last_name  = $_SESSION['last_name'];
$role       = $_SESSION['role'];
?>