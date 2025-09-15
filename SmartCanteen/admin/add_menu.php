<?php
session_start();

// Only managers/admins can add menu items
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['manager', 'admin'])) {
    header("Location: ../login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "smartcanteen", "smart_canteen");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);

    // Handle image upload
    $targetDir = "../uploads/menu/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES["image"]["name"]);
    $targetFile = $targetDir . $fileName;
    move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);

    $stmt = $conn->prepare("INSERT INTO menu_items (name, category, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdis", $name, $category, $description, $price, $stock, $fileName);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Menu item added successfully!'); window.location.href='menu.html';</script>";
    } else {
        echo "<script>alert('❌ Error adding menu item.'); window.location.href='menu.html';</script>";
    }

    $stmt->close();
}
$conn->close();
?>
