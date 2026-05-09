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

$customer_id   = $customer['id'] ?? null;
$customer_name = $customer['full_name'] ?? 'Customer';

// ✅ Initialize counts
$myBookings = 0;
$myUpcoming = 0;
$totalPaid  = 0;
$totalPending = 0;

if ($customer_id) {
    // My Bookings = Completed + Pending
    $myBookingsStmt = $conn->prepare("SELECT COUNT(*) 
        FROM bookings 
        WHERE customer_id=? AND (status='Completed' OR status='Pending')");
    $myBookingsStmt->execute([$customer_id]);
    $myBookings = $myBookingsStmt->fetchColumn() ?? 0;

    // Upcoming = Pending only
    $myUpcomingStmt = $conn->prepare("SELECT COUNT(*) 
        FROM bookings 
        WHERE customer_id=? AND status='Pending'");
    $myUpcomingStmt->execute([$customer_id]);
    $myUpcoming = $myUpcomingStmt->fetchColumn() ?? 0;

    // Finance totals
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
}

// ✅ Page routing
$page = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customer Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css?v=22">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="dash-body">
<div class="admin-container">
  <?php include("includes/sidebar.php"); ?>
  <div class="main-content">
    <?php include("includes/topbar.php"); ?>
    <div class="content mt-3">
      <?php
      switch ($page) {
          case 'home':
              // ✅ Show your dashboard cards only when on home
              ?>
              <h3 class="section-title mb-4"><i class="fa fa-home me-2"></i> My Dashboard</h3>
              <!-- Personalized Stats -->
              <div class="row g-4 mb-4">
                <div class="col-md-6">
                  <div class="dashboard-card p-4 text-center">
                    <h5><i class="fa fa-clipboard-list me-2 text-purple"></i>My Bookings</h5>
                    <p class="display-6 text-purple"><strong><?= $myBookings ?></strong></p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="dashboard-card p-4 text-center">
                    <h5><i class="fa fa-calendar-check me-2 text-purple"></i>My Upcoming Appointments</h5>
                    <p class="display-6 text-purple"><strong><?= $myUpcoming ?></strong></p>
                  </div>
                </div>
              </div>

              <!-- Finance Preview Cards -->
              <div class="row g-4 mb-4">
                <div class="col-md-6">
                  <div class="dashboard-card p-4 text-center">
                    <h6><i class="fa fa-check-circle me-2 text-success"></i>Total Paid</h6>
                    <p class="display-6 text-success"><strong>₱<?= number_format($totalPaid, 2) ?></strong></p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="dashboard-card p-4 text-center">
                    <h6><i class="fa fa-hourglass-half me-2 text-warning"></i>Pending</h6>
                    <p class="display-6 text-warning"><strong>₱<?= number_format($totalPending, 2) ?></strong></p>
                  </div>
                </div>
              </div>

              
<!-- Social Links -->
<div class="row g-4">
  <div class="col-md-12">
    <a href="https://www.facebook.com/merzbeautyglowsalonandspa" 
       target="_blank" 
       class="text-decoration-none">
      <div class="dashboard-card text-center" style="padding:1.5rem;">
        <h4 class="fw-bold text-accent mb-2">
          <i class="fa fa-compass me-2 text-purple"></i>Connect With Us
        </h4>
        <p class="mb-2">Stay connected with Merz Beauty Glow Salon & Spa through our social platforms.</p>
        <p class="fw-semibold text-white text-decoration-underline mb-0">
          Merz Beauty Glow Salon & Spa
        </p>
      </div>
    </a>
  </div>
</div>



              <?php
              break;

          case 'profile':
              include __DIR__ . "/profile.php";
              break;
          case 'bookings':
              include __DIR__ . "/bookings.php";
              break;
          case 'services':
              include __DIR__ . "/services.php";
              break;
          case 'notifications':
              include __DIR__ . "/notifications.php"; // ✅ only notifications content
              break;
          default:
              // fallback to home
              ?>
              <h3 class="section-title mb-4"><i class="fa fa-home me-2"></i> My Dashboard</h3>
              <?php
              break;
      }
      ?>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
