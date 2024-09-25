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

include 'db_connect.php'; // Assumed you have a file to connect to the database

// Fetch subscriptions from the database
$sql = '
    SELECT s.subscriptionid, s.userid, u.username, s.plandefinitionid, p.planname, s.startdate, s.enddate, s.enabled, s.updated_at
    FROM subscriptions s
    JOIN users u ON s.userid = u.userid
    JOIN plansdefinitions p ON s.plandefinitionid = p.plandefinitionid
';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$subscriptions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscriptions List</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    
</head>
<body>
    <?php include 'navbar.php'; // Assuming you have a navbar ?>

    <div class="container mt-4">
        <h2 class="mb-4">Subscriptions List</h2>
        <table class="table table-bordered table-hover table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Plan</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Enabled</th>
                    <th>Last Updated</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscriptions as $subscription): ?>
                <tr>
                    <td><?php echo $subscription['subscriptionid']; ?></td>
                    <td><?php echo htmlspecialchars($subscription['username']); ?></td>
                    <td><?php echo htmlspecialchars($subscription['planname']); ?></td>
                    <td><?php echo $subscription['startdate']; ?></td>
                    <td><?php echo $subscription['enddate']; ?></td>
                    <td><?php echo $subscription['enabled'] ? 'Yes' : 'No'; ?></td>
                    <td><?php echo $subscription['updated_at']; ?></td>
                    <td class="text-center">
                        <?php if ($subscription['enabled']): ?>
                            <a href="disable_subscription.php?subscriptionid=<?php echo $subscription['subscriptionid']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to disable this subscription?');">
                                Disable
                            </a>
                        <?php else: ?>
                            <a href="enable_subscription.php?subscriptionid=<?php echo $subscription['subscriptionid']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to enable this subscription?');">
                                Enable
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
