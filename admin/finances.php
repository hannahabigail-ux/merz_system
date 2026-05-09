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

// ✅ Add Expense Record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    $productName = $_POST['product_name'] ?? '';
    $price       = floatval($_POST['price'] ?? 0);
    $quantity    = intval($_POST['quantity'] ?? 0);
    $totalPrice  = $price * $quantity; // auto calculate

    if ($productName && $price > 0 && $quantity > 0) {
        $stmt = $conn->prepare("INSERT INTO finances 
            (amount, payment_method, status, type, created_at) 
            VALUES (?, ?, ?, 'Expense', NOW())");
        $stmt->execute([$totalPrice, $productName, 'Unpaid']);
        $successMessage = "New expense record added successfully!";
    } else {
        $successMessage = "Please fill in all fields correctly.";
    }
}

// ✅ Delete Expense Record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_expense'])) {
    $stmt = $conn->prepare("DELETE FROM finances WHERE id=? AND type='Expense'");
    $stmt->execute([$_POST['id']]);
    $successMessage = "Expense record #{$_POST['id']} deleted successfully!";
}

// ✅ Fetch expenses
$stmt = $conn->query("SELECT * FROM finances WHERE type='Expense' ORDER BY created_at DESC");
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Revenue automatic from bookings + services
$totalRevenue = $conn->query("
    SELECT SUM(s.price)
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    WHERE b.status='Completed' OR b.status='Paid'
")->fetchColumn() ?? 0;

// ✅ Expenses manual
$totalExpense = $conn->query("SELECT SUM(amount) FROM finances WHERE type='Expense'")->fetchColumn() ?? 0;

// ✅ Net Profit
$netProfit = $totalRevenue - $totalExpense;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Finances</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
<body>
<div class="admin-container">
  <?php include __DIR__ . "/includes/sidebar.php"; ?>
  <div class="main-content">
    <?php include __DIR__ . "/includes/topbar.php"; ?>
    <h3 class="section-title mb-4"><i class="fa fa-coins me-2"></i> Financial Management</h3>
    <?php if ($successMessage): ?>
      <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <!-- ✅ Summary Cards -->
    <div class="row mb-4 g-3">
      <div class="col-md-4">
        <div class="card summary-card border-success shadow-sm h-100">
          <div class="card-body">
            <h5>Total Revenue</h5>
            <p>₱<?= number_format($totalRevenue, 2) ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card summary-card border-danger shadow-sm h-100">
          <div class="card-body">
            <h5>Total Expenses</h5>
            <p>₱<?= number_format($totalExpense, 2) ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card summary-card border-primary shadow-sm h-100">
          <div class="card-body">
            <h5>Net Profit</h5>
            <p>₱<?= number_format($netProfit, 2) ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- ✅ Add Expense Form -->
    <div class="dashboard-card p-3 mb-4">
      <form method="post" class="row g-3 align-items-center">
        <div class="col-md-3">
          <input type="text" name="product_name" class="form-control" placeholder="Product Name" required>
        </div>
        <div class="col-md-3">
          <input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required>
        </div>
        <div class="col-md-3">
          <input type="number" step="1" name="quantity" class="form-control" placeholder="Quantity" required>
        </div>
        <div class="col-md-3">
          <button type="submit" name="add_expense" class="btn btn-danger w-100">Add Expense</button>
        </div>
      </form>
    </div>

    <!-- ✅ Expenses Table -->
    <div class="dashboard-card p-3">
      <table class="table table-bordered table-hover align-middle w-100">
        <thead class="gradient-header text-white">
          <tr>
            <th>ID</th><th>Product Name</th><th>Amount</th><th>Date</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($expenses as $e): ?>
            <tr>
              <td><?= $e['id'] ?></td>
              <td><?= htmlspecialchars($e['payment_method']) ?></td>
              <td>₱<?= number_format($e['amount'], 2) ?></td>
              <td><?= date("M d, Y", strtotime($e['created_at'])) ?></td>
              <td>
                <form method="post" class="d-inline" onsubmit="return confirm('Delete this expense?');">
                  <input type="hidden" name="id" value="<?= $e['id'] ?>">
                  <button type="submit" name="delete_expense" class="btn btn-sm btn-light border px-2 ">
                    <i class="fa fa-trash text-danger"></i>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$expenses): ?>
            <tr><td colspan="6" class="text-center text-muted">No expenses recorded.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
