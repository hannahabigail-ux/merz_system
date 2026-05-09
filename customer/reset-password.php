<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_code_verified']) || !$_SESSION['reset_code_verified']) {
    header("Location: reset_code.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword === $confirmPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("UPDATE customers SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $_SESSION['reset_email']]);

        // ✅ Clear reset_code so it can’t be reused
        $stmt = $conn->prepare("UPDATE customers SET reset_code = NULL WHERE email = ?");
        $stmt->execute([$_SESSION['reset_email']]);

        unset($_SESSION['reset_email'], $_SESSION['reset_code_verified']);

        $_SESSION['success'] = "Your password has been reset successfully.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Passwords do not match. Please try again!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Merz - Reset Password</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center vh-100 justify-content-center bg-light">
<div class="auth-card shadow-sm" style="width: 350px; border-radius: 15px; border: none;">
    <div class="card-body text-center p-4">

        <!-- ✅ Logo Section -->
        <div class="image-container mb-3"> 
            <img class="logo-img" src="../assets/img/logo.jpg" alt="Merz Logo"> 
        </div>

        <h4 class="mb-3 fw-bold">Reset Password</h4>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="input-group mb-3">
                <input class="form-control" type="password" name="password" placeholder="Enter new password" required>
            </div>
            <div class="input-group mb-3">
                <input class="form-control" type="password" name="confirm_password" placeholder="Confirm password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>

        <p class="mt-3"><a href="login.php">Return to Login</a></p>
    </div>
</div>
</body>
</html>
