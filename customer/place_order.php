<?php
session_start();
include '../db.connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_items = $_SESSION['cart'] ?? [];

if (empty($cart_items)) {
    echo "<script>alert('Your cart is empty!'); window.location.href='cart.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup_option = $_POST['pickup_option'] ?? '';
    $scheduled_time = $_POST['scheduled_time'] ?? null;

    if (empty($pickup_option)) {
        echo "<script>alert('Please select a pickup option.'); window.history.back();</script>";
        exit();
    }

    // 🧮 Compute total amount again for safety
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $quantity = $item['quantity'] ?? 1;
        $item_price = (float)$item['price'];
        $addons_total = 0;

        if (!empty($item['items'])) {
            foreach ($item['items'] as $unit) {
                $unit_total = $item_price;
                if (!empty($unit['selected_addons'])) {
                    foreach ($unit['selected_addons'] as $addon) {
                        $parts = explode('|', $addon);
                        $unit_total += (float)$parts[1];
                    }
                }
                $subtotal += $unit_total;
            }
        } else {
            $subtotal += $item_price * $quantity;
        }
    }

    $service_fee = 5;
    $total_amount = $subtotal + $service_fee;

    // 🟢 Insert into orders table
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, total_amount, pickup_option, scheduled_time, status)
        VALUES (?, ?, ?, ?, 'Pending')
    ");
    $stmt->bind_param("idss", $user_id, $total_amount, $pickup_option, $scheduled_time);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // 🟢 Insert each item into order_items table
    $order_item_stmt = $conn->prepare("
        INSERT INTO order_items (order_id, item_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($cart_items as $item) {
        $item_id = $item['item_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];

        // Add addon cost if any
        $addons_total = 0;
        if (!empty($item['items'])) {
            foreach ($item['items'] as $unit) {
                if (!empty($unit['selected_addons'])) {
                    foreach ($unit['selected_addons'] as $addon) {
                        $parts = explode('|', $addon);
                        $addons_total += (float)$parts[1];
                    }
                }
            }
        }

        $final_price = ($price + $addons_total);
        $order_item_stmt->bind_param("iiid", $order_id, $item_id, $quantity, $final_price);
        $order_item_stmt->execute();

        // ✅ Decrease stock for each ordered item
        $update_stock = $conn->prepare("UPDATE menu SET stock = GREATEST(stock - ?, 0) WHERE item_id = ?");
        $update_stock->bind_param("ii", $quantity, $item_id);
        $update_stock->execute();
        $update_stock->close();
    }
    $order_item_stmt->close();

    // 🟢 Add a notification for the user
    $message = "Your order #$order_id has been placed successfully!";
    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $notif_stmt->bind_param("is", $user_id, $message);
    $notif_stmt->execute();
    $notif_stmt->close();

    // 🧹 Clear the cart
    unset($_SESSION['cart']);

    echo "<script>
        alert('Order placed successfully!');
        window.location.href='success.php?order_id=$order_id';
    </script>";
    exit();
} else {
    header("Location: checkout.php");
    exit();
}
?>
