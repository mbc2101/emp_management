<?php
session_start(); // Start the session

// Redirect to login if not logged in
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
include 'db_connect.php';

// Check if ID is set
if (isset($_GET['id'])) {
    $kinsmanid = intval($_GET['id']); // Ensure id is an integer

    try {
        // Prepare delete statement
        $stmt = $pdo->prepare("DELETE FROM kinsmans WHERE kinsmanid = ?");
        $stmt->execute([$kinsmanid]);

        // Redirect to the kinsman list after successful deletion
        header('Location: kinsmans.php');
        exit;
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        exit;
    }
} else {
    echo "Kinsman ID not specified.";
    exit;
}
?>
