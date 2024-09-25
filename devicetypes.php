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
    <title>Device Types Management</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-custom {
            display: inline-block;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?> <!-- Include the navbar -->

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-4">Device Types List</h2>
            <a href="add_devicetype.php" class="btn btn-success"><i class="fas fa-plus"></i> Add Device Type</a>
        </div>

        <table class="table table-bordered table-hover table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Device Type ID</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Size</th>
                    <th>Memory</th>
                    <th>OS</th>
                    <th>SIM</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch device types from the database
                $stmt = $pdo->query('SELECT * FROM devicetypes');

                while ($row = $stmt->fetch()) {
                    // Check if there are any devices for the current device type
                    $devicetypeid = $row['devicetypeid'];
                    $deviceCheckStmt = $pdo->prepare('SELECT COUNT(*) FROM devices WHERE devicetypeid = ?');
                    $deviceCheckStmt->execute([$devicetypeid]);
                    $deviceCount = $deviceCheckStmt->fetchColumn();

                    echo "<tr>
                        <td>{$row['devicetypeid']}</td>
                        <td>{$row['brand']}</td>
                        <td>{$row['model']}</td>
                        <td>{$row['size']}</td>
                        <td>{$row['memory']}</td>
                        <td>{$row['os']}</td>
                        <td>" . ($row['sim'] ? 'Yes' : 'No') . "</td>
                        <td class='text-center'>
                            <a href='edit_devicetype.php?devicetypeid={$row['devicetypeid']}' class='btn btn-warning btn-sm btn-custom'>
                                <i class='fas fa-edit'></i> Edit
                            </a>";
                    
                    // Only display the delete button if there are no linked devices
                    if ($deviceCount == 0) {
                        echo "<a href='delete_devicetype.php?devicetypeid={$row['devicetypeid']}' class='btn btn-danger btn-sm btn-custom'
                                onclick=\"return confirm('Are you sure you want to delete this device type?');\">
                                <i class='fas fa-trash'></i> Delete
                            </a>";
                    }

                    echo "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
