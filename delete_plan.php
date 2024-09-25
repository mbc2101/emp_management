<?php
session_start(); // Start the session

// Redirect to login if not logged in
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
include 'db_connect.php';

// Check if the ID is set in the URL
if (isset($_GET['id'])) {
    $plandefinitionid = $_GET['id'];

    // Prepare and execute the delete statement
    $stmt = $pdo->prepare('DELETE FROM plansdefinitions WHERE plandefinitionid = ?');
    $stmt->execute([$plandefinitionid]);

    // Redirect back to the plans list
    header('Location: plans.php');
    exit;
} else {
    die('ID not specified.');
}
?>
