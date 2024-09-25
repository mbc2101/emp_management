<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
include 'db_connect.php';

// Fetch the list of users
$users = $pdo->query("SELECT userid, username FROM users")->fetchAll(PDO::FETCH_ASSOC);

// Fetch current assignments, including the unassigned timestamp
$assignments = $pdo->query("
    SELECT o.deviceid, o.userid, u.username, o.unassigned_at
    FROM ownership o
    JOIN users u ON o.userid = u.userid
")->fetchAll(PDO::FETCH_ASSOC);

// Create an associative array to track assigned devices
$assignedDevices = [];
foreach ($assignments as $assignment) {
    $assignedDevices[$assignment['deviceid']] = $assignment['userid'];
}

// Fetch available devices (exclude already assigned ones)
$devices = $pdo->query("
    SELECT deviceid, serialnumber 
    FROM devices 
    WHERE statusid = 1 AND deviceid NOT IN (SELECT deviceid FROM ownership WHERE unassigned_at IS NULL)
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Assignment to User</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        #div1 {
            width: 350px;
            height: 70px;
            padding: 10px;
            border: 2px dashed #007bff;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            transition: background-color 0.3s;
        }
        #div1:hover {
            background-color: #e9ecef;
        }
        .device {
            padding: 10px;
            margin: 5px;
            border: 1px solid #007bff;
            border-radius: 5px;
            cursor: move;
            background-color: #ffffff;
            transition: transform 0.2s;
        }
        .device:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .assigned {
            background-color: #d4edda; /* Light green background */
            border-color: #c3e6cb; /* Green border */
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
    <script>
        function allowDrop(ev) {
            ev.preventDefault();
        }

        function drag(ev) {
            ev.dataTransfer.setData("text", ev.target.id);
        }

        function drop(ev, userId) {
            ev.preventDefault();
            var data = ev.dataTransfer.getData("text");
            var deviceId = document.getElementById(data).dataset.deviceid;

            // AJAX request to assign the device
            $.ajax({
                url: 'ajax_assign_unassign.php',
                method: 'POST',
                data: { action: 'assign', userid: userId, deviceid: deviceId },
                success: function(response) {
                    // Reload the page to fetch updated assignments
                    location.reload();
                },
                error: function() {
                    alert('Error assigning device.');
                }
            });
        }

        function unassignDevice(deviceid, userid) {
            // AJAX request to unassign the device
            $.ajax({
                url: 'ajax_assign_unassign.php',
                method: 'POST',
                data: { action: 'unassign', userid: userid, deviceid: deviceid },
                success: function(response) {
                    // Reload the page to fetch updated assignments
                    location.reload();
                },
                error: function() {
                    alert('Error unassigning device.');
                }
            });
        }
    </script>
</head>
<body>
<?php include 'navbar.php'; ?> <!-- Inclure la barre de navigation -->


<div class="container mt-5">
    <h2 class="text-center">Assign a Device to a User</h2>
    <p class="text-center">Drag and drop the device into the user's box to assign it.</p>

    <div class="row">
        <!-- Display users -->
        <?php foreach ($users as $user): ?>
            <div class="col-md-4">
                <div id="div1" ondrop="drop(event, <?php echo $user['userid']; ?>)" ondragover="allowDrop(event)">
                    <p class="font-weight-bold"><?php echo $user['username']; ?></p>
                    <div>
                        <?php
                        // Check if this user has any assigned devices (only those that are not unassigned)
                        $userAssignedDevices = array_filter($assignments, function($assignment) use ($user) {
                            return $assignment['userid'] == $user['userid'] && !$assignment['unassigned_at']; // Filter out unassigned
                        });
                        if ($userAssignedDevices) {
                            echo "<div class='assigned'>";
                            foreach ($userAssignedDevices as $device) {
                                echo "Assigned Device ID: " . $device['deviceid'] . " ";
                                echo "<button class='btn btn-danger btn-sm' onclick='unassignDevice(" . $device['deviceid'] . ", " . $user['userid'] . ")'>Unassign</button><br>";
                            }
                            echo "</div>";
                        } else {
                            echo "No devices assigned.";
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Display devices -->
    <h3>Available Devices</h3>
    <div class="row">
        <?php foreach ($devices as $device): ?>
            <div class="col-md-4">
                <div class="device" id="device<?php echo $device['deviceid']; ?>" data-deviceid="<?php echo $device['deviceid']; ?>" draggable="true" ondragstart="drag(event)">
                    Device ID: <?php echo $device['deviceid']; ?>, Serial Number: <?php echo $device['serialnumber']; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
