<div class="sidebar">
  <div class="logo mb-4 text-center">
    <img src="../assets/img/logo.jpg" style="border-radius: 50%; width:120px;">
  </div>

  <a href="admin_dashboard.php?page=home" 
     class="<?= ($_GET['page'] ?? 'home') === 'home' ? 'active' : '' ?>">
    <i class="fa fa-home"></i> Home
  </a>

  <a href="admin_dashboard.php?page=bookings" 
     class="<?= ($_GET['page'] ?? '') === 'bookings' ? 'active' : '' ?>">
    <i class="fa fa-calendar"></i> Bookings
  </a>

  <a href="admin_dashboard.php?page=customers" 
     class="<?= ($_GET['page'] ?? '') === 'customers' ? 'active' : '' ?>">
    <i class="fa fa-users"></i> Customers
  </a>

  <a href="admin_dashboard.php?page=services" 
     class="<?= ($_GET['page'] ?? '') === 'services' ? 'active' : '' ?>">
    <i class="fa fa-scissors"></i> Services
  </a>

  <a href="admin_dashboard.php?page=finances" 
     class="<?= ($_GET['page'] ?? '') === 'finances' ? 'active' : '' ?>">
    <i class="fa fa-coins"></i> Finances
  </a>

  <a href="admin_dashboard.php?page=reviews" 
     class="<?= ($_GET['page'] ?? '') === 'reviews' ? 'active' : '' ?>">
    <i class="fa fa-comments"></i> Reviews
  </a>


  <a href="../logout.php">
    <i class="fa fa-sign-out"></i> Logout
  </a>
</div>
