<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

if (!isset($_SESSION['customer'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['customer'];
$stmt = $conn->prepare("SELECT id FROM customers WHERE email=? LIMIT 1");
$stmt->execute([$email]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);
$customer_id = $customer['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $customer_id) {
    $booking_id = $_POST['booking_id'];
    $service_id = $_POST['service_id'];
    $rating     = $_POST['rating'];
    $comments   = $_POST['comments'];

    // ✅ Prevent duplicate feedback for the same booking
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM feedback 
                                 WHERE customer_id=? AND booking_id=?");
    $checkStmt->execute([$customer_id, $booking_id]);
    $exists = $checkStmt->fetchColumn();

    if ($exists) {
        header("Location: booking.php?feedback_exists=1");
        exit();
    }

    // ✅ Insert feedback with booking_id
    $stmt = $conn->prepare("INSERT INTO feedback (customer_id, service_id, booking_id, rating, comments, created_at)
                            VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$customer_id, $service_id, $booking_id, $rating, $comments]);

    header("Location: booking.php?feedback=1");
    exit();
}
?>
