<?php
session_start();
include '../db.connect.php';

$user_id = $_SESSION['user_id'] ?? 0;
$conn->query("
    UPDATE notifications
    SET is_read = 1
    WHERE user_id IS NULL OR user_id = $user_id
");
?>
