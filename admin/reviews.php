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

$successMessage = null;

// ✅ Handle Delete Feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_feedback'])) {
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id=?");
    $stmt->execute([$_POST['id']]);
    $successMessage = "Feedback #{$_POST['id']} deleted successfully!";
}

// ✅ Fetch feedback records with customer + service info
$stmt = $conn->query("
    SELECT f.id, f.rating, f.comments, f.created_at,
           c.full_name AS customer_name,
           s.service_name
    FROM feedback f
    JOIN customers c ON f.customer_id = c.id
    JOIN services s ON f.service_id = s.id
    ORDER BY f.created_at DESC
");
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Feedback</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
  /* Reviews Section */
.reviews-container {
  background: linear-gradient(135deg, #f5f0ff, #fce7f3, #dbeafe);
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  margin: 20px;
}

.reviews-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.reviews-header h2 {
  font-size: 1.6rem;
  font-weight: 600;
  color: #6a1b9a; /* purple accent */
  display: flex;
  align-items: center;
  gap: 10px;
}

.reviews-header h2 i {
  color: #ec407a; /* pink accent */
  font-size: 1.4rem;
}

/* Filter bar */
.filter-bar {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}

.filter-bar select {
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #ddd;
  font-size: 0.95rem;
  cursor: pointer;
  transition: border-color 0.3s;
}

.filter-bar select:hover {
  border-color: #ec407a;
}

/* Reviews Table */
.reviews-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.95rem;
}

.reviews-table th {
  background: linear-gradient(90deg, #ec407a, #6a1b9a);
  color: #fff;
  padding: 12px;
  text-align: left;
  font-weight: 500;
}

  .dash-body .main-content {
    margin-left: 100px;   /* keep your sidebar offset */
    padding: 20px;      
    margin-top: 30px;    /* move content upward */
    }

.reviews-table td {
  padding: 12px;
  border-bottom: 1px solid #f0f0f0;
  vertical-align: middle;
}

.reviews-table tr:hover {
  background: #fdf2f8; /* subtle pink hover */
}

/* Rating stars */
.rating-stars {
  color: #ffb400;
  font-size: 1.1rem;
}

/* Comments styling */
.comment-bubble {
  background: #f9f9f9;
  padding: 10px 14px;
  border-radius: 10px;
  font-style: italic;
  color: #555;
}

/* Actions buttons */
.action-btn {
  padding: 6px 12px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  font-size: 0.85rem;
  transition: background 0.3s;
}

.action-btn.reply {
  background: #6a1b9a;
  color: #fff;
}

.action-btn.highlight {
  background: #ec407a;
  color: #fff;
}

.action-btn.delete {
  background: #e53935;
  color: #fff;
}

.action-btn:hover {
  opacity: 0.85;
}

/* Empty state */
.empty-state {
  text-align: center;
  padding: 40px;
  color: #888;
  font-size: 1rem;
}

.empty-state i {
  font-size: 2rem;
  color: #ec407a;
  margin-bottom: 10px;
}

</style>
</head>
<body class="dash-body">

<div class="admin-container">
  <?php include __DIR__ . "/includes/sidebar.php"; ?>
  <?php include __DIR__ . "/includes/topbar.php"; ?>

  <div class="main-content">
    <h3 class="section-title"><i class="fa fa-comments me-2"></i> Reviews</h3>

    <?php if ($successMessage): ?>
      <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <!-- ✅ Feedback Table -->
    <table class="table table-bordered align-middle">
      <thead class="gradient-header text-white">
        <tr>
          <th>ID</th>
          <th>Customer</th>
          <th>Service</th>
          <th>Rating</th>
          <th>Comments</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($feedbacks): ?>
          <?php foreach ($feedbacks as $fb): ?>
            <tr>
              <td><?= $fb['id'] ?></td>
              <td><?= htmlspecialchars($fb['customer_name']) ?></td>
              <td><?= htmlspecialchars($fb['service_name']) ?></td>
              <td>
                <?php
                  for ($i = 1; $i <= 5; $i++) {
                    echo $i <= (int)$fb['rating']
                      ? "<i class='fa-solid fa-star text-warning'></i>"
                      : "<i class='fa-regular fa-star text-muted'></i>";
                  }
                ?>
              </td>
              <td><?= htmlspecialchars($fb['comments']) ?></td>
              <td><?= date("M d, Y h:i A", strtotime($fb['created_at'])) ?></td>
              <td>
                <!-- ✅ Delete Feedback Button -->
                <button type="button" class="btn btn-sm btn-light px-2 border"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteFeedbackModal"
                        data-id="<?= $fb['id'] ?>"
                        data-name="<?= htmlspecialchars($fb['service_name']) ?>"
                        title="Delete Feedback">
                  <i class="fa fa-trash" style="color:#dc3545;"></i>
                </button>
              </td>


            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center">No feedback found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    <div class="modal fade" id="deleteFeedbackModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="fa fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p id="deleteFeedbackText">Are you sure you want to delete this feedback?</p>
          <input type="hidden" name="id" id="deleteFeedbackId">
          <input type="hidden" name="delete_feedback" value="1">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="fa fa-trash me-1"></i> Yes, Delete
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

  </div> <!-- end main-content -->
</div> <!-- end admin-container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const deleteFeedbackModal = document.getElementById('deleteFeedbackModal');
  deleteFeedbackModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');

    document.getElementById('deleteFeedbackId').value = id;
    document.getElementById('deleteFeedbackText').textContent =
      "Are you sure you want to delete feedback for \"" + name + "\" (ID #" + id + ")?";
  });
</script>

</body>
</html>
