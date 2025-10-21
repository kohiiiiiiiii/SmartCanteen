<?php
session_start();
include '../db.connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id    = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$cart_items = $_SESSION['cart'] ?? [];

// 🟢 Fetch add-ons for each item
foreach ($cart_items as $i => $item) {
    $item_id = $item['item_id'] ?? null;
    if ($item_id) {
        $addons_res = $conn->query("SELECT addon_name, addon_price FROM menu_addons WHERE item_id = $item_id");
        $addons = [];
        while ($row = $addons_res->fetch_assoc()) {
            $addons[] = $row['addon_name'] . "|" . $row['addon_price'];
        }
        $cart_items[$i]['addons'] = $addons;
        $_SESSION['cart'][$i]['addons'] = $addons;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmartCanteen - Cart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/index.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <img src="../assets/img/smartcanteenLogo.png" class="logo d-block mx-auto my-4">
    <div class="links mb-4">
        <a href="index.php"><i class="bi bi-house-door"></i> Home</a>
        <a href="about.php"><i class="bi bi-info-circle"></i> About</a>
    </div>
    <div class="profile-bar d-flex align-items-center justify-content-between p-3 border-top">
        <div class="d-flex align-items-center">
            <img src="../assets/img/user_avatar.png" alt="Profile" class="rounded-circle" width="50" height="50">
            <div>
                <h6 class="mb-0"><?= htmlspecialchars($first_name) ?></h6>
                <small class="text-muted">Customer</small>
            </div>
        </div>
        <a href="../logout.php" class="logout text-danger fs-5">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="content py-4 px-3">
    <div class="topbar d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">🛍 My Cart</h5>
        <a href="index.php" class="btn btn-secondary">← Back to Menu</a>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <ul class="list-group shadow-sm">
                <?php if (!empty($cart_items)): ?>
                    <?php foreach ($cart_items as $index => $item): ?>
                        <?php
                        $item_total = 0;
                        $unit_items = $item['items'] ?? [];
                        if (!empty($unit_items)) {
                            foreach ($unit_items as $unit) {
                                $unit_price = $item['price'];
                                if (!empty($unit['selected_addons'])) {
                                    foreach ($unit['selected_addons'] as $addon) {
                                        $parts = explode("|", $addon);
                                        $addon_price = isset($parts[1]) ? (float)$parts[1] : 0;
                                        $unit_price += $addon_price;
                                    }
                                }
                                $item_total += $unit_price;
                            }
                        } else {
                            $item_total = $item['price'] * $item['quantity'];
                        }
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="me-3" style="width:60px; height:60px; object-fit:cover; border-radius:10px;">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                    <?php if (!empty($unit_items)): ?>
                                        <?php foreach ($unit_items as $u_i => $u_item): ?>
                                            <small class="text-muted d-block">
                                                <strong>#<?= $u_i + 1 ?>:</strong>
                                                <?= !empty($u_item['selected_addons'])
                                                    ? implode(", ", array_map(fn($a) => explode("|", $a)[0], $u_item['selected_addons']))
                                                    : "No add-ons" ?>
                                            </small>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <small class="text-muted">No add-ons</small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="text-end">
                                <p class="mb-1 fw-semibold text-success">₱<?= number_format($item_total, 2) ?></p>

                                <button type="button" 
                                        class="btn btn-sm btn-warning w-100 mb-2"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editModal"
                                        onclick='openEditModal(<?= $index ?>, <?= json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>

                                <form method="POST" action="remove_item.php">
                                    <input type="hidden" name="index" value="<?= $index ?>">
                                    <button type="submit" class="btn btn-sm btn-danger w-100">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-muted text-center py-4">Your cart is empty.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- 🧾 Order Summary -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">Order Summary</h5>
                    <?php
                    $subtotal = 0;
                    foreach ($cart_items as $item) {
                        if (!empty($item['items'])) {
                            foreach ($item['items'] as $unit) {
                                $unit_total = $item['price'];
                                if (!empty($unit['selected_addons'])) {
                                    foreach ($unit['selected_addons'] as $addon) {
                                        $parts = explode("|", $addon);
                                        $unit_total += (float)$parts[1];
                                    }
                                }
                                $subtotal += $unit_total;
                            }
                        } else {
                            $subtotal += $item['price'] * $item['quantity'];
                        }
                    }
                    $service_fee = 5;
                    $total = $subtotal + $service_fee;
                    ?>
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>₱<?= number_format($subtotal, 2) ?></span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Service Fee</span><span>₱<?= number_format($service_fee, 2) ?></span></div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold mb-3"><span>Total</span><span>₱<?= number_format($total, 2) ?></span></div>
                    <?php if (!empty($cart_items)): ?>
                        <a href="checkout.php" class="btn btn-success w-100">Proceed to Checkout</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 🟢 Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="update_item.php">
        <div class="modal-header bg-warning-subtle">
          <h5 class="modal-title" id="editModalLabel">Edit Order</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="index" id="editIndex">
          <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" class="form-control" id="editQuantity" name="quantity" min="1" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Add-ons per Item</label>
            <div id="editAddonsContainer"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openEditModal(index, item) {
  const container = document.getElementById('editAddonsContainer');
  const qtyInput = document.getElementById('editQuantity');
  document.getElementById('editIndex').value = index;
  qtyInput.value = item.quantity;

  function renderAddons() {
    const quantity = parseInt(qtyInput.value);
    container.innerHTML = '';

    if (item.addons && item.addons.length > 0) {
      for (let q = 1; q <= quantity; q++) {
        const selected = (item.items && item.items[q - 1]?.selected_addons) || [];
        let html = `<div class='border rounded p-2 mb-2 bg-light'>
                      <strong>Item #${q}</strong>`;
        item.addons.forEach(addon => {
          const [name, price] = addon.split('|');
          const checked = selected.some(a => a.split('|')[0] === name);
          html += `
            <div class='form-check'>
              <input class='form-check-input' type='checkbox' name='selected_addons_${q}[]' value='${name}|${price}' ${checked ? 'checked' : ''}>
              <label class='form-check-label'>${name} (₱${parseFloat(price).toFixed(2)})</label>
            </div>`;
        });
        html += `</div>`;
        container.innerHTML += html;
      }
    } else {
      container.innerHTML = '<small class="text-muted">No add-ons available for this item.</small>';
    }
  }

  renderAddons();

  // 🟢 Re-render when quantity changes
  qtyInput.oninput = renderAddons;
}
</script>

</body>
</html>
