<?php
session_start();
include '../db.connect.php';
include __DIR__ . '/../includes/menu_functions.php'; // menu functions

// Allow both Admin and Manager
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin','Manager'])){
    header("Location: ../login.php");
    exit();
}

// Session variables
$firstName  = $_SESSION['first_name'] ?? '';
$middleName = $_SESSION['middle_name'] ?? '';
$lastName   = $_SESSION['last_name'] ?? '';
$suffix     = $_SESSION['suffix'] ?? '';
$role       = $_SESSION['role'] ?? '';
$fullName = trim($firstName . ($middleName ? " $middleName" : "") . " $lastName" . ($suffix ? " $suffix" : ""));

// Ensure uploads folder exists
$uploadDir = "../uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Handle CRUD operations
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['delete_menu'])){
        deleteMenuItem($conn, intval($_POST['delete_menu']));
        header("Location: menu.php"); exit();
    }
    elseif(isset($_POST['add_menu'])){
        $_POST['created_by'] = $_SESSION['user_id'];
        $_POST['created_at'] = date('Y-m-d H:i:s');
        addMenuItem($conn, $_POST);
        header("Location: menu.php"); exit();
    }
    elseif(isset($_POST['update_menu'])){
        updateMenuItem($conn, $_POST);
        header("Location: menu.php"); exit();
    }
}

// Fetch menu items
$menuItems = getMenuItems($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SmartCanteen - Manage Menu Items</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="../assets/css/index_admin.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <img src="../assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo d-block mx-auto">
  <a href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
  <a href="menu.php"><i class="bi bi-journal-text"></i> Manage Menu</a>
  <a href="orders.php"><i class="bi bi-receipt"></i> Orders</a>
  
  <div class="profile-bar d-flex justify-content-between align-items-center bg-light rounded shadow-sm">
    <div class="d-flex align-items-center">
      <a href="profile.php" class="profile-link"><img src="../assets/img/user_avatar.png" alt="Profile" class="rounded-circle me-3" width="50" height="50"></a>
      <div>
        <h6 class="mb-0"><span id="profileName"><?= htmlspecialchars($_SESSION['first_name'] ?? '') ?></span></h6>
        <small class="text-muted" id="profileRole"><?= htmlspecialchars($_SESSION['role'] ?? '') ?></small>
      </div>
    </div>
    <a href="../logout.php" class="logout text-danger"><i class="bi bi-box-arrow-right"></i></a>
  </div>
</div>

<div class="content">
  <div class="topbar d-flex justify-content-between align-items-center">
    <h5>Manage Menu Items</h5>
    <i class="bi bi-bell-fill fs-4 text-light"></i>
  </div>

  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
      <thead class="table recentcard">
        <tr>
          <th>ID</th>
          <th>Image</th>
          <th>Name</th>
          <th>Category</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Availability</th>
          <th>Add-ons</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $result = $conn->query("SELECT * FROM menu ORDER BY created_at DESC");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $imgSrc = !empty($row['image']) ? "../uploads/".$row['image'] : "../assets/img/no-image.png";
                $availability = date('H:i', strtotime($row['availability_start'])) . " to " . date('H:i', strtotime($row['availability_end']));

                // Fetch add-ons for this menu item
                $addons_result = $conn->query("SELECT addon_name, addon_price FROM menu_addons WHERE item_id={$row['item_id']} ORDER BY addon_name ASC");
                $addons_list = [];
                while ($addon = $addons_result->fetch_assoc()) {
                    $addons_list[] = htmlspecialchars($addon['addon_name']) . " (₱" . number_format($addon['addon_price'], 2) . ")";
                }
                $addons_str = !empty($addons_list) ? implode(", ", $addons_list) : "—";

                echo "<tr>
                        <td>{$row['item_id']}</td>
                        <td><img src='$imgSrc' width='50'></td>
                        <td>".htmlspecialchars($row['name'])."</td>
                        <td>".htmlspecialchars($row['category'])."</td>
                        <td>₱ ".number_format($row['price'], 2)."</td>
                        <td>{$row['stock']}</td>
                        <td>{$availability}</td>
                        <td>{$addons_str}</td>
                        <td>
                          <button class='btn btn-primary btn-sm me-1' data-bs-toggle='modal' data-bs-target='#editMenuModal'
                                  onclick='editMenu(
                                    ".json_encode($row['item_id']).",
                                    ".json_encode($row['name']).",
                                    ".json_encode($row['description']).",
                                    ".json_encode($row['category']).",
                                    ".json_encode($row['price']).",
                                    ".json_encode($row['stock']).",
                                    ".json_encode($row['availability_start']).",
                                    ".json_encode($row['availability_end']).",
                                    ".json_encode($row['image'])."
                                  )'>
                            <i class='bi bi-pencil-square'></i>
                          </button>

                          <form method='POST' action='' style='display:inline-block;' onsubmit='return confirm(\"Delete this item?\")'>
                            <input type='hidden' name='delete_menu' value='".htmlspecialchars($row['item_id'])."'>
                            <button type='submit' class='btn btn-danger btn-sm'>
                              <i class='bi bi-trash'></i>
                            </button>
                          </form>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='9' class='text-center'>No menu items found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <button class="btn btn-success mt-3 mb-3 mx-auto d-block" data-bs-toggle="modal" data-bs-target="#addMenuModal">
    <i class="bi bi-plus-circle"></i> Add Menu Item
  </button>
</div>

<!-- Add Menu Modal -->
<div class="modal fade" id="addMenuModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="POST" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">Add Menu Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-3">
          <div class="col-md-6"><label>Name</label><input type="text" name="name" class="form-control" required></div>
          <div class="col-md-6"><label>Category</label>
            <select name="category" class="form-select" required>
              <option value="breakfast">Breakfast</option>
              <option value="lunch">Lunch</option>
              <option value="snacks">Snacks</option>
              <option value="beverages">Beverages</option>
            </select>
          </div>
          <div class="col-md-12"><label>Description</label><textarea name="description" class="form-control" rows="2" required></textarea></div>
          <div class="col-md-3"><label>Price</label><input type="number" step="0.01" name="price" class="form-control" required></div>
          <div class="col-md-3"><label>Stock</label><input type="number" name="stock" class="form-control" required></div>
          <div class="col-md-12">
          <label>Add-ons</label>
          <div id="addonWrapper">
            <div class="row g-2 mb-2">
              <div class="col-md-5"><input type="text" name="addon_name[]" class="form-control" placeholder="Add-on Name"></div>
              <div class="col-md-5"><input type="number" step="0.01" name="addon_price[]" class="form-control" placeholder="Price"></div>
              <div class="col-md-2"><button type="button" class="btn btn-danger btn-sm removeAddon">-</button></div>
            </div>
          </div>
          <button type="button" class="btn btn-success btn-sm mt-1" id="addAddonBtn">Add Add-on</button>
        </div>
          <div class="col-md-3"><label>Availability Start</label><input type="time" name="availability_start" class="form-control" required></div>
          <div class="col-md-3"><label>Availability End</label><input type="time" name="availability_end" class="form-control" required></div>
          <div class="col-md-12"><label>Image</label><input type="file" name="image" class="form-control" accept="image/*" required></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" name="add_menu" class="btn btn-dashboard">Add</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Menu Modal -->
<div class="modal fade" id="editMenuModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="POST" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">Edit Menu Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" name="edit_item_id" id="editItemId">
        <input type="hidden" name="current_image" id="currentImage">
        <div class="row g-3">
          <div class="col-md-12 ">
            <div class="mb-2 justify-content-center d-flex">
              <img id="editImagePreview" src="../assets/img/no-image.png" width="150">
            </div>
            <input type="file" name="edit_image" class="form-control" accept="image/*" id="editImageInput">
          </div>
          <div class="col-md-6"><label>Name</label><input type="text" name="edit_name" id="editName" class="form-control" required></div>
          <div class="col-md-6"><label>Category</label>
            <select name="edit_category" id="editCategory" class="form-select" required>
              <option value="breakfast">Breakfast</option>
              <option value="lunch">Lunch</option>
              <option value="snacks">Snacks</option>
              <option value="beverages">Beverages</option>
            </select>
          </div>
          <div class="col-md-12"><label>Description</label><textarea name="edit_description" id="editDescription" class="form-control" rows="2" required></textarea></div>
          <div class="col-md-3"><label>Price</label><input type="number" step="0.01" name="edit_price" id="editPrice" class="form-control" required></div>
          <div class="col-md-3"><label>Stock</label><input type="number" name="edit_stock" id="editStock" class="form-control" required></div>
          <div class="col-md-12">
            <label>Add-ons</label>
            <div id="editAddonWrapper"></div>
            <button type="button" class="btn btn-success btn-sm mt-1" id="editAddAddonBtn">Add Add-on</button>
          </div>
          <div class="col-md-3"><label>Availability Start</label><input type="time" name="edit_availability_start" id="editAvailabilityStart" class="form-control" required></div>
          <div class="col-md-3"><label>Availability End</label><input type="time" name="edit_availability_end" id="editAvailabilityEnd" class="form-control" required></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" name="update_menu" class="btn btn-dashboard">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
// Function to create add-on input row
function createAddonRow(wrapper, name='', price='') {
    const div = document.createElement('div');
    div.classList.add('row', 'g-2', 'mb-2');
    div.innerHTML = `
      <div class="col-md-5"><input type="text" name="${wrapper=='addonWrapper'?'addon_name[]':'edit_addon_name[]'}" class="form-control" placeholder="Add-on Name" value="${name}"></div>
      <div class="col-md-5"><input type="number" step="0.01" name="${wrapper=='addonWrapper'?'addon_price[]':'edit_addon_price[]'}" class="form-control" placeholder="Price" value="${price}"></div>
      <div class="col-md-2"><button type="button" class="btn btn-danger btn-sm removeAddon">-</button></div>
    `;
    document.getElementById(wrapper).appendChild(div);
}

// Add button
document.getElementById('addAddonBtn').addEventListener('click', ()=>createAddonRow('addonWrapper'));
document.getElementById('editAddAddonBtn').addEventListener('click', ()=>createAddonRow('editAddonWrapper'));

// Remove button
document.addEventListener('click', function(e){
    if(e.target && e.target.classList.contains('removeAddon')){
        e.target.closest('.row').remove();
    }
});

// Reusable function to handle image preview
function setupImagePreview(inputId, previewId, defaultSrc) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);

    input.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result; // show selected image
            }
            reader.readAsDataURL(file);
        } else {
            preview.src = defaultSrc; // reset if no file
        }
    });
}

// Initialize for Add and Edit modals
setupImagePreview('addImageInput', 'addImagePreview', '../assets/img/no-image.png');
setupImagePreview('editImageInput', 'editImagePreview', '../assets/img/no-image.png');

// Update edit preview when opening Edit Modal
function editMenu(id, name, description, category, price, stock, availability_start, availability_end, image) {
    document.getElementById('editItemId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editDescription').value = description;
    document.getElementById('editCategory').value = category;
    document.getElementById('editPrice').value = price;
    document.getElementById('editStock').value = stock;
    document.getElementById('currentImage').value = image;

    document.getElementById('editAvailabilityStart').value = availability_start.slice(11,16);
    document.getElementById('editAvailabilityEnd').value = availability_end.slice(11,16);

    // Show current image
    document.getElementById('editImagePreview').src = image ? "../uploads/" + image : "../assets/img/no-image.png";

    // Clear & load add-ons
    const wrapper = document.getElementById('editAddonWrapper');
    wrapper.innerHTML = '';
    fetch(`fetch_addons.php?item_id=${id}`)
        .then(response => response.json())
        .then(data => {
            if(data.length === 0) {
                createAddonRow('editAddonWrapper');
            } else {
                data.forEach(addon => createAddonRow('editAddonWrapper', addon.addon_name, addon.addon_price));
            }
        })
        .catch(() => createAddonRow('editAddonWrapper'));
}
</script> 


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>