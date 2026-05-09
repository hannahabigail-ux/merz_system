<?php
// ✅ Safe session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/notification_helper.php";

// ✅ Ensure customer is logged in
if (!isset($_SESSION['customer'])) {
    header("Location: ../shared/login.php");
    exit();
}

// ✅ Get customer info
$customer_email = $_SESSION['customer'];
$stmt = $conn->prepare("SELECT id, full_name FROM customers WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $customer_email]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    die("Customer not found.");
}
$customer_id   = $customer['id'];
$customer_name = $customer['full_name'];

// ✅ Handle booking actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? null;
    $action     = $_POST['action'] ?? null;

    if ($action === 'create') {
        $service_id   = $_POST['service_id'];
        $booking_date = $_POST['booking_date'];
        $booking_time = $_POST['booking_time'];

        $stmt = $conn->prepare("INSERT INTO bookings (customer_id, service_id, booking_date, booking_time, status) 
                                VALUES (:customer_id, :service_id, :booking_date, :booking_time, 'Pending')");
        $stmt->execute([
            ':customer_id' => $customer_id,
            ':service_id' => $service_id,
            ':booking_date' => $booking_date,
            ':booking_time' => $booking_time
        ]);

        // ✅ Notify admin
        addNotification(
            $conn,
            $customer_id, 'customer',
            1, 'admin',
            'booking_created',
            $customer_name . " booked service #" . $service_id . " on " . $booking_date . " at " . $booking_time
        );

        // ✅ Notify customer
        addNotification(
            $conn,
            1, 'admin',
            $customer_id, 'customer',
            'booking_created',
            "Your booking for service #{$service_id} on {$booking_date} at {$booking_time} has been submitted."
        );

        header("Location: booking.php?success=1");
        exit();
    }

    if ($booking_id && $action === 'reschedule') {
        $new_date = $_POST['new_date'];
        $new_time = $_POST['new_time'];
        $stmt = $conn->prepare("UPDATE bookings 
                                SET booking_date=?, booking_time=?, status='Pending' 
                                WHERE id=? AND customer_id=? AND status NOT IN ('Completed','Cancelled')");
        $stmt->execute([$new_date, $new_time, $booking_id, $customer_id]);

        // ✅ Notify admin
        addNotification(
            $conn,
            $customer_id, 'customer',
            1, 'admin',
            'booking_rescheduled',
            $customer_name . " rescheduled booking #" . $booking_id . " to " . $new_date . " " . $new_time
        );

        // ✅ Notify customer
        addNotification(
            $conn,
            1, 'admin',
            $customer_id, 'customer',
            'booking_rescheduled',
            "Your booking #{$booking_id} has been rescheduled to {$new_date} at {$new_time}."
        );

        header("Location: booking.php?rescheduled=1");
        exit();
    }

    if ($booking_id && $action === 'cancel') {
        $stmt = $conn->prepare("UPDATE bookings 
                                SET status='Cancelled' 
                                WHERE id=? AND customer_id=? AND status NOT IN ('Completed','Cancelled')");
        $stmt->execute([$booking_id, $customer_id]);

        // ✅ Notify admin
        addNotification(
            $conn,
            $customer_id, 'customer',
            1, 'admin',
            'booking_cancelled',
            $customer_name . " cancelled booking #" . $booking_id
        );

        // ✅ Notify customer
        addNotification(
            $conn,
            1, 'admin',
            $customer_id, 'customer',
            'booking_cancelled',
            "Your booking #{$booking_id} has been cancelled."
        );

        header("Location: booking.php?cancelled=1");
        exit();
    }
}

// ✅ Fetch bookings with linked services
$bookStmt = $conn->prepare("
    SELECT b.id, b.booking_date, b.booking_time, b.status, b.service_id, s.service_name
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    WHERE b.customer_id = ?
    ORDER BY b.id DESC
");
$bookStmt->execute([$customer_id]);
$bookings = $bookStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bookings - Customer Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css?v=15">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="dash-body">
<div class="admin-container">
  <?php include("includes/sidebar.php"); ?>
  <div class="main-content">
    <?php include("includes/topbar.php"); ?>
    <div class="content mt-3">

      <!-- ✅ Alerts -->
      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success text-start">Booking created successfully!</div>
      <?php elseif (isset($_GET['rescheduled'])): ?>
        <div class="alert alert-info text-start">Booking updated and pending approval!</div>
      <?php elseif (isset($_GET['cancelled'])): ?>
        <div class="alert alert-warning text-start">Booking cancelled successfully!</div>
      <?php elseif (isset($_GET['feedback'])): ?>
        <div class="alert alert-success text-start">Feedback submitted successfully!</div>
      <?php elseif (isset($_GET['feedback_exists'])): ?>
        <div class="alert alert-warning text-start">You have already submitted feedback for this service.</div>
      <?php endif; ?>

      <!-- ✅ Book a Service Form -->
      <?php if (isset($_GET['service_id']) && intval($_GET['service_id']) > 0): ?>
        <?php
          $srvStmt = $conn->prepare("SELECT service_name FROM services WHERE id=? LIMIT 1");
          $srvStmt->execute([intval($_GET['service_id'])]);
          $srv = $srvStmt->fetch(PDO::FETCH_ASSOC);
          $serviceName = $srv ? htmlspecialchars($srv['service_name']) : "Selected Service";
        ?>
        <h3 class="section-title mb-4"><i class="fa fa-calendar-plus me-2"></i> Book: <?= $serviceName ?></h3>
        <div class="dashboard-card p-4 mb-4">
          <form method="post" action="booking.php">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="service_id" value="<?= intval($_GET['service_id']) ?>">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="booking_date" class="form-label">Booking Date</label>
                <input type="date" id="booking_date" name="booking_date" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label for="booking_time" class="form-label">Booking Time</label>
                <input type="time" id="booking_time" name="booking_time" class="form-control" required>
              </div>
            </div>
            <div class="mt-3 text-start">
              <button type="submit" class="btn btn-primary">Book Now</button>
            </div>
          </form>
        </div>
      <?php endif; ?>

      <h3 class="section-title mb-4"><i class="fa fa-calendar me-2"></i> My Bookings</h3>

      <!-- ✅ Bookings List -->
      <div class="dashboard-card p-4">
        <table class="table table-striped align-middle">
          <thead class="table-dark">
            <tr>
              <th class="text-start">Service</th>
              <th class="text-start">Date</th>
              <th class="text-start">Time</th>
              <th class="text-start">Status</th>
              <th class="text-start">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
              if ($bookings) {
                foreach ($bookings as $b) {
                  echo "<tr>
                          <td class='text-start'>".htmlspecialchars($b['service_name'])."</td>
                          <td class='text-start'>".date("M d, Y", strtotime($b['booking_date']))."</td>
                          <td class='text-start'>".date("h:i A", strtotime($b['booking_time']))."</td>
                          <td class='text-start'>
                            <span class='badge bg-".(
                              $b['status']=='Completed' ? 'success' :
                              ($b['status']=='Cancelled' ? 'danger' : 'warning')
                            )."'>
                              {$b['status']}
                            </span>
                          </td>
                          <td class='text-start'>";
                          if ($b['status'] === 'Completed') {
                      $fbStmt = $conn->prepare("SELECT rating, comments FROM feedback WHERE customer_id=? AND booking_id=? LIMIT 1");
                      $fbStmt->execute([$customer_id, $b['id']]);
                      $feedback = $fbStmt->fetch(PDO::FETCH_ASSOC);

                      if (!$feedback) {
                          echo "<form method='post' action='feedback.php' class='mt-2'>
                                  <input type='hidden' name='booking_id' value='{$b['id']}'>
                                  <input type='hidden' name='service_id' value='{$b['service_id']}'>
                                  <select name='rating' class='form-select form-select-sm mb-2' required>
                                    <option value=''>-- Rate Service --</option>
                                    <option value='5'>★★★★★ Excellent</option>
                                    <option value='4'>★★★★ Good</option>
                                    <option value='3'>★★★ Average</option>
                                    <option value='2'>★★ Poor</option>
                                    <option value='1'>★ Very Poor</option>
                                  </select>
                                  <textarea name='comments' class='form-control mb-2' placeholder='Write your feedback...' required></textarea>
                                  <button type='submit' class='btn btn-sm btn-success'>Submit Feedback</button>
                                </form>";
                      } else {
                          echo "<div class='mt-2 text-start'>
                                  <span class='badge bg-success'>Feedback submitted</span><br>
                                  <small><strong>Rating:</strong> {$feedback['rating']} / 5</small><br>
                                  <small><strong>Comments:</strong> ".htmlspecialchars($feedback['comments'])."</small>
                                </div>";
                      }
                  } elseif ($b['status'] !== 'Cancelled' && $b['status'] !== 'Completed') {
                      echo "<div class='d-flex flex-wrap gap-2'>
                              <button class='btn btn-sm btn-warning' data-bs-toggle='modal' data-bs-target='#confirmModal' 
                                data-id='{$b['id']}' data-action='reschedule'>Reschedule</button>
                              <button class='btn btn-sm btn-danger' data-bs-toggle='modal' data-bs-target='#confirmModal' 
                                data-id='{$b['id']}' data-action='cancel'>Cancel</button>
                            </div>";
                  } else {
                      echo "<span class='text-muted'>—</span>";
                  }

                  echo "</td></tr>";
                }
              } else {
                echo "<tr><td colspan='5' class='text-center'>No bookings found.</td></tr>";
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <form method="post" action="booking.php">
        <div class="modal-header bg-dark text-white rounded-top">
          <h5 class="modal-title">Confirm Action</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p id="confirmText" class="fw-semibold">Are you sure?</p>
          <input type="hidden" name="booking_id" id="bookingId">
          <input type="hidden" name="action" id="actionField">
          <!-- Reschedule inputs (hidden by default, shown only if reschedule) -->
          <div id="rescheduleFields" class="mt-3 d-none">
            <label for="new_date" class="form-label">New Date</label>
            <input type="date" name="new_date" id="new_date" class="form-control mb-2">
            <label for="new_time" class="form-label">New Time</label>
            <input type="time" name="new_time" id="new_time" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
          <button type="submit" class="btn btn-primary">Yes, proceed</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // ✅ Confirmation Modal Logic
  const confirmModal = document.getElementById('confirmModal');
  confirmModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const bookingId = button.getAttribute('data-id');
    const action = button.getAttribute('data-action');

    document.getElementById('bookingId').value = bookingId;
    document.getElementById('actionField').value = action;

    const confirmText = document.getElementById('confirmText');
    const rescheduleFields = document.getElementById('rescheduleFields');

    if (action === 'cancel') {
      confirmText.textContent = "Are you sure you want to cancel this booking?";
      rescheduleFields.classList.add('d-none');
    } else if (action === 'reschedule') {
      confirmText.textContent = "Please select a new date and time to reschedule.";
      rescheduleFields.classList.remove('d-none');
    }
  });
</script>
</body>
</html>
