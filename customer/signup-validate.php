<?php
session_start();
require_once "../includes/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: signup.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM customers WHERE email=?");
    $stmt->execute([$_POST['email']]);
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Email already exists.";
        header("Location: signup.php");
        exit();
    }

    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO customers (full_name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['full_name'], $_POST['email'], $hashed]);

    $_SESSION['success'] = "Account created. Please log in.";
    header("Location: login.php");
    exit();
}
