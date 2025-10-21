<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['index'])) {
    $index = intval($_POST['index']);
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    // 🟢 Check if the cart item exists
    if (isset($_SESSION['cart'][$index])) {
        $item = $_SESSION['cart'][$index];

        // 🟡 Build a structure for each quantity (unit)
        $unit_items = [];
        for ($i = 1; $i <= $quantity; $i++) {
            $key = 'selected_addons_' . $i;
            $selected_addons = isset($_POST[$key]) ? $_POST[$key] : [];

            $unit_items[] = [
                'selected_addons' => $selected_addons
            ];
        }

        // 🟢 Update quantity and detailed item list
        $item['quantity'] = $quantity;
        $item['items'] = $unit_items;

        // 🧮 Recalculate total price per item
        $total = 0;
        foreach ($unit_items as $unit) {
            $unit_price = $item['price'];
            if (!empty($unit['selected_addons'])) {
                foreach ($unit['selected_addons'] as $addon) {
                    $parts = explode("|", $addon);
                    $addon_price = isset($parts[1]) ? (float)$parts[1] : 0;
                    $unit_price += $addon_price;
                }
            }
            $total += $unit_price;
        }

        $item['total_price'] = $total;

        // 🟢 Save back into session
        $_SESSION['cart'][$index] = $item;
    }
}

header("Location: cart.php");
exit();
