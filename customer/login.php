<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Login - Merz</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <!-- Logo Section -->
        <div class="image-container mb-3">
            <img class="logo-img" src="../assets/img/logo.jpg" alt="Merz Logo">
        </div>

        <h2>Customer Login</h2>
        <p>Welcome back! Please log in to manage your bookings and services.</p>

        <!-- ✅ Success Message -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- ✅ Error Message -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Login Fields -->
        <form action="validate.php<?php if(isset($_GET['service_id'])) echo '?service_id='.$_GET['service_id']; ?>" method="post" class="mt-4">
            <div class="input-group">
                <input type="email" placeholder="Enter Email" name="email" required>
                <i class="fas fa-user"></i>
            </div>
            <div class="input-group">
                <input type="password" placeholder="Enter Password" name="password" required>
                <i class="fas fa-lock"></i>
            </div>
            <button class="login-btn" type="submit">Log In</button>
        </form>

        <!-- Forgot Password -->
        <div class="mt-3 text-center">
            <a href="forgot-password.php" class="text-decoration-none">
                <i class="fas fa-key"></i> Forgot Password?
            </a>
        </div>

        <!-- Secondary Actions -->
        <div class="action-buttons mt-4">
            <button class="secondary-btn" type="button" onclick="window.location.href='signup.php'">
                <i class="fas fa-user-plus"></i> New Customer Registration
            </button>
        </div>

        <!-- Floating Back Arrow -->
        <a href="role_select.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>
</div>
</body>
</html>
