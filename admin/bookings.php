<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/notification_helper.php"; 

// ✅ Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$successMessage = null;

// ✅ Handle booking actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'] ?? null;
    $action    = $_POST['action'] ?? null;

    if ($bookingId && $action) {
        if ($action === 'cancel') {
            $stmt = $conn->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ? AND status='Pending'");
            $stmt->execute([$bookingId]);

            // ✅ Fetch customer info
            $stmt = $conn->prepare("SELECT customer_id FROM bookings WHERE id=?");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($booking) {
                addNotification(
                    $conn,
                    $_SESSION['admin_id'], 'admin',
                    $booking['customer_id'], 'customer',
                    'booking_cancelled',
                    "Your booking #{$bookingId} has been cancelled by admin."
                );
            }

            $successMessage = "Booking #{$bookingId} has been cancelled.";
        } elseif ($action === 'complete') {
            $stmt = $conn->prepare("UPDATE bookings SET status = 'Completed' WHERE id = ? AND status='Pending'");
            $stmt->execute([$bookingId]);

            $stmt = $conn->prepare("
                SELECT b.customer_id, s.price 
                FROM bookings b
                LEFT JOIN services s ON s.id = b.service_id
                WHERE b.id=?
            ");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($booking) {
                $stmt = $conn->prepare("INSERT INTO finances 
                    (booking_id, customer_id, amount, payment_method, status, type, created_at)
                    VALUES (?, ?, ?, ?, 'Paid', 'Revenue', NOW())");
                $stmt->execute([
                    $bookingId,
                    $booking['customer_id'],
                    $booking['price'],
                    'Cash'
                ]);

                // ✅ Notify customer
                addNotification(
                    $conn,
                    $_SESSION['admin_id'], 'admin',
                    $booking['customer_id'], 'customer',
                    'booking_completed',
                    "Your booking #{$bookingId} has been marked complete."
                );
            }

            $successMessage = "Booking #{$bookingId} marked as completed and payment recorded.";
        }
    }
    header("Location: admin_dashboard.php?page=bookings&success=" . urlencode($successMessage));
    exit();
}

// ✅ Fetch bookings
$stmt = $conn->query("
    SELECT b.id, c.full_name, s.service_name, s.price, b.booking_date, b.booking_time, b.status
    FROM bookings b
    LEFT JOIN customers c ON c.id = b.customer_id
    LEFT JOIN services s ON s.id = b.service_id
    ORDER BY b.booking_date DESC, b.booking_time DESC
");
$bookings = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

// ✅ Summary counts
$summaryStmt = $conn->query("SELECT status, COUNT(*) as count FROM bookings GROUP BY status");
$summaryData = $summaryStmt->fetchAll(PDO::FETCH_ASSOC);
$summary = ['Pending'=>0,'Completed'=>0,'Cancelled'=>0];
foreach ($summaryData as $row) {
    $summary[$row['status']] = $row['count'];
}

// ✅ Revenue totals
$revenueStmt = $conn->query("
    SELECT b.status, SUM(s.price) AS total
    FROM bookings b
    LEFT JOIN services s ON s.id = b.service_id
    GROUP BY b.status
");
$revenueData = $revenueStmt->fetchAll(PDO::FETCH_ASSOC);
$revenue = ['Pending'=>0,'Completed'=>0,'Cancelled'=>0];
foreach ($revenueData as $row) {
    $revenue[$row['status']] = $row['total'];
}

// ✅ Daily totals per customer
$stmt = $conn->query("
    SELECT c.full_name, DATE(b.booking_date) AS booking_day, SUM(s.price) AS total_spent
    FROM bookings b
    LEFT JOIN customers c ON c.id = b.customer_id
    LEFT JOIN services s ON s.id = b.service_id
    WHERE b.status = 'Completed'
    GROUP BY c.id, booking_day
    ORDER BY booking_day DESC, c.full_name ASC
");
$totals = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

// ✅ Success message
if (isset($_GET['success'])) {
    $successMessage = $_GET['success'];
}
?>

<body class="bg-light">
  <h3 class="section-title mb-4"><i class="fa fa-calendar me-2"></i> Booking Management</h3>
<div class="container-fluid py-4">

  <!-- Bookings Management Card -->
  <div class="card shadow-sm border-0">
    <div class="card-body">

      <!-- Filters -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <input type="text" id="searchInput" class="form-control w-50" placeholder="Search bookings...">
        <select id="statusFilter" class="form-select w-auto ms-2">
          <option value="">All Status</option>
          <option value="Pending">Pending</option>
          <option value="Completed">Completed</option>
          <option value="Cancelled">Cancelled</option>
        </select>
      </div>

      <!-- Bookings Table -->
      <div class="table-responsive">
        <table id="bookingsTable" class="table table-hover table-striped align-middle rounded">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Customer</th>
              <th>Service</th>
              <th>Price</th>
              <th>Date</th>
              <th>Time</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bookings as $b): ?>
              <tr>
                <td><?= $b['id'] ?></td>
                <td><?= htmlspecialchars($b['full_name']) ?></td>
                <td><?= htmlspecialchars($b['service_name']) ?></td>
                <td>₱<?= number_format($b['price'], 2) ?></td>
                <td><?= date("M d, Y", strtotime($b['booking_date'])) ?></td>
                <td><?= date("h:i A", strtotime($b['booking_time'])) ?></td>
                <td><span class="badge bg-<?= $b['status']=='Completed'?'success':($b['status']=='Cancelled'?'danger':'warning') ?>">
                  <?= $b['status'] ?></span></td>
                <td>
  <?php if ($b['status'] === 'Pending'): ?>
    <div class="d-flex flex-row flex-wrap gap-2 justify-content-center">
      <button class="btn btn-sm btn-success"
              data-bs-toggle="modal"
              data-bs-target="#confirmModal"
              data-id="<?= $b['id'] ?>"
              data-action="complete">
        <i class="fa fa-check"></i>
      </button>
      <button class="btn btn-sm btn-danger"
              data-bs-toggle="modal"
              data-bs-target="#confirmModal"
              data-id="<?= $b['id'] ?>"
              data-action="cancel">
        <i class="fa fa-times"></i>
      </button>
    </div>
  <?php else: ?>
    <span class="text-muted">—</span>
  <?php endif; ?>
</td>

              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <nav>
        <ul id="pagination" class="pagination justify-content-center"></ul>
      </nav>

      <!-- Customer Daily Totals -->
      <h4 class="fw-bold mt-4">Customer Daily Totals</h4>
      <table class="table table-striped align-middle">
        <thead class="table-secondary">
          <tr>
            <th>Customer</th>
            <th>Date</th>
            <th>Total Spent</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($totals as $t): ?>
            <tr>
              <td><?= htmlspecialchars($t['full_name']) ?></td>
              <td><?= !empty($t['booking_day']) ? date("M d, Y", strtotime($t['booking_day'])) : '-' ?></td>
              <td>₱<?= number_format($t['total_spent'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    </div>
  </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <form method="post" action="admin_dashboard.php?page=bookings">
        <div class="modal-header bg-dark text-white rounded-top">
          <h5 class="modal-title">Confirm Action</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p id="confirmText" class="fw-semibold">Are you sure?</p>
          <input type="hidden" name="booking_id" id="bookingId">
          <input type="hidden" name="action" id="actionField">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
          <button type="submit" class="btn btn-primary">Yes, proceed</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Scripts -->
<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // ✅ Confirmation Modal Logic
  const confirmModal = document.getElementById('confirmModal');
  confirmModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const bookingId = button.getAttribute('data-id');
    const action = button.getAttribute('data-action');

    document.getElementById('bookingId').value = bookingId;
    document.getElementById('actionField').value = action;

    if (action === 'cancel') {
      document.getElementById('confirmText').textContent = "Are you sure you want to cancel this booking?";
    } else if (action === 'complete') {
      document.getElementById('confirmText').textContent = "Mark this booking as completed?";
    }
  });

  // ✅ Search + Filter + Pagination
  const searchInput = document.getElementById('searchInput');
  const statusFilter = document.getElementById('statusFilter');
  const allRows = Array.from(document.querySelectorAll('#bookingsTable tbody tr'));
  const rowsPerPage = 10;
  let currentPage = 1;
  let filteredRows = allRows;

  function applyFilter() {
    const searchValue = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value.toLowerCase();

    filteredRows = allRows.filter(row => {
      const text = row.textContent.toLowerCase();
      const statusText = row.querySelector('td:nth-child(7)').textContent.toLowerCase();
      const matchesSearch = text.includes(searchValue);
      const matchesStatus = !statusValue || statusText.includes(statusValue);
      return matchesSearch && matchesStatus;
    });

    currentPage = 1;
    paginate();
  }

  function paginate() {
    allRows.forEach(row => row.style.display = 'none');

    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;

    filteredRows.slice(start, end).forEach(row => row.style.display = '');

    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';
    for (let i = 1; i <= totalPages; i++) {
      const li = document.createElement('li');
      li.className = 'page-item' + (i === currentPage ? ' active' : '');
      li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
      li.addEventListener('click', function(e) {
        e.preventDefault();
        currentPage = i;
        paginate();
      });
      pagination.appendChild(li);
    }
  }

  searchInput.addEventListener('keyup', applyFilter);
  statusFilter.addEventListener('change', applyFilter);

  applyFilter();
</script>
</body>
</html>
