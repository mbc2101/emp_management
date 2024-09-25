<?php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Include the database connection file
include 'db_connect.php'; // Make sure this file connects to your database

// Check if the subscription ID is provided
if (isset($_GET['subscriptionid'])) {
    $subscriptionId = (int) $_GET['subscriptionid']; // Cast to integer to prevent SQL injection

    // Prepare the SQL statement to update the subscription
    $sql = 'UPDATE subscriptions SET enabled = 0 WHERE subscriptionid = :subscriptionid';
    $stmt = $pdo->prepare($sql);
    
    // Execute the statement
    if ($stmt->execute(['subscriptionid' => $subscriptionId])) {
        // Redirect back to subscriptions list with a success message
        header('Location: subscriptions.php?message=Subscription disabled successfully.');
        exit;
    } else {
        // Redirect back with an error message
        header('Location: subscriptions.php?error=Could not disable the subscription.');
        exit;
    }
} else {
    // Redirect back with an error message if no subscription ID was provided
    header('Location: subscriptions.php?error=No subscription ID provided.');
    exit;
}
?>
