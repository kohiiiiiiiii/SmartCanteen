<!-- Add Order Modal -->
<div class="modal fade" id="addOrderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="../includes/order_functions.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>User ID</label>
          <input type="number" name="user_id" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Pickup Option</label>
          <select name="pickup_option" class="form-select">
            <option value="dine-in">Dine-in</option>
            <option value="take-out">Take-out</option>
          </select>
        </div>
        <div class="mb-3">
          <label>Scheduled Time</label>
          <input type="time" name="scheduled_time" class="form-control">
        </div>
        <div class="mb-3">
          <label>Total Amount</label>
          <input type="number" step="0.01" name="total_amount" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Cancel</button>
      <button type="submit" name="add_order" class="btn btn-success">Add</button> 
      </div>
    </form>
  </div>
</div>

<!-- Edit Order Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="../includes/order_functions.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edit_order_id" name="order_id">
        <div class="mb-3">
          <label>User ID</label>
          <input type="number" id="edit_user_id" name="user_id" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Pickup Option</label>
          <select id="edit_pickup_option" name="pickup_option" class="form-select">
            <option value="dine-in">Dine-in</option>
            <option value="take-out">Take-out</option>
          </select>
        </div>
        <div class="mb-3">
          <label>Scheduled Time</label>
          <input type="time" id="edit_scheduled_time" name="scheduled_time" class="form-control">
        </div>
        <div class="mb-3">
          <label>Total Amount</label>
          <input type="number" step="0.01" id="edit_total_amount" name="total_amount" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Status</label>
          <select id="edit_status" name="status" class="form-select">
            <option value="Pending">Pending</option>
            <option value="Preparing">Preparing</option>
            <option value="Ready">Ready</option>
            <option value="Completed">Completed</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" name="update_order" class="btn btn-success">Save Changes</button>
      </div>
    </form>
  </div>
</div>
