<?php 
session_start(); 
?>

<!DOCTYPE html>
<html>
   <head>
        <title>Admin Login - Merz</title>
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

                <h2>Admin Login</h2>
                <p>Authorized personnel only. Please log in to manage the system.</p>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <!-- Login Fields -->
                <form action="validate_admin.php" method="post" class="mt-4">
                  <div class="input-group">
                      <input type="email" placeholder="Enter Admin Email" name="email" required>
                      <i class="fas fa-envelope"></i>
                  </div>

                  <div class="input-group">
                     <input type="password" placeholder="Enter Password" name="password" required>
                     <i class="fas fa-lock"></i>
                  </div>

                  <button class="login-btn" type="submit">
                      <i class="fas fa-sign-in-alt"></i> Log In
                  </button>

                  <!-- Forgot Password -->
                  <div class="login-footer mt-3">
                      <p><a href="../customer/forgot-password.php">Forgot Password?</a></p>
                  </div>
               </form>
                
                <!-- Floating Back Arrow -->
                <a href="../shared/role_select.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                </a>


            </div>
        </div>
   </body>
</html>
