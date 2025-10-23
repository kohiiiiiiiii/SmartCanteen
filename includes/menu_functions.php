<?php
// ✅ No session_start() here — manager/menu.php already has it
include '../db.connect.php';

// 🔐 Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

/**
 * ✅ Get all menu items
 */
function getMenuItems($conn) {
    $items = [];
    $sql = "SELECT * FROM menu ORDER BY created_at DESC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    return $items;
}

/**
 * ✅ Add new menu item (handles $_POST from manager/menu.php)
 */
function addMenuItem($conn, $data) {
    $name = $data['name'];
    $description = $data['description'];
    $category = $data['category'];
    $price = $data['price'];
    $stock = $data['stock'];
    $availability_start = $data['availability_start'];
    $availability_end = $data['availability_end'];
    $created_by = $_SESSION['user_id'] ?? null;
    $created_at = date('Y-m-d H:i:s');

    // ✅ Handle image upload
    $imageName = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $targetDir . $imageName;
        move_uploaded_file($_FILES['image']['tmp_name'], $targetPath);
    }

    // ✅ Insert into menu table
    $stmt = $conn->prepare("INSERT INTO menu (name, description, category, price, stock, availability_start, availability_end, image, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssddsssis", $name, $description, $category, $price, $stock, $availability_start, $availability_end, $imageName, $created_by, $created_at);
    $stmt->execute();
    $menu_id = $stmt->insert_id;
    $stmt->close();

    // ✅ Handle add-ons
    if (!empty($data['addon_name'])) {
        foreach ($data['addon_name'] as $i => $addon_name) {
            $addon_price = $data['addon_price'][$i] ?? 0;
            if (!empty($addon_name)) {
                $stmt = $conn->prepare("INSERT INTO menu_addons (item_id, addon_name, addon_price) VALUES (?, ?, ?)");
                $stmt->bind_param("isd", $menu_id, $addon_name, $addon_price);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    return true;
}

/**
 * ✅ Update menu item (handles $_POST from manager/menu.php)
 */
function updateMenuItem($conn, $data) {
    $id = $data['edit_item_id'];
    $name = $data['edit_name'];
    $description = $data['edit_description'];
    $category = $data['edit_category'];
    $price = $data['edit_price'];
    $stock = $data['edit_stock'];
    $availability_start = $data['edit_availability_start'];
    $availability_end = $data['edit_availability_end'];
    $currentImage = $data['current_image'];

    // ✅ Handle image upload
    $newImage = $currentImage;
    if (!empty($_FILES['edit_image']['name'])) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        $newImage = time() . '_' . basename($_FILES['edit_image']['name']);
        $targetPath = $targetDir . $newImage;
        move_uploaded_file($_FILES['edit_image']['tmp_name'], $targetPath);
    }

    // ✅ Update menu
    $stmt = $conn->prepare("UPDATE menu SET name=?, description=?, category=?, price=?, stock=?, availability_start=?, availability_end=?, image=? WHERE item_id=?");
    $stmt->bind_param("sssddsssi", $name, $description, $category, $price, $stock, $availability_start, $availability_end, $newImage, $id);
    $stmt->execute();
    $stmt->close();

    // ✅ Update add-ons
    $conn->query("DELETE FROM menu_addons WHERE item_id=$id");
    if (!empty($data['edit_addon_name'])) {
        foreach ($data['edit_addon_name'] as $i => $addon_name) {
            $addon_price = $data['edit_addon_price'][$i] ?? 0;
            if (!empty($addon_name)) {
                $stmt = $conn->prepare("INSERT INTO menu_addons (item_id, addon_name, addon_price) VALUES (?, ?, ?)");
                $stmt->bind_param("isd", $id, $addon_name, $addon_price);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    return true;
}

/**
 * ✅ Delete menu item
 */
function deleteMenuItem($conn, $id) {
    $conn->query("DELETE FROM menu_addons WHERE item_id = $id");
    $conn->query("DELETE FROM menu WHERE item_id = $id");
    return true;
}
?>
