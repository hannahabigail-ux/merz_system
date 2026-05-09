<?php
require_once __DIR__ . "/../includes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        try {
            $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
            $stmt->execute([$id]);

            header("Location: admin_dashboard.php?page=customers&success=" . urlencode("Customer ID #{$id} deleted successfully."));
            exit();
        } catch (Exception $e) {
            header("Location: admin_dashboard.php?page=customers&error=" . urlencode("Error deleting customer: " . $e->getMessage()));
            exit();
        }
    } else {
        header("Location: admin_dashboard.php?page=customers&error=" . urlencode("No customer ID provided."));
        exit();
    }
} else {
    header("Location: admin_dashboard.php?page=customers&error=" . urlencode("Invalid request method."));
    exit();
}
