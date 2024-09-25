<?php
session_start(); // Start the session

// Redirect to login if not logged in
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
include 'db_connect.php';

// Check if device ID is provided
if (isset($_GET['deviceid'])) {
    $deviceid = $_GET['deviceid'];

    // Delete the device
    $stmt = $pdo->prepare('DELETE FROM devices WHERE deviceid = ?');
    $stmt->execute([$deviceid]);

    // Redirect back to the device list
    header('Location: devices.php');
    exit;
} else {
    // Redirect if no device ID is specified
    header('Location: devices.php');
    exit;
}
?>
