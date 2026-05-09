<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../includes/db.php";

// ✅ Ensure admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: ../shared/login.php");
    exit();
}

// ✅ Get admin info
$email = $_SESSION['admin'];
$stmt = $conn->prepare("SELECT id, full_name FROM admins WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("Admin not found.");
}
$admin_id = $admin['id'];

// ✅ Mark all notifications as read for this admin
$updateStmt = $conn->prepare("
    UPDATE notifications 
    SET is_read = 1 
    WHERE receiver_id = :rid AND receiver_role = 'admin'
");
$updateStmt->execute([':rid' => $admin_id]);

// ✅ Fetch notifications for this admin
$stmt = $conn->prepare("
    SELECT content, created_at, is_read 
    FROM notifications 
    WHERE receiver_id = :rid AND receiver_role = 'admin'
    ORDER BY created_at DESC
");
$stmt->execute([':rid' => $admin_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Notifications</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { background:linear-gradient(135deg, #f5f0ff, #fce7f3, #dbeafe); font-family: 'Segoe UI', sans-serif; }
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
    <h3 class="section-title mb-4"><i class="fa fa-bell me-2"></i> Notifications</h3>
    <div class="content mt-3">

      

      <div class="dashboard-card p-4">
        <?php if ($notifications): ?>
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Message</th>
                <th>Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($notifications as $notif): ?>
                <tr class="<?= $notif['is_read'] ? '' : 'table-warning' ?>">
                  <td><?= htmlspecialchars($notif['content']) ?></td>
                  <td><?= date("M d, Y h:i A", strtotime($notif['created_at'])) ?></td>
                  <td><?= $notif['is_read'] ? 'Read' : 'Unread' ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="text-muted text-center">No notifications yet.</p>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>
</body>
</html>

