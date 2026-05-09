<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../includes/db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$successMessage = null;

// ✅ Handle Add Service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $serviceName = $_POST['service_name'] ?? null;
    $price       = $_POST['price'] ?? null;
    $status      = $_POST['status'] ?? null;
    $categoryId  = $_POST['category_id'] ?? null;

    if ($categoryId && $serviceName && $price && $status) {
        $stmt = $conn->prepare("INSERT INTO services (service_name, price, status, category_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$serviceName, $price, $status, $categoryId]);
        $successMessage = "New service added successfully!";
    } else {
        $successMessage = "Please fill in all required fields before adding a service.";
    }
}

// ✅ Handle Update Service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_service'])) {
    $serviceId   = $_POST['id'] ?? null;
    $serviceName = $_POST['service_name'] ?? null;
    $price       = $_POST['price'] ?? null;
    $status      = $_POST['status'] ?? null;
    $categoryId  = $_POST['category_id'] ?? null;

    if ($serviceId && $categoryId && $serviceName && $price && $status) {
        $stmt = $conn->prepare("UPDATE services SET service_name=?, price=?, status=?, category_id=? WHERE id=?");
        $stmt->execute([$serviceName, $price, $status, $categoryId, $serviceId]);
        $successMessage = "Service #{$serviceId} updated successfully!";
    } else {
        $successMessage = "Please fill in all required fields before updating.";
    }
}

// ✅ Handle Delete Service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_service'])) {
    $serviceId = $_POST['id'] ?? null;
    if ($serviceId) {
        $stmt = $conn->prepare("DELETE FROM services WHERE id=?");
        $stmt->execute([$serviceId]);
        $successMessage = "Service #{$serviceId} deleted successfully!";
    }
}

// ✅ Fetch categories for dropdown
$catStmt = $conn->query("SELECT id, category_name FROM categories ORDER BY category_name ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Fetch services ordered by ID
$stmt = $conn->query("SELECT s.*, c.category_name 
                      FROM services s 
                      LEFT JOIN categories c ON s.category_id = c.id 
                      ORDER BY s.id ASC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Services</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <style>
    body { background: linear-gradient(135deg, #f9f9ff, #fceef5); min-height: 100vh; }
    .dashboard-card { border-radius: 12px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 20px; margin-bottom:20px; }
    .table thead { background: linear-gradient(135deg, #6c63ff, #836fff); color: #fff; }
    .badge { font-size: 0.85rem; }
    .form-control, .form-select { box-shadow: none !important; }
  </style>
</head>
<body class="dash-body">
  
<div class="container mt-4">
  <h3 class="section-title mb-4"><i class="fa fa-scissors me-2"></i> Services Management</h3>

  <!-- ✅ Success Alert -->
  <?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fa fa-check-circle me-2"></i> <?= htmlspecialchars($successMessage) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- ✅ Add New Service -->
  <div class="dashboard-card mb-4">
    <form method="post">
      <div class="row g-2">
        <div class="col-md-3"><input type="text" name="service_name" class="form-control" placeholder="Service Name" required></div>
        <div class="col-md-2"><input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required></div>
        <div class="col-md-2">
          <select name="status" class="form-select">
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
        <div class="col-md-3">
          <select name="category_id" class="form-select" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2"><button type="submit" name="add_service" class="btn btn-primary w-100">Add Service</button></div>
      </div>
    </form>
  </div>

  <!-- ✅ Search + Filter Bar -->
  <div class="row mb-3">
    <div class="col-md-4"><input type="text" id="searchService" class="form-control shadow-sm" placeholder="Search by service name..."></div>
    <div class="col-md-3">
      <select id="serviceStatusFilter" class="form-select shadow-sm">
        <option value="">Filter by status</option>
        <option value="Active">Active</option>
        <option value="Inactive">Inactive</option>
      </select>
    </div>
    <div class="col-md-2"><button type="button" id="resetFilters" class="btn btn-outline-secondary w-100">Reset Filters</button></div>
  </div>

  <!-- ✅ Services Table -->
  <div class="dashboard-card">
    <table class="table table-hover align-middle" id="servicesTable">
      <thead>
        <tr><th>ID</th><th>Name</th><th>Price</th><th>Status</th><th>Category</th><th class="text-center">Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($services as $s): ?>
          <tr>
            <form method="post">
              <input type="hidden" name="id" value="<?= $s['id'] ?>">
              <td><?= $s['id'] ?></td>
              <td data-label="Name"><input type="text" name="service_name" value="<?= htmlspecialchars($s['service_name']) ?>" class="form-control"></td>
              <td data-label="Price"><input type="number" step="0.01" name="price" value="<?= $s['price'] ?>" class="form-control"></td>
              <td data-label="Status">
                <span class="badge <?= $s['status']==='Active' ? 'bg-success' : 'bg-secondary' ?>"><?= htmlspecialchars($s['status']) ?></span>
                <select name="status" class="form-select mt-2 status-dropdown">
                  <option value="Active" <?= $s['status']==='Active'?'selected':'' ?>>Active</option>
                  <option value="Inactive" <?= $s['status']==='Inactive'?'selected':'' ?>>Inactive</option>
                </select>
              </td>
              <td>
                <select name="category_id" class="form-select" required>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $s['category_id']==$cat['id']?'selected':'' ?>>
                      <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td class="text-center">
                <div class="d-flex justify-content-center gap-2">
                  <button type="submit" name="update_service" class="btn btn-sm btn-light px-2 border"><i class="fa fa-pencil-alt" style="color:#0d6efd;"></i></button>
                  <button type="button" class="btn btn-sm btn-light px-2 border"
                          data-bs-toggle="modal"
                          data-bs-target="#deleteModal"
                                                    data-id="<?= $s['id'] ?>"
                          data-name="<?= htmlspecialchars($s['service_name']) ?>">
                    <i class="fa fa-trash" style="color:#dc3545;"></i>
                  </button>
                </div>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ✅ Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="fa fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p id="deleteText">Are you sure you want to delete this service?</p>
          <input type="hidden" name="id" id="deleteId">
          <input type="hidden" name="delete_service" value="1">
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

<!-- ✅ Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // ✅ Delete Modal Logic
  const deleteModal = document.getElementById('deleteModal');
  deleteModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');

    document.getElementById('deleteId').value = id;
    document.getElementById('deleteText').textContent =
      "Are you sure you want to delete service \"" + name + "\" (ID #" + id + ")?";
  });

  // ✅ Search + Filter Logic
  const searchService = document.getElementById('searchService');
  const statusFilter = document.getElementById('serviceStatusFilter');
  const resetFilters = document.getElementById('resetFilters');
  const rows = document.querySelectorAll('#servicesTable tbody tr');

  function filterServices() {
    const searchValue = searchService.value.toLowerCase();
    const statusValue = statusFilter.value.toLowerCase();

    rows.forEach(row => {
      const nameInput = row.querySelector('td[data-label="Name"] input');
      const nameText = nameInput ? nameInput.value.toLowerCase() : "";
      const statusDropdown = row.querySelector('.status-dropdown');
      const currentStatus = statusDropdown ? statusDropdown.value.toLowerCase() : "";

      const matchesSearch = nameText.includes(searchValue);
      const matchesStatus = !statusValue || currentStatus === statusValue;

      row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    });
  }

  searchService.addEventListener('keyup', filterServices);
  statusFilter.addEventListener('change', filterServices);

  // ✅ Reset Filters
  resetFilters.addEventListener('click', () => {
    searchService.value = '';
    statusFilter.value = '';
    filterServices();
  });

  // ✅ Update badge + re-filter when status dropdown changes
  document.querySelectorAll('.status-dropdown').forEach(dropdown => {
    dropdown.addEventListener('change', function() {
      const badge = this.closest('td').querySelector('.badge');
      if (this.value === 'Active') {
        badge.textContent = 'Active';
        badge.className = 'badge bg-success';
      } else {
        badge.textContent = 'Inactive';
        badge.className = 'badge bg-secondary';
      }
      filterServices();
    });
  });

  // ✅ Initial filter run
  filterServices();
</script>
</body>
</html>
