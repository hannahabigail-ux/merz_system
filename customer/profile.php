<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

if (!isset($_SESSION['customer'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['customer'];
$stmt = $conn->prepare("SELECT id, full_name, email FROM customers WHERE email=? LIMIT 1");
$stmt->execute([$email]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Define $customer_name before including topbar
$customer_name = $customer['full_name'] ?? 'Customer';
$customer_id   = $customer['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customer Dashboard - Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css?v=17">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="dash-body">
<div class="admin-container">
  <?php include("includes/sidebar.php"); ?>
  <div class="main-content">
    <?php include("includes/topbar.php"); ?>
    <div class="content mt-3">

    <h3 class="section-title mb-4"><i class="fa fa-user me-2"></i> My Profile</h3>
      <!-- ✅ Success Alert -->
      <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fa fa-check-circle me-2"></i> Profile updated successfully!
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <div class="container-fluid profile-section">
        <div class="profile-header mb-4 text-center">
          <h2 class="fw-bold">Account Details</h2>
          <p class="text-muted">Manage your personal information</p>
        </div>

        <div class="row g-4">
          <!-- Profile Card -->
          <div class="col-md-4">
            <div class="card shadow-sm text-center p-4">
              <h5 class="fw-semibold"><?= htmlspecialchars($customer['full_name']) ?></h5>
              <p class="text-muted"><?= htmlspecialchars($customer['email']) ?></p>
            </div>
          </div>

          <!-- Edit Form -->
          <div class="col-md-8">
            <div class="card shadow-sm p-4">
              <h5 class="fw-bold mb-3">Edit Information</h5>
              <form method="post" action="update_profile.php">
                <div class="mb-3">
                  <label class="form-label">Full Name</label>
                  <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($customer['full_name']) ?>" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Email Address</label>
                  <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer['email']) ?>" readonly>
                </div>
                <button type="submit" name="update_profile" class="btn book-btn w-100">Update Profile</button>
              </form>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
