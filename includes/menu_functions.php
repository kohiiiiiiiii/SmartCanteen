<?php
include '../db.connect.php';

$uploadDir = "../uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
$debugLog = $uploadDir . "menu_debug.log";

// small helper: append debug message
function dbg($msg) {
    global $debugLog;
    file_put_contents($debugLog, date("[Y-m-d H:i:s] ") . $msg . PHP_EOL, FILE_APPEND);
}

// Fetch all menu items
function getMenuItems($conn) {
    return $conn->query("SELECT * FROM menu ORDER BY created_at DESC");
}

// Fetch add-ons for a menu item
function getMenuAddons($conn, $item_id) {
    $result = $conn->query("SELECT addon_name, addon_price FROM menu_addons WHERE item_id=" . intval($item_id) . " ORDER BY addon_name ASC");
    $addons = [];
    while ($row = $result->fetch_assoc()) {
        $addons[] = $row; // keys: addon_name, addon_price
    }
    return $addons;
}

// Delete menu item
function deleteMenuItem($conn, $item_id, $uploadDir="../uploads/") {
    $imageResult = $conn->query("SELECT image FROM menu WHERE item_id = $item_id");
    if($imageRow = $imageResult->fetch_assoc()){
        if(!empty($imageRow['image']) && file_exists($uploadDir.$imageRow['image'])){
            unlink($uploadDir.$imageRow['image']);
        }
    }
    $conn->query("DELETE FROM menu_addons WHERE item_id=$item_id");
    return $conn->query("DELETE FROM menu WHERE item_id=$item_id");
}

// Add menu item with add-ons
function addMenuItem($conn, $data, $uploadDir="../uploads/") {
    dbg("---- addMenuItem called ----");

    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error']===0){
        $image = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir.$image);
        dbg("Uploaded image saved: " . $image);
    }

    $availability_start = !empty($data['availability_start']) ? date("H:i:s", strtotime($data['availability_start'])) : null;
    $availability_end   = !empty($data['availability_end'])   ? date("H:i:s", strtotime($data['availability_end']))   : null;

    $stmt = $conn->prepare("
        INSERT INTO menu (name, description, price, category, stock, availability_start, availability_end, created_by, created_at, image)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "ssdssissss",
        $data['name'],
        $data['description'],
        $data['price'],
        $data['category'],
        $data['stock'],
        $availability_start,
        $availability_end,
        $data['created_by'],
        $data['created_at'],
        $image
    );
    $ok = $stmt->execute();
    if(!$ok) { dbg("Insert failed: ".$stmt->error); return false; }

    $item_id = $stmt->insert_id;
    $stmt->close();

    // Save add-ons
    if(!empty($data['addon_name'])){
        $stmtA = $conn->prepare("INSERT INTO menu_addons (item_id, addon_name, addon_price) VALUES (?, ?, ?)");
        foreach($data['addon_name'] as $i=>$name){
            $name = trim($name);
            if($name === '') continue;
            $price = isset($data['addon_price'][$i]) ? floatval($data['addon_price'][$i]) : 0.0;
            $stmtA->bind_param("isd", $item_id, $name, $price);
            $stmtA->execute();
        }
        $stmtA->close();
    }

    dbg("Add menu item successful, id=$item_id");
    dbg("---- addMenuItem end ----");
    return true;
}

// Update menu item with add-ons
function updateMenuItem($conn, $data, $uploadDir="../uploads/") {
    dbg("---- updateMenuItem called ----");

    $item_id = intval($data['edit_item_id']);
    $current_image = $data['current_image'] ?? '';
    $image = $current_image;

    if(isset($_FILES['edit_image']) && $_FILES['edit_image']['error']===0){
        $image = time() . "_" . basename($_FILES['edit_image']['name']);
        move_uploaded_file($_FILES['edit_image']['tmp_name'], $uploadDir.$image);
        dbg("Uploaded edit image saved: " . $image);
    }

    $availability_start = !empty($data['edit_availability_start']) ? date("H:i:s", strtotime($data['edit_availability_start'])) : null;
    $availability_end   = !empty($data['edit_availability_end'])   ? date("H:i:s", strtotime($data['edit_availability_end']))   : null;

    // Update menu
    $stmt = $conn->prepare("
        UPDATE menu 
        SET name=?, description=?, price=?, category=?, stock=?, availability_start=?, availability_end=?, image=?
        WHERE item_id=?
    ");
    $stmt->bind_param(
        "ssdssissi",
        $data['edit_name'],
        $data['edit_description'],
        $data['edit_price'],
        $data['edit_category'],
        $data['edit_stock'],
        $availability_start,
        $availability_end,
        $image,
        $item_id
    );
    $ok = $stmt->execute();
    $stmt->close();
    if(!$ok){ dbg("Update failed: ".$conn->error); return false; }

    // Delete old add-ons
    $conn->query("DELETE FROM menu_addons WHERE item_id=$item_id");

    // Insert updated add-ons
    if(!empty($data['edit_addon_name'])){
        $stmtA = $conn->prepare("INSERT INTO menu_addons (item_id, addon_name, addon_price) VALUES (?, ?, ?)");
        foreach($data['edit_addon_name'] as $i=>$name){
            $name = trim($name);
            if($name === '') continue;
            $price = isset($data['edit_addon_price'][$i]) ? floatval($data['edit_addon_price'][$i]) : 0.0;
            $stmtA->bind_param("isd", $item_id, $name, $price);
            $stmtA->execute();
        }
        $stmtA->close();
    }

    dbg("Update menu item successful, id=$item_id");
    dbg("---- updateMenuItem end ----");
    return true;
}
?>
