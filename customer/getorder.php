<?php
include '../db.connect.php';
if (!isset($_SESSION['user_id'])) exit(json_encode([]));

$conn = new mysqli("localhost", "root", "smartcanteen", "smart_canteen");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$user_id = $_SESSION['user_id'];
$sql = "SELECT o.order_id, o.status, m.name AS meal_name 
        FROM orders o
        JOIN menu m ON o.item_id = m.item_id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
echo json_encode($orders);
?>
