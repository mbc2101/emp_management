<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

include 'db_connect.php';

// Get the search term if provided
$searchTerm = isset($_POST['search']) ? $_POST['search'] : '';

// Prepare the SQL query with a search filter for both name and email
$sql = '
    SELECT k.*, GROUP_CONCAT(u.username SEPARATOR ", ") AS users
    FROM kinsmans k
    LEFT JOIN responsiblefor rf ON k.kinsmanid = rf.kinsmanid
    LEFT JOIN users u ON rf.userid = u.userid
    WHERE k.name LIKE :searchTerm OR k.email LIKE :searchTerm
    GROUP BY k.kinsmanid
';
$stmt = $pdo->prepare($sql);
$stmt->execute(['searchTerm' => "%$searchTerm%"]);
$kinsmans = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kinsmans Management</title>
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
    <script>
        function autoSubmit() {
            document.getElementById('searchForm').submit();
        }
    </script>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-4">Kinsmans List</h2>
            <a href="add_kinsman.php" class="btn btn-success"><i class="fas fa-user-plus"></i> Add Kinsman</a>
        </div>

        <!-- Search form -->
        <form method="POST" id="searchForm" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by name, email" value="<?php echo htmlspecialchars($searchTerm); ?>" oninput="autoSubmit()" id="searchInput" autofocus>
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
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kinsmans as $kinsman): ?>
                <tr>
                    <td><?php echo $kinsman['kinsmanid']; ?></td>
                    <td>
                        <a href="#" data-toggle="modal" data-target="#usersModal<?php echo $kinsman['kinsmanid']; ?>">
                            <?php echo $kinsman['name']; ?>
                        </a>
                    </td>
                    <td><?php echo $kinsman['email']; ?></td>
                    <td class="actions-btns">
                        <a href="edit_kinsman.php?kinsmanid=<?php echo $kinsman['kinsmanid']; ?>" class="btn btn-warning btn-sm btn-custom">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="delete_kinsman.php?id=<?php echo $kinsman['kinsmanid']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this kinsman?');">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>

                <!-- Modal for displaying users under the responsibility -->
                <div class="modal fade" id="usersModal<?php echo $kinsman['kinsmanid']; ?>" tabindex="-1" role="dialog" aria-labelledby="usersModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="usersModalLabel">Users under <?php echo $kinsman['name']; ?></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Users:</strong> <?php echo $kinsman['users'] ?: 'No users found'; ?></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
