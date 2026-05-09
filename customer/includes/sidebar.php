<div class="sidebar">
  <div class="logo mb-4 text-center">
    <img src="../assets/img/logo.jpg" style="border-radius: 50%; width:120px;">
  </div>
  <a href="customer_dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'customer_dashboard.php' ? 'active' : '' ?>">
    <i class="fa fa-home"></i> Home
  </a>
  <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>">
    <i class="fa fa-user"></i> Profile
  </a>
  <a href="booking.php" class="<?= basename($_SERVER['PHP_SELF']) === 'booking.php' ? 'active' : '' ?>">
    <i class="fa fa-calendar"></i> Bookings
  </a>
  <a href="services.php" class="<?= basename($_SERVER['PHP_SELF']) === 'services.php' ? 'active' : '' ?>">
    <i class="fa fa-scissors"></i> Services
  </a>
  <!-- ✅ New Finance Link -->
  <a href="finances.php" class="<?= basename($_SERVER['PHP_SELF']) === 'finances.php' ? 'active' : '' ?>">
    <i class="fa fa-wallet"></i> My Payments
  </a>
  <a href="../logout.php"><i class="fa fa-sign-out"></i> Logout</a>
</div>
