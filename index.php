<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Merz Beauty Glow</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-purple" href="index.php">
      <img src="assets/img/logo.jpg" alt="Merz Logo" width="40" height="40" class="rounded-circle me-2">
      Merz Beauty Glow
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
        <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
        <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
        <li class="nav-item">
          <a class="nav-link btn btn-gradient px-3 ms-2" href="role_select.php">Log In / Sign Up</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- HERO -->
<!-- HERO -->
<header class="hero-section d-flex align-items-center">
  <div class="overlay"></div>
  <div class="container text-center hero-content">
    <img src="assets/img/logo.jpg" alt="Merz Logo" class="home-logo mb-3">
    <h1 class="hero-title">Welcome to Merz Beauty Glow</h1>
    <p class="hero-subtitle mb-4">Premium salon and spa experiences, designed for you.</p>

    <!-- Button positioned directly below subtitle -->
    <a href="role_select.php" class="home-btn mt-3">
      <i class="fas fa-calendar-check"></i> Book an Appointment
    </a>
  </div>
</header>


<!-- SERVICES -->
<section id="services" class="services-section container my-5">
  <h2 class="section-title text-center mb-4">Our Services</h2>

  <div class="row mb-5">
    <div class="col-md-6 mx-auto">
      <input type="text" id="serviceSearch" class="form-control search-bar" placeholder="Search for a service...">
    </div>
  </div>

  <div class="accordion" id="servicesAccordion">
    <?php
      require_once("includes/db.php");
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
          echo '
            <div class="col-md-4">
              <div class="card service-card text-center p-3">
                <h5 class="service-name">'.htmlspecialchars($s['service_name']).'</h5>
                <p class="text-muted">'.htmlspecialchars($s['description']).'</p>
                <p class="fw-bold text-purple">₱'.number_format($s['price'], 2).'</p>
                <a href="role_select.php?service_id='.$s['id'].'" class="book-btn">Book Now</a>';

          // ✅ Show recent feedback for this service using `message` column
          $fbStmt = $conn->prepare("SELECT f.rating, f.message, c.full_name 
                                    FROM feedback f 
                                    JOIN customers c ON f.customer_id = c.id 
                                    WHERE f.service_id = ? 
                                    ORDER BY f.created_at DESC LIMIT 3");
          $fbStmt->execute([$s['id']]);
          $reviews = $fbStmt->fetchAll(PDO::FETCH_ASSOC);

          if ($reviews) {
            echo "<div class='mt-3 text-start'><strong>Recent Feedback:</strong><ul class='list-unstyled'>";
            foreach ($reviews as $r) {
              echo "<li><span class='text-warning'>".str_repeat("★", (int)$r['rating'])."</span> "
                 .htmlspecialchars($r['message'])." <em>- ".htmlspecialchars($r['full_name'])."</em></li>";
            }
            echo "</ul></div>";
          }

          echo '</div></div>';
        }

        echo '
              </div>
            </div>
          </div>
        </div>';
      }
    ?>
  </div>
</section>

<!-- ABOUT -->
<section id="about" class="py-5 bg-pastel">
  <div class="container">
    <h2 class="fw-bold text-center mb-5 section-title text-accent">About Us</h2>

    <!-- Intro -->
    <div class="row align-items-center mb-5">
      <div class="col-md-6 text-center text-md-start fade-in">
        <h3 class="fw-bold mb-3 text-accent">Discover Your Glow</h3>
        <p class="lead">
          At <strong>Merz Beauty Glow Salon & Spa</strong>, beauty meets comfort. 
          Our team blends expertise and creativity to bring out your natural confidence and radiance.
        </p>
        <p>
          Located in Casisang, Malaybalay City, we provide personalized salon and spa experiences 
          that leave you refreshed, empowered, and beautifully renewed.
        </p>
      </div>
      <div class="col-md-6 text-center fade-in">
        <div class="card-accent p-2 rounded shadow-sm">
          <img src="assets/img/salon.jpg" alt="Salon Image" 
              class="img-fluid rounded" style="object-fit:cover; max-height:350px; width:100%;">
        </div>
      </div>
    </div>

    <!-- Highlights -->
    <div class="row text-center mb-5">
      <div class="col-md-4 mb-3 fade-in">
        <div class="card-accent h-100">
          <h5 class="fw-bold">Premium Care</h5>
          <p>Gentle, dermatologist-tested treatments designed to enhance your natural glow.</p>
        </div>
      </div>
      <div class="col-md-4 mb-3 fade-in">
        <div class="card-accent h-100">
          <h5 class="fw-bold">Relaxing Atmosphere</h5>
          <p>A welcoming space where every detail is handled with care and precision.</p>
        </div>
      </div>
      <div class="col-md-4 mb-3 fade-in">
        <div class="card-accent h-100">
          <h5 class="fw-bold">Trusted Team</h5>
          <p>Skilled professionals with hundreds of satisfied clients.</p>
        </div>
      </div>
    </div>

<!-- Featured Services -->
<h3 class="fw-bold text-center mb-4 text-accent">Featured Services</h3>
<div class="row text-center mb-5">
  <!-- Service 1 -->
  <div class="col-md-3 mb-4 fade-in">
    <div class="card shadow-sm border-0 h-100">
      <img src="assets/img/rebond.jpg" alt="Hair Rebonding" 
           class="card-img-top rounded-top"
           style="object-fit:cover; width:100%; height:300px;">
      <div class="card-body">
        <h5 class="fw-bold text-accent">Hair Rebonding</h5>
        <p class="text-muted">Smooth, straight, and shiny hair with our professional rebonding treatment.</p>
      </div>
    </div>
  </div>

  <!-- Service 2 (Gel Polish / Nail Care) -->
  <div class="col-md-3 mb-4 fade-in">
    <div class="card shadow-sm border-0 h-100">
      <img src="assets/img/nailCare.PNG" alt="Gel Polish / Nail Care" 
           class="card-img-top rounded-top"
           style="object-fit:cover; width:100%; height:300px;">
      <div class="card-body">
        <h5 class="fw-bold text-accent">Gel Polish / Nail Care</h5>
        <p class="text-muted">Beautiful, long-lasting nails with our expert gel polish and nail care services.</p>
      </div>
    </div>
  </div>

  <!-- Service 3 (Facial Care) -->
  <div class="col-md-3 mb-4 fade-in">
    <div class="card shadow-sm border-0 h-100">
      <img src="assets/img/facial.PNG" alt="Facial Care" 
           class="card-img-top rounded-top"
           style="object-fit:cover; width:100%; height:300px;">
      <div class="card-body">
        <h5 class="fw-bold text-accent">Facial Care</h5>
        <p class="text-muted">Rejuvenating facial treatments to refresh and revitalize your skin.</p>
      </div>
    </div>
  </div>

  <!-- Service 4 (Foot Care / Spa Massage) -->
  <div class="col-md-3 mb-4 fade-in">
    <div class="card shadow-sm border-0 h-100">
      <img src="assets/img/footcare.jpg" alt="Foot Care / Spa Massage" 
           class="card-img-top rounded-top"
           style="object-fit:cover; width:100%; height:300px;">
      <div class="card-body">
        <h5 class="fw-bold text-accent">Foot Care / Spa Massage</h5>
        <p class="text-muted">Relaxing foot spa and massage treatments for total comfort and care.</p>
      </div>
    </div>
  </div>
</div>



    <!-- Owner's Message -->
    <div class="row align-items-center mb-5">
      <div class="col-md-4 text-center fade-in">
        <img src="assets/img/owner.jpg" alt="Owner" 
             class="img-fluid rounded-circle shadow-lg" style="width:200px; height:200px; object-fit:cover;">
      </div>
      <div class="col-md-8 text-md-start fade-in">
        <h4 class="fw-bold text-accent">Message from the Owner</h4>
        <p class="lead">
          “At Merz Beauty Glow, our mission is to empower every client to feel confident and radiant. 
          We believe beauty is not just about appearance, but about self‑care and renewal.” 
        </p>
        <p class="fw-bold mb-0">— Mercy Yap, Owner</p>
      </div>
    </div>

    <!-- Meet the Team -->
    <div class="row text-center">
      <h3 class="fw-bold mb-4 text-accent">Meet Our Team</h3>
      <div class="col-12 fade-in">
        <div class="card shadow-sm border-0 text-center bg-pastel">
          <div class="p-3">
            <img src="assets/img/staff.jpg" alt="Our Staff" 
                 class="img-fluid rounded shadow-lg border border-3 border-accent"
                 style="object-fit:cover; max-height:450px; width:100%; border-radius:12px;">
          </div>
          <div class="card-body">
            <h5 class="fw-bold text-accent">Our Dedicated Staff</h5>
            <p class="text-muted">
              The talented team at Merz Beauty Glow Salon & Spa — ready to bring out your natural confidence and radiance.
            </p>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>



<!-- CONTACT -->
<section id="contact" class="py-5 text-center bg-pastel">
  <div class="container">
    <h2 class="fw-bold mb-4 section-title text-accent">Contact Us</h2>
    <p class="lead mb-4">We’d love to hear from you. Reach out for appointments, inquiries, or feedback.</p>
    <div class="row justify-content-center">
      
      <!-- Email -->
      <div class="col-md-3 mb-3 fade-in">
        <div class="card-accent h-100">
          <i class="fas fa-envelope fa-2x mb-3"></i>
          <h5>Email</h5>
          <p>info@merzbeautyglow.com</p>
        </div>
      </div>

      <!-- Phone -->
      <div class="col-md-3 mb-3 fade-in">
        <div class="card-accent h-100">
          <i class="fas fa-phone fa-2x mb-3"></i>
          <h5>Phone</h5>
          <p>0912-345-6789</p>
        </div>
      </div>

      <!-- Location -->
      <div class="col-md-3 mb-3 fade-in">
        <div class="card-accent h-100">
          <i class="fas fa-map-marker-alt fa-2x mb-3"></i>
          <h5>Location</h5>
          <p>Corner Grema Subdivision, Brgy. Casisang,<br>Malaybalay City, Bukidnon, Philippines, 8700</p>
        </div>
      </div>

      <!-- Facebook -->
      <div class="col-md-3 mb-3 fade-in">
        <div class="card-accent h-100">
          <i class="fab fa-facebook fa-2x mb-3"></i>
          <h5>Facebook</h5>
          <p>
            <a href="https://www.facebook.com/merzbeautyglowsalonandspa" 
               target="_blank" 
               class="text-decoration-underline" 
               style="color:#fff;">
               Merz Beauty Glow Salon & Spa
            </a>
          </p>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- FOOTER -->
<footer class="py-4 bg-pastel text-center">
  <div class="container">
    <p class="mb-0 text-accent">&copy; <?= date("Y"); ?> Merz Beauty Glow Salon & Spa. All rights reserved.</p>
  </div>
</footer>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Service search filter
const searchInput = document.getElementById('serviceSearch');
if (searchInput) {
  searchInput.addEventListener('keyup', function() {
    let query = this.value.toLowerCase();
    document.querySelectorAll('.service-card').forEach(function(card) {
      let name = card.querySelector('.service-name').textContent.toLowerCase();
      card.style.display = name.includes(query) ? "block" : "none";
    });
  });
}

// Smooth scroll for navbar links
document.querySelectorAll('a.nav-link').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    if (this.getAttribute('href').startsWith('#')) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    }
  });
});

// Fade-in on scroll
const fadeElements = document.querySelectorAll('.fade-in');

function handleScroll() {
  fadeElements.forEach(el => {
    const rect = el.getBoundingClientRect();
    if (rect.top < window.innerHeight - 100) {
      el.classList.add('visible');
    }
  });
}

window.addEventListener('scroll', handleScroll);
window.addEventListener('load', handleScroll);
</script>


</body>
</html>
