<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredcode = $_POST['code'] ?? '';
    $email = $_SESSION['email'] ?? null;

    if (!$email) {
        $_SESSION['error'] = "No Email session found. Please try again!";
        header("Location: forgot-password.php");
        exit();
    }

    // ✅ Use $conn and customers table
    $stmt = $conn->prepare("SELECT reset_code FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer) {
        if ($enteredcode == $customer['reset_code']) {
            // ✅ Clear reset_code immediately after successful verification
            $clear = $conn->prepare("UPDATE customers SET reset_code = NULL WHERE email = ?");
            $clear->execute([$email]);

            $_SESSION['success'] = "Code has been verified!";
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_code_verified'] = true;
            header("Location: reset-password.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid Code. Please try again!";
        }
    } else {
        $_SESSION['error'] = "No customer found with that email.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Merz - Reset Code</title>
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

        <h4 class="mb-3 fw-bold">Code Verification</h4>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success text-center"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger text-center"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Code Verification Form -->
        <form action="" method="post">
            <div class="input-group mb-3">
                <input class="form-control" type="text" name="code" placeholder="Enter Verification Code" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Verify Code</button>
        </form>

        <p class="mt-3" style="font-size: 14px;"> 
            <a class="nav-link-custom" href="forgot-password.php">Return to the Previous Page</a>
        </p>
    </div>
</div>
</body>
</html>
