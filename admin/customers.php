<?php
// Ensure DB connection is available
require_once __DIR__ . "/../includes/db.php";

// Fetch all customers ordered by ID for consistency
$stmt = $conn->query("SELECT id, full_name, email, created_at FROM customers ORDER BY id ASC");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3 class="section-title mb-4"><i class="fa fa-users me-2"></i> Customer Management</h3>

<!-- ✅ Search + Reset Bar -->
<div class="row mb-3">
  <div class="col-md-6">
    <input type="text" id="searchCustomer" class="form-control" placeholder="Search by name or email...">
  </div>
  <div class="col-md-2">
    <button type="button" id="resetCustomerFilters" class="btn btn-secondary w-100">Reset</button>
  </div>
</div>

<div class="dashboard-card p-3">
 <table class="table table-bordered table-hover align-middle" id="customersTable">
  <thead class="gradient-header text-white">
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Email</th>
      <th>Joined</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
      <?php if ($customers): ?>
        <?php foreach ($customers as $c): ?>
          <tr>
            <td><?= $c['id'] ?></td>
            <td data-label="Name"><?= htmlspecialchars($c['full_name']) ?></td>
            <td data-label="Email"><?= htmlspecialchars($c['email']) ?></td>
            <td data-label="Joined">
              <span class="badge bg-info text-dark">
                <?= date("M d, Y", strtotime($c['created_at'])) ?>
              </span>
            </td>
            <td>
              <!-- ✅ Delete Button -->
              <button type="button" class="btn btn-sm btn-light px-2 border"
                      data-bs-toggle="modal"
                      data-bs-target="#deleteCustomerModal"
                      data-id="<?= $c['id'] ?>"
                      data-name="<?= htmlspecialchars($c['full_name']) ?>"
                      title="Delete Customer">
                <i class="fa fa-trash" style="color:#dc3545;"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5" class="text-center text-muted">No customers found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- ✅ Delete Confirmation Modal -->
<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="delete_customer.php">
        <div class="modal-header text-white" style="background: linear-gradient(135deg, #dc3545, #ff6b6b);">
          <h5 class="modal-title"><i class="fa fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p id="deleteCustomerText">Are you sure you want to delete this customer?</p>
          <input type="hidden" name="id" id="deleteCustomerId">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="fa fa-trash me-1"></i> Yes, Delete
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ✅ Bootstrap JS (required for modal) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // ✅ Search + Reset Logic
  const searchCustomer = document.getElementById('searchCustomer');
  const resetCustomerFilters = document.getElementById('resetCustomerFilters');
  const rows = document.querySelectorAll('#customersTable tbody tr');

  function filterCustomers() {
    const searchValue = searchCustomer.value.toLowerCase();
    rows.forEach(row => {
      const nameText = row.querySelector('td[data-label="Name"]').textContent.toLowerCase();
      const emailText = row.querySelector('td[data-label="Email"]').textContent.toLowerCase();
      const combinedText = nameText + " " + emailText;
      row.style.display = combinedText.includes(searchValue) ? '' : 'none';
    });
  }

  searchCustomer.addEventListener('keyup', filterCustomers);
  resetCustomerFilters.addEventListener('click', () => {
    searchCustomer.value = '';
    filterCustomers();
  });

  // ✅ Delete Modal Logic
  const deleteCustomerModal = document.getElementById('deleteCustomerModal');
  deleteCustomerModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');

    document.getElementById('deleteCustomerId').value = id;
    document.getElementById('deleteCustomerText').textContent =
      "Are you sure you want to delete customer \"" + name + "\" (ID #" + id + ")?";
  });

  // ✅ Initial run
  filterCustomers();
</script>
