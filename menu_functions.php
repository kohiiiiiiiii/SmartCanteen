<?php
include '../db.connect.php'; // adjust path if needed

$uploadDir = "../uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Fetch all menu items
function getMenuItems($conn) {
    return $conn->query("SELECT * FROM menu ORDER BY created_at DESC");
}

// Fetch add-ons for a menu item
function getMenuAddons($conn, $item_id) {
    $result = $conn->query("SELECT addon_name, addon_price FROM menu_addons WHERE item_id=$item_id ORDER BY addon_name ASC");
    $addons = [];
    while($row = $result->fetch_assoc()) $addons[] = $row;
    return $addons;
}

// Delete a menu item
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

// Add menu item
function addMenuItem($conn, $data, $uploadDir="../uploads/") {
    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error']===0){
        $image = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir.$image);
    }

    $stmt = $conn->prepare("INSERT INTO menu (name, description, price, category, stock, availability_start, availability_end, created_by, created_at, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssissss", $data['name'], $data['description'], $data['price'], $data['category'], $data['stock'], $data['availability_start'], $data['availability_end'], $data['created_by'], $data['created_at'], $image);
    $stmt->execute();
    $menu_id = $stmt->insert_id;
    $stmt->close();

    // Add add-ons
    if(!empty($data['addon_name'])){
        $stmt = $conn->prepare("INSERT INTO menu_addons (item_id, addon_name, addon_price) VALUES (?, ?, ?)");
        foreach($data['addon_name'] as $i=>$addon_name){
            if(trim($addon_name)==='') continue;
            $stmt->bind_param("isd", $menu_id, $addon_name, $data['addon_price'][$i]);
            $stmt->execute();
        }
        $stmt->close();
    }
    return true;
}

// Update menu item
function updateMenuItem($conn, $data, $uploadDir="../uploads/") {
    $item_id = intval($data['edit_item_id']);
    $current_image = $data['current_image'];

    if(isset($_FILES['edit_image']) && $_FILES['edit_image']['error']===0){
        $image = time() . "_" . basename($_FILES['edit_image']['name']);
        move_uploaded_file($_FILES['edit_image']['tmp_name'], $uploadDir.$image);
    } else {
        $image = $current_image;
    }

    $conn->query("DELETE FROM menu_addons WHERE item_id=$item_id");

    if(!empty($data['edit_addon_name'])){
        $stmt = $conn->prepare("INSERT INTO menu_addons (item_id, addon_name, addon_price) VALUES (?, ?, ?)");
        foreach($data['edit_addon_name'] as $i=>$addon_name){
            if(trim($addon_name)==='') continue;
            $stmt->bind_param("isd", $item_id, $addon_name, $data['edit_addon_price'][$i]);
            $stmt->execute();
        }
        $stmt->close();
    }

    $stmt = $conn->prepare("UPDATE menu SET name=?, description=?, price=?, category=?, stock=?, availability_start=?, availability_end=?, image=? WHERE item_id=?");
    $stmt->bind_param("ssdssissi", $data['edit_name'], $data['edit_description'], $data['edit_price'], $data['edit_category'], $data['edit_stock'], $data['edit_availability_start'], $data['edit_availability_end'], $image, $item_id);
    $stmt->execute();
    $stmt->close();
    return true;
}
