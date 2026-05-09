<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Merz - Role Selection</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <div class="image-container mb-3"> 
            <img class="logo-img" src="assets/img/logo.jpg" alt="Merz Logo"> 
        </div>
        <h2>Select Your Role</h2>
        <p>Please choose how you want to access the system</p>

        <div class="action-buttons">
            <!-- Customer role goes to login -->
            <button class="secondary-btn" type="button" onclick="window.location.href='customer/login.php'">
                <i class="fas fa-user"></i> Customer
            </button>

            <!-- Admin role goes to admin login -->
            <button class="secondary-btn" type="button" onclick="window.location.href='admin/admin_login.php'">
                <i class="fas fa-user-shield"></i> Admin
            </button>

        </div>

        <!-- Floating Back Arrow -->
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>
</div>
</body>
</html>
