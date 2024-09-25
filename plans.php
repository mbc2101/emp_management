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
    <title>Plans Management</title>
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
        .actions-btns {
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?> <!-- Include the navbar -->

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-4">Plans List</h2>
            <a href="add_plan.php" class="btn btn-success"><i class="fas fa-plus"></i> Add Plan</a>
        </div>

        <table class="table table-bordered table-hover table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Plan ID</th>
                    <th>Plan Name</th>
                    <th>Price</th>
                    <th>Max Messages</th>
                    <th>Max Photos</th>
                    <th>Official Plan</th>
                    <th>Enabled</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch plans from the database
                $stmt = $pdo->query('SELECT * FROM plansdefinitions');

                while ($row = $stmt->fetch()) {
                    echo "<tr>
                        <td>{$row['plandefinitionid']}</td>
                        <td>{$row['planname']}</td>
                        <td>{$row['price']}</td>
                        <td>{$row['maxmessages']}</td>
                        <td>{$row['maxphotos']}</td>
                        <td>" . ($row['officialplan'] ? 'Yes' : 'No') . "</td>
                        <td>" . ($row['enabled'] ? 'Yes' : 'No') . "</td>
                        <td class='actions-btns'>
                            <a href='edit_plan.php?id={$row['plandefinitionid']}' class='btn btn-warning btn-sm btn-custom'>
                                <i class='fas fa-edit'></i> Edit
                            </a>
                            <a href='delete_plan.php?id={$row['plandefinitionid']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Are you sure you want to delete this plan?');\">
                                <i class='fas fa-trash'></i> Delete
                            </a>
                            <button class='btn btn-link btn-custom' data-toggle='modal' data-target='#planDetailsModal{$row['plandefinitionid']}'>
                                <i class='fas fa-info-circle'></i>
                            </button>
                        </td>
                    </tr>";

                    // Modal for plan details
                    echo "
                    <div class='modal fade' id='planDetailsModal{$row['plandefinitionid']}' tabindex='-1' role='dialog' aria-labelledby='planDetailsModalLabel' aria-hidden='true'>
                        <div class='modal-dialog' role='document'>
                            <div class='modal-content'>
                                <div class='modal-header'>
                                    <h5 class='modal-title' id='planDetailsModalLabel'>Plan Details: {$row['planname']}</h5>
                                    <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                                        <span aria-hidden='true'>&times;</span>
                                    </button>
                                </div>
                                <div class='modal-body'>
                                    <p><strong>Plan ID:</strong> {$row['plandefinitionid']}</p>
                                    <p><strong>Price:</strong> {$row['price']}</p>
                                    <p><strong>Max Messages:</strong> {$row['maxmessages']}</p>
                                    <p><strong>Max Photos:</strong> {$row['maxphotos']}</p>
                                    <p><strong>Official Plan:</strong> " . ($row['officialplan'] ? 'Yes' : 'No') . "</p>
                                    <p><strong>Enabled:</strong> " . ($row['enabled'] ? 'Yes' : 'No') . "</p>
                                </div>
                                <div class='modal-footer'>
                                    <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                                </div>
                            </div>
                        </div>
                    </div>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
