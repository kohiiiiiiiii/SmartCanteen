<?php
// ✅ Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../db.connect.php';

/* =========================================
   ✅ ADD ORDER
   ========================================= */
if (isset($_POST['add_order'])) {
    $user_id = $_POST['user_id'];
    $pickup_option = $_POST['pickup_option'];
    $scheduled_time = $_POST['scheduled_time'];
    $total_amount = $_POST['total_amount'];

    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, pickup_option, scheduled_time, total_amount)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("issd", $user_id, $pickup_option, $scheduled_time, $total_amount);
    $stmt->execute();
    $stmt->close();

    redirectBack();
}

//UPDATE ORDER

if (isset($_POST['update_order'])) {
    $order_id = $_POST['order_id'];
    $user_id = $_POST['user_id'];
    $pickup_option = $_POST['pickup_option'];
    $scheduled_time = $_POST['scheduled_time'];
    $total_amount = $_POST['total_amount'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("
        UPDATE orders 
        SET user_id=?, pickup_option=?, scheduled_time=?, total_amount=?, status=? 
        WHERE order_id=?
    ");
    $stmt->bind_param("issdsi", $user_id, $pickup_option, $scheduled_time, $total_amount, $status, $order_id);
    $stmt->execute();
    $stmt->close();

    redirectBack();
}

// MARK AS COMPLETED (GET method support)

if (isset($_GET['complete'])) {
    $order_id = intval($_GET['complete']);

    if ($order_id > 0) {
        // ✅ 1. Mark the order as Completed
        $stmt = $conn->prepare("UPDATE orders SET status='Completed' WHERE order_id=?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();

        // ✅ 2. Reduce stock for each item in this order
        $itemsQuery = $conn->prepare("SELECT item_id, quantity FROM order_items WHERE order_id=?");
        $itemsQuery->bind_param("i", $order_id);
        $itemsQuery->execute();
        $itemsResult = $itemsQuery->get_result();

        while ($item = $itemsResult->fetch_assoc()) {
            $item_id = $item['item_id'];
            $qty = $item['quantity'];

            // Prevent stock from going below zero
            $updateStock = $conn->prepare("
                UPDATE menu 
                SET stock = GREATEST(stock - ?, 0)
                WHERE item_id = ?
            ");
            $updateStock->bind_param("ii", $qty, $item_id);
            $updateStock->execute();
            $updateStock->close();
        }

        $itemsQuery->close();
    }

    redirectBack();
}

// DELETE ORDER

if (isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];

    if (!empty($order_id)) {
        $stmt = $conn->prepare("DELETE FROM orders WHERE order_id=?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();

        // Also remove related order items
        $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id=?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();
    }

    redirectBack();
}

// GET ALL ORDERS
   
function getAllOrders($conn) {
    $sql = "
        SELECT 
            o.*, 
            CONCAT(u.first_name, ' ', u.last_name) AS user_name 
        FROM orders o 
        INNER JOIN users u ON o.user_id = u.id
        ORDER BY o.order_id DESC
    ";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// REDIRECT BACK TO THE RIGHT DASHBOARD
function redirectBack() {
    $role = $_SESSION['role'] ?? '';

    if ($role === 'Manager') {
        header("Location: ../manager/orders.php");
    } else {
        header("Location: ../admin/orders.php");
    }
    exit();
}
?>
