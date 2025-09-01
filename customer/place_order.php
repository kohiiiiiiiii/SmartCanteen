<?php
session_start();
include 'db.php'; // 🔗 your database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get order data from frontend
$data = json_decode(file_get_contents("php://input"), true);
$cart = $data['cart'] ?? [];
$dine_option = $data['dine_option'] ?? 'pickup'; 
$schedule_time = $data['schedule_time'] ?? null;

if (empty($cart)) {
    echo json_encode(["status" => "error", "message" => "Cart is empty"]);
    exit;
}

// Insert into orders table
$stmt = $conn->prepare("INSERT INTO orders (user_id, dine_option, schedule_time) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $dine_option, $schedule_time);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

// Insert each item into order_items
$itemStmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");

foreach ($cart as $item) {
    $menu_id = $item['menu_id'];  // frontend must send menu_id
    $qty = $item['qty'];
    $price = $item['price'];      // frontend must send price

    $itemStmt->bind_param("iiid", $order_id, $menu_id, $qty, $price);
    $itemStmt->execute();
}
$itemStmt->close();

echo json_encode(["status" => "success", "message" => "Order placed!", "order_id" => $order_id]);
$conn->close();
?>