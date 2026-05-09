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
  <title>Services - Customer Dashboard</title>
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
      <h3 class="section-title mb-4"><i class="fa fa-scissors me-2"></i> Our Services</h3>

      <!-- Search bar -->
      <div class="row mb-5">
        <div class="col-md-6 mx-auto">
          <input type="text" id="serviceSearch" class="form-control search-bar" placeholder="Search for a service...">
        </div>
      </div>

      <!-- Accordion categories -->
      <div class="accordion" id="servicesAccordion">
        <?php
          $catStmt = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
          $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

          foreach ($categories as $index => $cat) {
            $catId = $cat['id'];
            $catName = htmlspecialchars($cat['category_name']);
            $isFirst = ($index === 0);
            $showClass = $isFirst ? 'show' : '';
            $collapsedClass = $isFirst ? '' : 'collapsed';

            echo '
            <div class="accordion-item mb-3">
              <h2 class="accordion-header" id="heading'.$index.'">
                <button class="accordion-button '.$collapsedClass.'" type="button" data-bs-toggle="collapse" data-bs-target="#collapse'.$index.'">
                  '.$catName.'
                </button>
              </h2>
              <div id="collapse'.$index.'" class="accordion-collapse collapse '.$showClass.'">
                <div class="accordion-body">
                  <div class="row g-4">';
            
            $srvStmt = $conn->prepare("SELECT * FROM services WHERE category_id=? AND status='Active' ORDER BY service_name ASC");
            $srvStmt->execute([$catId]);
            $services = $srvStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($services as $s) {
              // Fetch recent feedback for this service
              $fbStmt = $conn->prepare("
                SELECT f.rating, f.comments, f.created_at, c.full_name
                FROM feedback f
                JOIN customers c ON f.customer_id = c.id
                WHERE f.service_id = ?
                ORDER BY f.created_at DESC
                LIMIT 3
              ");
              $fbStmt->execute([$s['id']]);
              $feedbacks = $fbStmt->fetchAll(PDO::FETCH_ASSOC);

              echo '
                <div class="col-md-4">
                  <div class="card service-card text-center p-3">
                    <h5 class="service-name">'.htmlspecialchars($s['service_name']).'</h5>
                    <p class="text-muted">'.htmlspecialchars($s['description']).'</p>
                    <p class="fw-bold text-purple">₱'.number_format($s['price'], 2).'</p>
                    <a href="booking.php?service_id='.$s['id'].'" class="book-btn">Book Now</a>
                    <hr>
                    <h6 class="mt-2">Recent Feedback</h6>';
                    
                    if ($feedbacks) {
                      foreach ($feedbacks as $fb) {
                        // Render stars
                        $stars = "";
                        for ($i=1; $i<=5; $i++) {
                          $stars .= '<i class="fa fa-star'.($i <= $fb['rating'] ? ' text-warning' : ' text-muted').'"></i>';
                        }
                        echo '
                          <div class="feedback-item text-start mt-2">
                            <strong>'.htmlspecialchars($fb['full_name']).'</strong><br>
                            '.$stars.'<br>
                            <small>'.htmlspecialchars($fb['comments']).'</small><br>
                            <small class="text-muted">'.date("M d, Y", strtotime($fb['created_at'])).'</small>
                          </div>';
                      }
                    } else {
                      echo '<p class="text-muted"><em>No feedback yet.</em></p>';
                    }

              echo '
                  </div>
                </div>';
            }

            echo '
                  </div>
                </div>
              </div>
            </div>';
          }
        ?>
      </div>

    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const searchInput = document.getElementById("serviceSearch");
  if (searchInput) {
    searchInput.addEventListener("keyup", function() {
      const filter = searchInput.value.toLowerCase();
      const serviceCards = document.querySelectorAll(".service-card");
      serviceCards.forEach(card => {
        const name = card.querySelector(".service-name").textContent.toLowerCase();
        const desc = card.querySelector(".text-muted").textContent.toLowerCase();
        if (name.includes(filter) || desc.includes(filter)) {
          card.parentElement.style.display = "";
          const accordionBody = card.closest(".accordion-collapse");
          if (accordionBody && !accordionBody.classList.contains("show")) {
            new bootstrap.Collapse(accordionBody, { show: true });
          }
        } else {
          card.parentElement.style.display = "none";
        }
      });
    });
  }
</script>
</body>
</html>
