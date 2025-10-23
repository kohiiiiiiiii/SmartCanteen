<?php
session_start();
include '../db.connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$currentTime = date('H:i:s');

// Fetch session info
$firstName  = $_SESSION['first_name'] ?? '';
$middleName = $_SESSION['middle_name'] ?? '';
$lastName   = $_SESSION['last_name'] ?? '';
$suffix     = $_SESSION['suffix'] ?? '';
$role       = $_SESSION['role'] ?? 'Customer';

// Fetch ALL menu items
$menu_sql = "SELECT * FROM menu ORDER BY created_at ASC";
$menu_result = $conn->query($menu_sql);
if ($menu_result === false) {
    die('Error fetching menu: ' . $conn->error);
}

// Initialize cart
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Handle Add to Cart / Order Now
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];
    $name = $_POST['name'];
    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['quantity'];
    $image = $_POST['image'] ?? '';

    // ✅ Correct relative path for image
    // Image path is relative to the root, so cart.php can access it easily
    $imagePath = !empty($item['image']) ? '../' . $item['image'] : '../assets/img/no-image.png';

    $addons = $_POST['addons'] ?? [];
    $selected_addons = $_POST['selected_addons'] ?? [];

    $cart_item = [
        'item_id' => $item_id,
        'name' => $name,
        'price' => $price,
        'quantity' => $quantity,
        'image' => $image_path, // store full relative path
        'addons' => $addons,
        'selected_addons' => $selected_addons
    ];

    // Check if item already exists in cart
    $found = false;
    foreach ($_SESSION['cart'] as $index => $ci) {
        if ($ci['item_id'] == $item_id) {
            $_SESSION['cart'][$index] = $cart_item; // update existing
            $found = true;
            break;
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = $cart_item; // add new
    }

    // Redirect
    if (isset($_POST['order_now'])) {
        header("Location: cart.php");
        exit();
    } else {
        header("Location: index.php");
        exit();
    }
}


// Cart count
$cart_count = count($_SESSION['cart']);

// Notifications
$notif_sql = "SELECT notif_id, message, created_at FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC";
$stmt_notif = $conn->prepare($notif_sql);
$stmt_notif->bind_param("i", $user_id);
$stmt_notif->execute();
$notif_result = $stmt_notif->get_result();
$unread_count = $notif_result->num_rows;
$notifications = $notif_result->fetch_all(MYSQLI_ASSOC);

// Order history
$order_sql = "
    SELECT o.order_id, o.created_at AS order_date, o.total_amount, o.status,
           m.name AS item_name, oi.quantity, oi.price
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN menu m ON oi.item_id = m.item_id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
";
$stmt = $conn->prepare($order_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$order_result = $stmt->get_result();

// Group orders by order_id
$orders = [];
while ($row = $order_result->fetch_assoc()) {
    $orderId = $row['order_id'];
    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'order_date' => $row['order_date'],
            'total_amount' => $row['total_amount'],
            'status' => $row['status'],
            'items' => []
        ];
    }
    $orders[$orderId]['items'][] = [
        'name' => $row['item_name'],
        'quantity' => $row['quantity'],
        'price' => $row['price']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SmartCanteen - Customer Dashboard</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="../assets/css/index.css">
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <img src="../assets/img/smartcanteenLogo.png" class="logo d-block mx-auto my-4">
    <div class="links mb-4">
        <a href="index.php" class="active"><i class="bi bi-house-door"></i> Home</a>
        <a href="about.php"><i class="bi bi-info-circle"></i> About</a>
        <a href="#orderhistory"><i class="bi bi-clock-history"></i> Order History</a>
    </div>
    <div class="profile-bar d-flex align-items-center justify-content-between p-3 border-top">
        <div class="d-flex align-items-center">
            <a href="profile.php" class="profile-link me-3">
                <img src="../assets/img/user_avatar.png" alt="Profile" class="rounded-circle" width="50" height="50">
            </a>
            <div>
                <h6 class="mb-0"><?= htmlspecialchars($first_name) ?></h6>
                <small class="text-muted">Customer</small>
            </div>
        </div>
        <a href="../logout.php" class="logout text-danger fs-5"><i class="bi bi-box-arrow-right"></i></a>
    </div>
</div>

<!-- Content -->
<div class="content py-4 px-3">
    <!-- Topbar -->
    <div class="topbar d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">Welcome, <?= htmlspecialchars($first_name) ?>!</h5>
        <div class="d-flex gap-3">
            <a href="cart.php" class="text-light fs-4 position-relative">
                <i class="bi bi-cart-fill"></i>
                <?php if($cart_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>

            <!-- Notification Bell -->
            <div class="dropdown">
                <a href="#" class="text-light fs-4 position-relative" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell-fill">
                    <?php if($unread_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $unread_count ?></span>
                    <?php endif; ?></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown" style="min-width: 250px;">
                    <?php if($unread_count > 0): ?>
                        <?php foreach($notifications as $notif): ?>
                            <li>
                                <a class="dropdown-item" href="order_history.php?notif_id=<?= $notif['notif_id'] ?>">
                                    <?= htmlspecialchars($notif['message']) ?><br>
                                    <small class="text-muted"><?= date('F j, Y h:i A', strtotime($notif['created_at'])) ?></small>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><span class="dropdown-item text-muted">No new notifications</span></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Menu Section -->
    <section class="menu container mb-5">
        <h3 class="mb-4">🍴 Today’s Menu</h3>
        <!-- Search & Category Filter -->
        <div class="row mb-4">
            <div class="col-md-6 mb-2">
                <input type="text" id="searchInput" class="form-control" placeholder="Search menu...">
            </div>
            <div class="col-md-6 mb-2">
                <select id="filterCategory" class="form-select">
                    <option value="all">All Categories</option>
                    <option value="breakfast">Breakfast</option>
                    <option value="lunch">Lunch</option>
                    <option value="snacks">Snacks</option>
                    <option value="beverages">Beverages</option>
                </select>
            </div>
        </div>

        <div class="row g-4" id="menuCards">
            <?php if($menu_result->num_rows > 0): ?>
                <?php while($row = $menu_result->fetch_assoc()):
                    $imgSrc = !empty($row['image']) ? "../uploads/".$row['image'] : "../assets/img/no-image.png";
                    $addons_result = $conn->query("SELECT addon_name, addon_price FROM menu_addons WHERE item_id={$row['item_id']}");
                    $addons = [];
                    while($addon = $addons_result->fetch_assoc()){
                        $addons[] = $addon['addon_name'] . ":" . $addon['addon_price'];
                    }
                    $addons_str = !empty($addons) ? implode("|", $addons) : "None";
                    $category = strtolower($row['category'] ?? 'all');
                ?>
                <div class="col-md-4 menu-card-wrapper" 
                     data-name="<?= htmlspecialchars(strtolower($row['name'])) ?>" 
                     data-category="<?= $category ?>">
                    <div class="card h-100 shadow-sm border-0 menu-card p-3"
                         data-id="<?= $row['item_id'] ?>"
                         data-name="<?= htmlspecialchars($row['name']) ?>"
                         data-description="<?= htmlspecialchars($row['description']) ?>"
                         data-price="<?= $row['price'] ?>"
                         data-image="<?= $imgSrc ?>"
                         data-addons="<?= htmlspecialchars($addons_str) ?>"
                         data-stock="<?= $row['stock'] ?>"
                         data-availability-start="<?= $row['availability_start'] ?>"
                         data-availability-end="<?= $row['availability_end'] ?>">
                        <img src="<?= $imgSrc ?>" class="card-img-top mb-3 rounded" alt="<?= htmlspecialchars($row['name']) ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title mb-2"><?= htmlspecialchars($row['name']) ?></h5>
                            <p class="text-muted small mb-2"><?= htmlspecialchars($row['description']) ?></p>
                            <ul class="list-group list-group-flush text-start small mb-0">
                            <li class="list-group-item py-1">🍽 <strong>₱<?= number_format($row['price'],2) ?></strong></li>
                            <li class="list-group-item py-1">📦 
                                <?php if ($row['stock'] > 10): ?>
                                    <span class="badge bg-success"><?= $row['stock'] ?> In Stock</span>
                                <?php elseif ($row['stock'] > 0): ?>
                                    <span class="badge bg-warning text-dark"><?= $row['stock'] ?> Low Stock</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Out of Stock</span>
                                <?php endif; ?>
                            </li>
                            <li class="list-group-item py-1">
                            ⏰ Available: 
                            <?php 
                                if (!empty($row['availability_start']) && !empty($row['availability_end'])) {
                                    $start = date("h:i A", strtotime($row['availability_start']));
                                    $end   = date("h:i A", strtotime($row['availability_end']));
                                    echo $start . " - " . $end;
                                }
                            ?>
                        </li>
                            <li class="list-group-item py-1">✨ Add-ons:
                                <?php if($addons_str !== "None"):
                                    foreach(explode("|",$addons_str) as $addon):
                                        list($aName,$aPrice) = explode(":",$addon);
                                ?>
                                    <span class="badge bg-warning text-dark me-1 mb-1"><?= htmlspecialchars($aName) ?> (₱<?= number_format($aPrice,2) ?>)</span>
                                <?php endforeach; else: ?> None <?php endif; ?>
                            </li>
                        </ul>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">No menu available today.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Order History Section -->
    <section class="order-history container mb-5" id="orderhistory">
        <h3 class="mb-4">🧾 Your Order History</h3>

        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $orderId => $order): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Order #<?= $orderId ?></strong><br>
                        <small class="text-muted"><?= date('F j, Y h:i A', strtotime($order['order_date'])) ?></small>
                    </div>
                    <div>
                        <span class="badge <?= $order['status'] === 'Completed' ? 'bg-success' : 'bg-warning text-dark' ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </div>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush mb-2">
                        <?php foreach ($order['items'] as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?>
                            </div>
                            <span>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="d-flex justify-content-end">
                        <strong>Total: ₱<?= number_format($order['total_amount'], 2) ?></strong>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">You haven't placed any orders yet.</p>
        <?php endif; ?>
    </section>
</div>

<!-- Menu Modal -->
<div class="modal fade" id="menuModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" method="POST">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title fw-bold" id="menuModalLabel">Menu Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body text-center bg-light">
        <img id="modalItemImg" src="" class="img-fluid mb-3 rounded-3 shadow-sm" alt="">
        <p id="modalItemDesc" class="text-secondary small"></p>
        <p><strong class="fs-5 text-success">₱<span id="modalItemPriceText"></span></strong></p>
        <p id="modalItemAvailability" class="mb-3 fw-bold text-primary"></p>

        <div class="mb-3 text-start">
          <label for="modalQuantity" class="form-label fw-semibold">Quantity:</label>
          <input type="number" id="modalQuantity" name="quantity" class=" rounded-pill bg-warning text-center" value="1" min="1">
        </div>

        <div id="modalAddonsWrapper" class="mb-3 text-start">
          <p class="fw-semibold mb-2 text-dark"><i class="bi bi-plus-circle-fill text-warning"></i> Add-ons:</p>
          <div id="modalAddons" class="d-flex flex-wrap gap-4"></div>
        </div>

        <input type="hidden" id="modalItemId" name="item_id">
        <input type="hidden" id="modalItemName" name="name">
        <input type="hidden" id="modalItemPrice" name="price">
        <input type="hidden" id="modalItemImage" name="image">
      </div>

      <div class="modal-footer bg-warning-subtle d-flex justify-content-between">
        <button type="submit" name="add_to_cart" class="btn btn-outline-dark btn-warning rounded-pill px-4">
          <i class="bi bi-cart-plus"></i> Add to Cart
        </button>
        <button type="submit" name="order_now" class="btn btn-success rounded-pill px-4">
          <i class="bi bi-bag-check"></i> Order Now
        </button>
      </div>
    </form>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    
    // Filter menu
    const searchInput = document.getElementById("searchInput");
    const filterCategory = document.getElementById("filterCategory");
    const menuCards = document.querySelectorAll("#menuCards .menu-card-wrapper");

    function filterMenu() {
        const query = searchInput.value.toLowerCase();
        const categoryFilter = filterCategory.value.toLowerCase();

        menuCards.forEach(card => {
            const name = card.dataset.name;
            const category = card.dataset.category;
            const matchSearch = name.includes(query);
            const matchCategory = categoryFilter === "all" || category === categoryFilter;
            card.style.display = (matchSearch && matchCategory) ? "" : "none";
        });
    }

    searchInput.addEventListener("input", filterMenu);
    filterCategory.addEventListener("change", filterMenu);

    // Modal logic
    const modal = new bootstrap.Modal(document.getElementById('menuModal'));
    const modalTitle = document.getElementById('menuModalLabel');
    const modalItemId = document.getElementById('modalItemId');
    const modalItemName = document.getElementById('modalItemName');
    const modalItemPrice = document.getElementById('modalItemPrice');
    const modalItemImg = document.getElementById('modalItemImg');
    const modalItemDesc = document.getElementById('modalItemDesc');
    const modalAddonsWrapper = document.getElementById('modalAddonsWrapper');
    const modalAddons = document.getElementById('modalAddons');
    const modalQuantity = document.getElementById('modalQuantity');
    const modalAddToCartBtn = document.querySelector("button[name='add_to_cart']");
    const modalOrderNowBtn = document.querySelector("button[name='order_now']");
    const modalItemPriceText = document.getElementById('modalItemPriceText');

    document.querySelectorAll(".menu-card").forEach(card => {
        card.addEventListener("click", () => {
            const itemId = card.dataset.id;
            const name = card.dataset.name;
            const price = parseFloat(card.dataset.price).toFixed(2);
            const image = card.dataset.image;
            const description = card.dataset.description;
            const addonsStr = card.dataset.addons;
            const stock = parseInt(card.dataset.stock) || 0;

            modalTitle.textContent = name;
            modalItemId.value = itemId;
            modalItemName.value = name;
            modalItemPrice.value = price;
            modalItemImg.src = image;
            modalItemDesc.textContent = description;
            modalItemPriceText.textContent = price;
            modalQuantity.value = 1;
            modalQuantity.max = stock;

            // Add-ons
            modalAddons.innerHTML = '';
            if (addonsStr && addonsStr !== "None") {
                modalAddonsWrapper.style.display = 'block';
                addonsStr.split("|").forEach(addon => {
                    const [addonName, addonPrice] = addon.split(":");
                    const checkbox = document.createElement('div');
                    checkbox.classList.add('form-check');
                    checkbox.innerHTML = `
                        <input class="form-check-input" type="checkbox" name="selected_addons[]" value="${addonName}:${addonPrice}" id="addon_${addonName}">
                        <label class="form-check-label" for="addon_${addonName}">${addonName} (₱${parseFloat(addonPrice).toFixed(2)})</label>
                    `;
                    modalAddons.appendChild(checkbox);
                });
            } else {
                modalAddonsWrapper.style.display = 'none';
            }

            // Handle stock
            if (stock <= 0) {
                modalQuantity.disabled = true;
                modalAddToCartBtn.disabled = true;
                modalOrderNowBtn.disabled = true;
                if (!document.getElementById('outOfStockMsg')) {
                    const msg = document.createElement('p');
                    msg.id = 'outOfStockMsg';
                    msg.classList.add('text-danger', 'mt-2', 'text-center');
                    msg.textContent = '❌ Out of Stock';
                    modalQuantity.parentNode.appendChild(msg);
                }
            } else {
                modalQuantity.disabled = false;
                modalAddToCartBtn.disabled = false;
                modalOrderNowBtn.disabled = false;
                const existingMsg = document.getElementById('outOfStockMsg');
                if (existingMsg) existingMsg.remove();
            }

            modal.show();
        });
    });
});
</script>
</body>
</html>
