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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Management System</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?> <!-- Include the navbar -->

    <div class="container mt-4">
        <h1 class="mb-4">Welcome to the Management Dashboard</h1>
        <p>Select a section from the menu to start managing data.</p>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Overview</h5>
                <p class="card-text">Use the navigation menu to access Users, Devices, Kinsmans, and Subscriptions.</p>
                <a href="users.php" class="btn btn-primary"><i class="fas fa-users"></i> Manage Users</a>
                <a href="devices.php" class="btn btn-secondary"><i class="fas fa-desktop"></i> Manage Devices</a>
                <a href="kinsmans.php" class="btn btn-info"><i class="fas fa-users-cog"></i> Manage Kinsmans</a>
                <a href="subscriptions.php" class="btn btn-warning"><i class="fas fa-list-alt"></i> Manage Subscriptions</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
