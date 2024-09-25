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
    <title>Users Management</title>
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
            <h2 class="mb-4">Users List</h2>
            <a href="add_user.php" class="btn btn-success"><i class="fas fa-user-plus"></i> Add User</a>
        </div>

        <input type="text" id="searchInput" class="form-control mb-4" placeholder="Search for users..."autofocus>
                <script>
    // Move cursor to the end of the input value after page loads
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.getElementById('searchInput');
        if (input) {
            var value = input.value;
            input.focus();
            input.setSelectionRange(value.length, value.length);  // Moves the cursor to the end
        }
    });
</script>

        <table class="table table-bordered table-hover table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Reference</th>
                    <th>Subscription Plan</th> <!-- New Column -->
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <?php
                // Fetch users from the database
                $stmt = $pdo->query('
                    SELECT u.userid, 
                           u.username, 
                           u.userreference,
                           CONCAT(dt.brand, " ", dt.model) AS devices,
                           p.planname
                    FROM users u
                    LEFT JOIN ownership o ON u.userid = o.userid
                    LEFT JOIN devices d ON o.deviceid = d.deviceid
                    LEFT JOIN devicetypes dt ON d.devicetypeid = dt.devicetypeid  
                    LEFT JOIN subscriptions s ON u.userid = s.userid
                    LEFT JOIN plansdefinitions p ON s.plandefinitionid = p.plandefinitionid
                    GROUP BY u.userid
                ');

                while ($row = $stmt->fetch()) {
                    echo "<tr>
                        <td>{$row['userid']}</td>
                        <td><a href='#' data-toggle='modal' data-target='#userDevicesModal{$row['userid']}'>{$row['username']}</a></td>
                        <td>{$row['userreference']}</td>
                        <td>{$row['planname']}</td> <!-- Display Plan Name -->
                        <td class='actions-btns'>
                            <a href='edit_user.php?userid={$row['userid']}' class='btn btn-warning btn-sm btn-custom'>
                                <i class='fas fa-edit'></i> Edit
                            </a>
                            <a href='delete_user.php?userid={$row['userid']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Are you sure you want to delete this user?');\">
                                <i class='fas fa-trash'></i> Delete
                            </a>
                            <button class='btn btn-link btn-custom' data-toggle='modal' data-target='#userParamsModal{$row['userid']}' title='Edit Parameters'>
                                <i class='fas fa-cog'></i>
                            </button>
                        </td>
                    </tr>";

                    // Modal for devices and subscription
                    echo "
                    <div class='modal fade' id='userDevicesModal{$row['userid']}' tabindex='-1' role='dialog' aria-labelledby='userDevicesModalLabel' aria-hidden='true'>
                        <div class='modal-dialog' role='document'>
                            <div class='modal-content'>
                                <div class='modal-header'>
                                    <h5 class='modal-title' id='userDevicesModalLabel'>Devices and Subscription: {$row['username']}</h5>
                                    <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                                        <span aria-hidden='true'>&times;</span>
                                    </button>
                                </div>
                                <div class='modal-body'>
                                    <p><strong>Device(s):</strong> {$row['devices']}</p>
                                    <p><strong>Subscription Plan:</strong> {$row['planname']}</p>
                                </div>
                                <div class='modal-footer'>
                                    <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                                </div>
                            </div>
                        </div>
                    </div>";

                    // Fetch parameters for the user
                    $params_stmt = $pdo->prepare('
                        SELECT * FROM parameters WHERE userid = :userid
                    ');
                    $params_stmt->execute(['userid' => $row['userid']]);
                    $params = $params_stmt->fetch();

                    // Modal for user parameters
                    echo "
                    <div class='modal fade' id='userParamsModal{$row['userid']}' tabindex='-1' role='dialog' aria-labelledby='userParamsModalLabel' aria-hidden='true'>
                        <div class='modal-dialog' role='document'>
                            <div class='modal-content'>
                                <form action='update_parameters.php' method='POST'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='userParamsModalLabel'>User Parameters: {$row['username']}</h5>
                                        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                                            <span aria-hidden='true'>&times;</span>
                                        </button>
                                    </div>
                                    <div class='modal-body'>
                                        <input type='hidden' name='userid' value='{$row['userid']}'>
                                        <div class='form-group'>
                                            <label for='language'>Language:</label>
                                            <input type='text' class='form-control' name='language' value='" . ($params ? $params['language'] : '') . "'>
                                        </div>
                                        <div class='form-group'>
                                            <label for='morning'>Morning:</label>
                                            <input type='text' class='form-control' name='morning' value='" . ($params ? $params['morning'] : '') . "'>
                                        </div>
                                        <div class='form-group'>
                                            <label for='noon'>Noon:</label>
                                            <input type='text' class='form-control' name='noon' value='" . ($params ? $params['noon'] : '') . "'>
                                        </div>
                                        <div class='form-group'>
                                            <label for='afternoon'>Afternoon:</label>
                                            <input type='text' class='form-control' name='afternoon' value='" . ($params ? $params['afternoon'] : '') . "'>
                                        </div>
                                        <div class='form-group'>
                                            <label for='evening'>Evening:</label>
                                            <input type='text' class='form-control' name='evening' value='" . ($params ? $params['evening'] : '') . "'>
                                        </div>
                                        <div class='form-group'>
                                            <label for='night'>Night:</label>
                                            <input type='text' class='form-control' name='night' value='" . ($params ? $params['night'] : '') . "'>
                                        </div>
                                    </div>
                                    <div class='modal-footer'>
                                        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                                        <button type='submit' class='btn btn-primary'>Save Changes</button>
                                    </div>
                                </form>
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
    <script>
        // Search function
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#userTableBody tr');

            rows.forEach(row => {
                const username = row.cells[1].textContent.toLowerCase();
                if (username.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
