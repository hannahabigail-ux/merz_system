<?php
session_start();
require_once __DIR__ . "/../includes/db.php";
if (!isset($_SESSION['customer'])) { header("Location: login.php"); exit(); }

$email = $_SESSION['customer'];
$stmt = $conn->prepare("SELECT id, full_name FROM customers WHERE email=?");
$stmt->execute([$email]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);
$customer_name = $customer['full_name'] ?? 'Customer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search - Customer Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css?v=14">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="dash-body">
<div class="admin-container">
  <?php include("includes/sidebar.php"); ?>
  <div class="main-content">
    <?php include("includes/topbar.php"); ?>
    <div class="content mt-3">

      <h2 class="section-title">Search Services</h2>
      <form method="get" action="search.php" class="mb-4">
        <input type="text" name="q" class="form-control search-bar" placeholder="Search services..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
      </form>

      <?php
        if (isset($_GET['q']) && $_GET['q'] !== '') {
            $q = "%" . $_GET['q'] . "%";
            $stmt = $conn->prepare("SELECT id, service_name, price FROM services WHERE service_name LIKE :q AND status='Active'");
            $stmt->execute([':q' => $q]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                echo "<div class='row g-4'>";
                foreach ($results as $service) {
                    echo "<div class='col-md-4'>
                            <div class='card service-card text-center p-3'>
                              <h5 class='text-purple'>".htmlspecialchars($service['service_name'])."</h5>
                              <p>₱".number_format($service['price'], 2)."</p>
                              <a href='booking.php?service_id={$service['id']}' class='btn book-btn'>Book Now</a>
                            </div>
                          </div>";
                }
                echo "</div>";
            } else {
                echo "<p>No services found.</p>";
            }
        }
      ?>

    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
