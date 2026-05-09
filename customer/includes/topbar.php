<?php
// Ensure session and DB connection are available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../../includes/db.php";

// Fetch customer info if not already set
if (!isset($customer_id) || !isset($customer_name)) {
    if (isset($_SESSION['customer'])) {
        $email = $_SESSION['customer'];
        $stmt = $conn->prepare("SELECT id, full_name FROM customers WHERE email=?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        $customer_id   = $customer['id'] ?? null;
        $customer_name = $customer['full_name'] ?? 'Customer';
    }
}

// ✅ Count unread notifications (normalized schema)
$unread = 0;
if ($customer_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM notifications 
        WHERE receiver_role = 'customer' 
          AND receiver_id = :cid 
          AND is_read = 0
    ");
    $stmt->execute([':cid' => $customer_id]);
    $unread = $stmt->fetchColumn();
}
?>

<div class="topbar d-flex justify-content-between align-items-center p-2 shadow-sm bg-white">
  <!-- Left-aligned Welcome Banner -->
  <h5 class="mb-0 text-dark fw-semibold">
    Welcome <span class="fw-bold text-purple"><?= htmlspecialchars($customer_name) ?></span> 
    to Merz Beauty Glow Salon and Spa
  </h5>

  <!-- Right-side icons -->
  <div class="d-flex align-items-center gap-3">

    <!-- Notifications Bell (direct link) -->
    <a href="customer_dashboard.php?page=notifications" class="text-decoration-none position-relative" data-bs-toggle="tooltip" title="Notifications">
      <i class="fa fa-bell fa-lg text-dark"></i>
      <?php if ($unread > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          <?= $unread ?>
        </span>
      <?php endif; ?>
    </a>

    <!-- Profile Icon -->
    <a href="profile.php" class="btn btn-link" data-bs-toggle="tooltip" title="Profile">
      <i class="fa fa-user text-dark"></i>
    </a>
    
  </div>
</div>

<script>
  // Enable Bootstrap tooltips
  document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
      new bootstrap.Tooltip(tooltipTriggerEl)
    })
  });
</script>
