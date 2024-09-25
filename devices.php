<?php
session_start(); // Start the session

// Redirect to login if not logged in
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
include 'db_connect.php';

// Get the search term if provided
$searchTerm = isset($_POST['search']) ? $_POST['search'] : '';

// Prepare the SQL query with a search filter
$sql = '
    SELECT d.deviceid, dt.brand, dt.model, dt.size, dt.memory, dt.os, dt.sim, dt.resolution, 
           d.serialnumber, d.macaddress, d.statusid, s.description AS description,
           GROUP_CONCAT(u.username SEPARATOR ", ") AS users
    FROM devices d
    LEFT JOIN devicetypes dt ON d.devicetypeid = dt.devicetypeid
    LEFT JOIN ownership o ON d.deviceid = o.deviceid
    LEFT JOIN users u ON o.userid = u.userid
    LEFT JOIN statusdefinitions s ON d.statusid = s.statusid  -- Join with statusdefinitions table
    WHERE o.unassigned_at is null and  (dt.brand LIKE :searchTerm OR dt.model LIKE :searchTerm OR d.serialnumber LIKE :searchTerm )
    GROUP BY d.deviceid, dt.brand, dt.model, dt.size, dt.memory, dt.os, dt.sim, dt.resolution, 
             d.serialnumber, d.macaddress, d.statusid, s.description
';

$stmt = $pdo->prepare($sql);
$stmt->execute(['searchTerm' => "%$searchTerm%"]);
$devices = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devices Management</title>
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
    <script>
        function autoSubmit() {
            document.getElementById('searchForm').submit();
        }
    </script>
</head>
<body>
    <?php include 'navbar.php'; ?> <!-- Include the navbar -->

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-4">Devices List</h2>
            <a href="add_device.php" class="btn btn-success"><i class="fas fa-plus"></i> Add Device</a>
        </div>

        <!-- Search form -->
        <form method="POST" id="searchForm" class="mb-3">
            <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by brand, model, or serial number" value="<?php echo htmlspecialchars($searchTerm); ?>" oninput="autoSubmit()" id="searchInput" autofocus>
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


        </div>
        </form>

        <table class="table table-bordered table-hover table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Device ID</th>
                    <th>Device Type</th>
                    <th>Serial Number</th>
                    <th>MAC Address</th>
                    <th>Status</th> <!-- Updated to 'Status' -->
                    <th>User Assignment</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($devices as $row): ?>
                    <tr>
                    <td><?php echo $row['deviceid']; ?></td>
<td><a href="#" data-toggle="modal" data-target="#deviceDetailsModal<?php echo $row['deviceid']; ?>"><?php echo $row['brand'] . ' ' . $row['model']; ?></a></td>
                        <td><?php echo $row['serialnumber']; ?></td>
                        <td><?php echo $row['macaddress']; ?></td>
                        <td><?php echo $row['description']; ?></td> <!-- Updated to display description -->
                        <td><?php echo $row['users']; ?></td>
                        <td class='text-center'>
                            <a href='edit_device.php?deviceid=<?php echo $row['deviceid']; ?>' class='btn btn-warning btn-sm btn-custom'>
                                <i class='fas fa-edit'></i> Edit
                            </a>
                            <a href='delete_device.php?deviceid=<?php echo $row['deviceid']; ?>' class='btn btn-danger btn-sm' onclick="return confirm('Are you sure you want to delete this device?');">
                                <i class='fas fa-trash'></i> Delete
                            </a>
                        </td>
                    </tr>

                    <!-- Modal for device details -->
                    <div class='modal fade' id='deviceDetailsModal<?php echo $row['deviceid']; ?>' tabindex='-1' role='dialog' aria-labelledby='deviceDetailsModalLabel' aria-hidden='true'>
                        <div class='modal-dialog' role='document'>
                            <div class='modal-content'>
                                <div class='modal-header'>
                                    <h5 class='modal-title' id='deviceDetailsModalLabel'>Device Details: <?php echo $row['deviceid']; ?></h5>
                                    <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                                        <span aria-hidden='true'>&times;</span>
                                    </button>
                                </div>
                                <div class='modal-body'>
                                    <p><strong>Device ID:</strong> <?php echo $row['deviceid']; ?></p>
                                    <p><strong>Brand:</strong> <?php echo $row['brand']; ?></p>
                                    <p><strong>Model:</strong> <?php echo $row['model']; ?></p>
                                    <p><strong>Size:</strong> <?php echo $row['size']; ?></p>
                                    <p><strong>Memory:</strong> <?php echo $row['memory']; ?></p>
                                    <p><strong>OS:</strong> <?php echo $row['os']; ?></p>
                                    <p><strong>SIM:</strong> <?php echo $row['sim']; ?></p>
                                    <p><strong>Resolution:</strong> <?php echo $row['resolution']; ?></p>
                                    <p><strong>Serial Number:</strong> <?php echo $row['serialnumber']; ?></p>
                                    <p><strong>MAC Address:</strong> <?php echo $row['macaddress']; ?></p>
                                    <p><strong>Status:</strong> <?php echo $row['description']; ?></p> <!-- Updated to show status description -->
                                    <p><strong>User Assignment:</strong> <?php echo $row['users']; ?></p>
                                </div>
                                <div class='modal-footer'>
                                    <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
