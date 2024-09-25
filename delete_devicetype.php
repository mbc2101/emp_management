<?php
session_start(); // Start the session

// Redirect to login if not logged in
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
include 'db_connect.php';

// Check if the device type ID is provided
if (isset($_GET['devicetypeid'])) {
    $deviceTypeId = $_GET['devicetypeid'];

    // Prepare the delete statement
    $deleteStmt = $pdo->prepare('DELETE FROM devicetypes WHERE devicetypeid = ?');
    
    // Execute the delete statement
    if ($deleteStmt->execute([$deviceTypeId])) {
        // Redirect to the device types list with a success message
        header('Location: devicetypes.php?message=Device type deleted successfully.');
    } else {
        // Redirect to the device types list with an error message
        header('Location: devicetypes.php?message=Error deleting device type.');
    }
} else {
    // Redirect to the device types list if no ID was provided
    header('Location: devicetypes.php?message=No device type ID provided.');
}
exit;
?>
