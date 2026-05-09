<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$page = $_GET['page'] ?? 'home';

if ($page === 'home') {
    // Dashboard counts
    $customers = $conn->query("SELECT COUNT(*) AS total FROM customers")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $bookings  = $conn->query("SELECT COUNT(*) AS total FROM bookings")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $services  = $conn->query("SELECT COUNT(*) AS total FROM services")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $feedback  = $conn->query("SELECT COUNT(*) AS total FROM feedback")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $revenue   = $conn->query("SELECT SUM(amount) AS total FROM finances")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Recent bookings
    $recentStmt = $conn->query("
        SELECT b.id, c.full_name, b.booking_date, b.booking_time, b.status
        FROM bookings b
        LEFT JOIN customers c ON c.id = b.customer_id
        ORDER BY b.created_at DESC
        LIMIT 5
    ");
    $recentBookings = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #f9f9ff, #fceef5);
      min-height: 100vh;
    }
    .dashboard-card {
      border-radius: 12px;
      background: linear-gradient(135deg, #fdfbfb, #ebedee);
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .dashboard-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    }
    .dashboard-card h3 {
      margin: 0;
      font-weight: bold;
      color: #333;
    }
    .dashboard-card h6 {
      color: #6c757d;
      font-weight: 500;
    }
    .dashboard-card i {
      color: #6c63ff;
    }
    .appointment {
      border-bottom: 1px solid #eee;
      padding: 12px 0;
    }
    .appointment:last-child {
      border-bottom: none;
    }
    .dashboard-header {
      font-weight: 600;
      color: #444;
    }
  </style>
</head>
<body class="dash-body">
<div class="admin-container">
  <?php include("includes/sidebar.php"); ?>
  <div class="main-content">
    <?php include("includes/topbar.php"); ?>
    <div class="content mt-3">
      <?php if ($page === 'home'): ?>
        <h3 class="section-title mb-4"><i class="fa fa-chart-line me-2"></i> Dashboard Overview</h3>


        <div class="row g-4 mb-4">
          <div class="col-md-2">
            <div class="dashboard-card p-3 text-center">
              <i class="fa fa-users fa-2x mb-2"></i>
              <h6>Customers</h6>
              <h3><?= $customers ?></h3>
            </div>
          </div>
          <div class="col-md-2">
            <div class="dashboard-card p-3 text-center">
              <i class="fa fa-calendar fa-2x mb-2"></i>
              <h6>Bookings</h6>
              <h3><?= $bookings ?></h3>
            </div>
          </div>
          <div class="col-md-2">
            <div class="dashboard-card p-3 text-center">
              <i class="fa fa-scissors fa-2x mb-2"></i>
              <h6>Services</h6>
              <h3><?= $services ?></h3>
            </div>
          </div>
          <div class="col-md-2">
            <div class="dashboard-card p-3 text-center">
              <i class="fa fa-comments fa-2x mb-2"></i>
              <h6>Feedback</h6>
              <h3><?= $feedback ?></h3>
            </div>
          </div>
          <div class="col-md-4">
            <div class="dashboard-card p-3 text-center">
              <i class="fa fa-dollar-sign fa-2x mb-2"></i>
              <h6>Total Revenue</h6>
              <h3>₱<?= number_format($revenue, 2) ?></h3>
            </div>
          </div>
        </div>

        <!-- Recent Bookings Centered -->
        <div class="row justify-content-center">
          <div class="col-md-8">
            <div class="dashboard-card p-4 text-left">
              <h4 class="mb-3"><i class="fa fa-clock me-2"></i>Recent Bookings</h4>
              <?php if ($recentBookings && count($recentBookings) > 0): ?>
                <?php foreach ($recentBookings as $r): ?>
                  <div class="appointment">
                    <p class="mb-1"><strong><?= $r['full_name'] ?? 'Unknown' ?></strong></p>
                    <span class="text-muted">
                      #<?= $r['id'] ?> | <?= date("M d, Y", strtotime($r['booking_date'])) ?>
                      <?= date("h:i A", strtotime($r['booking_time'])) ?>
                    </span>
                    <small class="d-block">Status:
                      <span class="badge 
                        <?= $r['status'] === 'Completed' ? 'bg-success' : 
                            ($r['status'] === 'Pending' ? 'bg-warning text-dark' : 
                            ($r['status'] === 'Cancelled' ? 'bg-danger' : 'bg-secondary')) ?>">
                        <?= ucfirst($r['status']) ?>
                      </span>
                    </small>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-muted">No bookings yet</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php else: ?>
        <?php
          $file = $page.".php";
          if (file_exists($file)) {
            include($file);
          } else {
            echo "<p>Page not found.</p>";
          }
        ?>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
