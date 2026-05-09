<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Merz - Register</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <div class="image-container mb-3">
            <img class="logo-img" src="../assets/img/logo.jpg" alt="Merz Logo">
        </div>
        <h2>Create Your Account</h2>
        <p>Join us for a premium beauty experience</p>


        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>


        <form action="signup-validate.php" method="post">
            <div class="input-group">
                <input type="text" placeholder="Full Name" name="full_name" required>
                <i class="fas fa-user"></i>
            </div>
            <div class="input-group">
                <input type="email" placeholder="Email Address" name="email" required>
                <i class="fas fa-envelope"></i>
            </div>
            <div class="input-group">
                <input type="password" placeholder="Password" name="password" required>
                <i class="fas fa-lock"></i>
            </div>
            <div class="input-group">
                <input type="password" placeholder="Re-enter Password" name="confirm_password" required>
                <i class="fas fa-lock"></i>
            </div>
            <button class="login-btn" type="submit"><i class="fas fa-user-check"></i> Register</button>
        </form>


        <div class="login-footer">
            <p><a href="login.php">Already have an account? Log In</a></p>
        </div>


        <a href="role_select.php" class="back-link"><i class="fas fa-arrow-left"></i></a>
    </div>
</div>
</body>
</html>


