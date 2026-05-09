<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM customers WHERE email=? LIMIT 1");
    $stmt->execute([$email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer && password_verify($password, $customer['password'])) {
        // ✅ Save session
        $_SESSION['customer'] = $customer['email'];

        // ✅ Redirect to dashboard (Home)
        header("Location: customer_dashboard.php");
        exit();
    } else {
        // ❌ Invalid login
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: login.php");
        exit();
    }
}
?>
