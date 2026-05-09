<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

if (!isset($_SESSION['customer'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $email     = $_SESSION['customer']; // session stores the customer email

    // ✅ Update only the full name (email stays the same)
    $stmt = $conn->prepare("UPDATE customers SET full_name=:fname WHERE email=:email");
    $stmt->execute([
        ':fname' => $full_name,
        ':email' => $email
    ]);

    // Redirect back with success flag
    header("Location: profile.php?updated=1");
    exit();
}
?>
