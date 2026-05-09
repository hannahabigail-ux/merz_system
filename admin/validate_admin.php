<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, full_name, email, password, role FROM admins WHERE email=? LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        // ✅ Set consistent session variables
        $_SESSION['admin_id']   = $admin['id'];          // <-- Add this
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin']      = $admin['email'];       // keep if you use it elsewhere
        $_SESSION['role']       = $admin['role'];

        header("Location: admin_dashboard.php?page=home");
        exit();
    } else {
        $_SESSION['error'] = "Invalid login credentials.";
        header("Location: admin_login.php");
        exit();
    }
}
