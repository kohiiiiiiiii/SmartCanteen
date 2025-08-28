<?php
include 'db_connect.php';
$meals = $conn->query("SELECT * FROM meals");

while ($meal = $meals->fetch_assoc()) {
  $addons = $conn->query("SELECT name FROM addons WHERE meal_id = " . $meal['id']);
?>
  <!-- Meal Card -->
  <div class="col-md-4 mb-4">
    <div class="card h-100">
      <img src="assets/img/<?php echo strtolower($meal['name']); ?>.png" class="card-img-top" alt="<?php echo $meal['name']; ?>">
      <div class="card-body text-center d-flex flex-column h-100">
        <h5 class="card-title"><?php echo $meal['name']; ?></h5>
        <p class="card-text"><?php echo $meal['description']; ?></p>
        <ul class="list-group list-group-flush mb-3 fixed-list">
          <li class="list-group-item">🍽️ <strong>Price:</strong> ₱<?php echo $meal['price']; ?></li>
          <li class="list-group-item">⏰ <strong>Available:</strong> <?php echo date("g:i A", strtotime($meal['available_from'])) . " – " . date("g:i A", strtotime($meal['available_to'])); ?></li>
          <li class="list-group-item">✨ <strong>Add-ons:</strong>
            <?php while ($addon = $addons->fetch_assoc()) echo $addon['name'] . ", "; ?>
          </li>
        </ul>
        <button class="btn btn-warning mt-auto" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $meal['id']; ?>">Order</button>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="orderModal<?php echo $meal['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content rounded-4">
        <form action="place_order.php" method="POST">
          <div class="modal-header bg-warning text-white">
            <h5 class="modal-title">Order: <?php echo $meal['name']; ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="meal_id" value="<?php echo $meal['id']; ?>">
            <p><?php echo $meal['description']; ?></p>
            <div class="mb-3">
              <label class="form-label">✨ Add-ons</label>
              <?php
              $addons->data_seek(0); // Reset pointer
              while ($addon = $addons->fetch_assoc()) {
              ?>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="addons[]" value="<?php echo $addon['name']; ?>">
                  <label class="form-check-label"><?php echo $addon['name']; ?></label>
                </div>
              <?php } ?>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-warning">Confirm Order</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php } ?>
