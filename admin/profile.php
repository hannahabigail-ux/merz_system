<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../includes/db.php";

// ✅ Ensure admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$adminEmail = $_SESSION['admin']; // session stores email

// ✅ Fetch current admin info
$stmt = $conn->prepare("SELECT id, full_name, email FROM admins WHERE email=? LIMIT 1");
$stmt->execute([$adminEmail]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("Admin not found.");
}

$admin_id   = $admin['id'];
$admin_name = $admin['full_name'] ?? 'Admin';

// ✅ Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);

    $update = $conn->prepare("UPDATE admins SET full_name=?, email=? WHERE id=?");
    $update->execute([$full_name, $email, $admin_id]);

    // ✅ Update session values
    $_SESSION['admin_name'] = $full_name;
    $_SESSION['admin']      = $email;

    header("Location: profile.php?updated=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { background: linear-gradient(135deg, #f5f0ff, #fce7f3, #dbeafe); font-family: 'Segoe UI', sans-serif; }
    .dashboard-header { font-weight: 700; color: #222; border-left: 4px solid #0d6efd; padding-left: 10px; margin-bottom: 1rem; }
    .summary-card { border-radius: 12px; color: #fff; min-height: 150px; display: flex; align-items: center; justify-content: center; text-align: center; }
    .summary-card h5 { font-size: 1rem; margin-bottom: .5rem; }
    .summary-card p { font-size: 1.6rem; font-weight: 600; margin: 0; }
    .border-success { background: linear-gradient(135deg, #28a745, #6cc070); }
    .border-danger { background: linear-gradient(135deg, #dc3545, #f08080); }
    .border-primary { background: linear-gradient(135deg, #0d6efd, #6ca8ff); }
    .dashboard-card { border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.05); width: 100%; }
    .gradient-header { background: linear-gradient(135deg, #0d6efd, #6ca8ff); }
    .main-content { margin-left: 100px; padding: 20px; margin-top: 30px; }
    .btn { border-radius: 8px; font-weight: 500; }
    .btn-danger { background: linear-gradient(135deg, #dc3545, #f08080); border: none; }
    .dash-body .main-content {
    margin-left: 100px;   /* keep your sidebar offset */
    padding: 20px;      
    margin-top: 30px;    /* move content upward */
    }
  </style>
</head>
<body class="dash-body">
<div class="admin-container">
  <?php include("includes/sidebar.php"); ?>
  <div class="main-content">
    <?php include("includes/topbar.php"); ?>
     <h3 class="section-title mb-4"><i class="fa fa-user-shield me-2"></i> Admin Profile</h3>

    <div class="content mt-3">



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
          <p class="text-muted">Manage your admin information</p>
        </div>

        <div class="row g-4">
          <!-- Profile Card -->
          <div class="col-md-4">
            <div class="card shadow-sm text-center p-4">
              <h5 class="fw-semibold"><?= htmlspecialchars($admin['full_name']) ?></h5>
              <p class="text-muted"><?= htmlspecialchars($admin['email']) ?></p>
            </div>
          </div>

          <!-- Edit Form -->
          <div class="col-md-8">
            <div class="card shadow-sm p-4">
              <h5 class="fw-bold mb-3">Edit Information</h5>
              <form method="post" action="profile.php">
                <div class="mb-3">
                  <label class="form-label">Full Name</label>
                  <input type="text" name="full_name" class="form-control" 
                         value="<?= htmlspecialchars($admin['full_name']) ?>" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Email Address</label>
                  <input type="email" name="email" class="form-control" 
                         value="<?= htmlspecialchars($admin['email']) ?>" required>
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
