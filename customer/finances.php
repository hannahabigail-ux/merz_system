<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

if (!isset($_SESSION['customer'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['customer'];
$stmt = $conn->prepare("SELECT id, full_name FROM customers WHERE email=?");
$stmt->execute([$email]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);
$customer_id = $customer['id'] ?? null;

// ✅ Totals
$totalPaidStmt = $conn->prepare("SELECT SUM(s.price)
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    WHERE b.customer_id=? AND (b.status='Completed' OR b.status='Paid')");
$totalPaidStmt->execute([$customer_id]);
$totalPaid = $totalPaidStmt->fetchColumn() ?? 0;

$totalPendingStmt = $conn->prepare("SELECT SUM(s.price)
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    WHERE b.customer_id=? AND b.status='Pending'");
$totalPendingStmt->execute([$customer_id]);
$totalPending = $totalPendingStmt->fetchColumn() ?? 0;

// ✅ Records
// Completed/Paid
$stmt = $conn->prepare("SELECT s.service_name, s.price, b.status, b.booking_date
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    WHERE b.customer_id=? AND (b.status='Completed' OR b.status='Paid')
    ORDER BY b.booking_date DESC");
$stmt->execute([$customer_id]);
$myPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pending
$stmt = $conn->prepare("SELECT s.service_name, s.price, b.status, b.booking_date
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    WHERE b.customer_id=? AND b.status='Pending'
    ORDER BY b.booking_date DESC");
$stmt->execute([$customer_id]);
$myPending = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Payments</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css?v=23">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="dash-body">
<div class="admin-container">
  <?php include("includes/sidebar.php"); ?>
  <div class="main-content">
    <?php include("includes/topbar.php"); ?>

    <div class="content mt-3">
      <h3 class="section-title mb-4"><i class="fa fa-wallet me-2"></i> My Payments</h3>

      <!-- ✅ Summary Cards -->
      <div class="row g-4 mb-4">
        <div class="col-md-6">
          <div class="dashboard-card p-4 text-center">
            <h6><i class="fa fa-check-circle me-2 text-success"></i>Total Paid</h6>
            <p class="display-6 text-success"><strong>₱<?= number_format($totalPaid, 2) ?></strong></p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="dashboard-card p-4 text-center">
            <h6><i class="fa fa-hourglass-half me-2 text-warning"></i>Pending Payments</h6>
            <p class="display-6 text-warning"><strong>₱<?= number_format($totalPending, 2) ?></strong></p>
          </div>
        </div>
      </div>

      <!-- ✅ Completed/Paid Table -->
      <div class="dashboard-card p-4 mb-4">
        <h4 class="mb-3"><i class="fa fa-list me-2"></i> Completed / Paid Services</h4>
        <table class="table table-hover align-middle">
          <thead class="gradient-header text-white">
            <tr>
              <th>Service</th><th>Price</th><th>Status</th><th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($myPayments): ?>
              <?php foreach ($myPayments as $p): ?>
                <tr>
                  <td><?= htmlspecialchars($p['service_name']) ?></td>
                  <td><strong>₱<?= number_format($p['price'], 2) ?></strong></td>
                  <td><span class="badge bg-success"><?= htmlspecialchars($p['status']) ?></span></td>
                  <td><?= date("M d, Y", strtotime($p['booking_date'])) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="4" class="text-center text-muted">No completed payments found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- ✅ Pending Table -->
      <div class="dashboard-card p-4">
        <h4 class="mb-3"><i class="fa fa-hourglass-half me-2"></i> Pending Services</h4>
        <table class="table table-hover align-middle">
          <thead class="gradient-header text-white">
            <tr>
              <th>Service</th><th>Price</th><th>Status</th><th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($myPending): ?>
              <?php foreach ($myPending as $p): ?>
                <tr>
                  <td><?= htmlspecialchars($p['service_name']) ?></td>
                  <td><strong>₱<?= number_format($p['price'], 2) ?></strong></td>
                  <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($p['status']) ?></span></td>
                  <td><?= date("M d, Y", strtotime($p['booking_date'])) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="4" class="text-center text-muted">No pending services found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
