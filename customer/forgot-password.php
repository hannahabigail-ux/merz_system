<?php
session_start();

// ✅ Correct paths (go up one level from customer/)
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    // ✅ Query customers table
    $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer) {
        // Generate reset code
        $reset_code = random_int(100000, 999999);

        // Save reset code in DB
        $update = $conn->prepare("UPDATE customers SET reset_code = ? WHERE email = ?");
        $update->execute([$reset_code, $email]);

        $_SESSION['email'] = $email;

        $mail = new PHPMailer(true);

        try {
            // ✅ Configure Gmail SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'hannahabigail2003@gmail.com'; // replace with your Gmail
            $mail->Password   = 'ywot fpiu gvyp gvqq';   // use Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('yourgmail@gmail.com', 'Merz Salon');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Password Reset Code";
            $mail->Body    = "<p>Hello, this is your password reset code: <b>{$reset_code}</b></p>";
            $mail->AltBody = "Hello. Use this code to reset your password: {$reset_code}";

            $mail->send();

            $_SESSION['success'] = "A verification code has been sent to your email.";
            header("Location: reset_code.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Message could not be sent. Mailer Error: " . $mail->ErrorInfo;
            header("Location: forgot-password.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "No customer found with that email.";
        header("Location: forgot-password.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Merz - Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center vh-100 justify-content-center bg-light">
    <div class="auth-card shadow-sm" style="width: 350px; border-radius: 15px; border: none;">
        <div class="card-body text-center p-4">        
            <!-- Logo Section -->
            <div class="image-container mb-3"> 
                <img class="logo-img" src="../assets/img/logo.jpg" alt="Merz Logo"> 
            </div>
            
            <h4 class="mb-3 fw-bold">Forgot Password</h4>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success text-center"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger text-center"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- Forgot Password Form -->
            <form action="forgot-password.php" method="POST">
                <div class="input-group mb-3">
                    <input class="form-control" type="email" name="email" placeholder="Enter email address" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Send Verification Code</button>
            </form>
            
            <!-- Navigation Link -->
            <p class="mt-3" style="font-size: 14px;"> 
                <a class="nav-link-custom" href="login.php">Return to Log In Page</a>
            </p>
        </div>
    </div>
</body>
</html>
