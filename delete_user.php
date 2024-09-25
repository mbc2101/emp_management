<?php
session_start(); // Start the session

// Redirect to login if not logged in
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
include 'db_connect.php';
?>

<?php
if (isset($_GET['userid'])) {
    $userid = $_GET['userid'];

    // Delete user from database
    $stmt = $pdo->prepare("DELETE FROM users WHERE userid = ?");
    $stmt->execute([$userid]);

    // Redirect to users.php after deletion
    header('Location: users.php');
} else {
    die('User ID not provided');
}
?>
