<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../../includes/db.php";

// ✅ Fetch admin info
$admin_id   = $_SESSION['admin_id'] ?? null;
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// ✅ Count unread notifications
$unread = 0;
if ($admin_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM notifications 
        WHERE receiver_role = 'admin' 
          AND receiver_id = :aid 
          AND is_read = 0
    ");
    $stmt->execute([':aid' => $admin_id]);
    $unread = $stmt->fetchColumn();
}
?>

<div class="topbar d-flex justify-content-between align-items-center p-2 shadow-sm bg-white">
  <h5 class="mb-0 text-dark fw-semibold">
    Welcome <span class="fw-bold text-purple"><?= htmlspecialchars($admin_name) ?></span> 
    to Merz Beauty Glow Salon and Spa (Admin)
  </h5>

  <div class="d-flex align-items-center gap-3">
    <!-- Notifications Bell -->
    <a href="admin_dashboard.php?page=notification" 
       class="text-decoration-none position-relative" 
       data-bs-toggle="tooltip" title="Notifications">
      <i class="fa fa-bell fa-lg text-dark"></i>
      <?php if ($unread > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          <?= $unread ?>
        </span>
      <?php endif; ?>
    </a>

    <!-- Profile Icon -->
    <a href="admin_dashboard.php?page=profile" class="btn btn-link" data-bs-toggle="tooltip" title="Profile">
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
