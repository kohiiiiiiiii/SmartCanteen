<?php
session_start();
include '../db.connect.php';

// ✅ Only allow Admin or Manager
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Manager'])) {
    echo 'unauthorized';
    exit();
}

// Get POST id
$order_id = $_POST['id'] ?? 0;

if($order_id){
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    if($stmt->execute()){
        echo 'success';
    } else {
        echo 'failed';
    }
    $stmt->close();
} else {
    echo 'failed';
}

$conn->close();
?>
