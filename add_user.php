<?php
// Enable error reporting for debugging

session_start(); // Start the session

// Redirect to login if not logged in
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
include 'db_connect.php';

// Fetch kinsmans, devices, and subscription plans for selection
$kinsmans = $pdo->query('SELECT kinsmanid, name FROM kinsmans')->fetchAll();
$devices = $pdo->query('SELECT deviceid, serialnumber FROM devices')->fetchAll();
$plans = $pdo->query('SELECT plandefinitionid, planname FROM plansdefinitions')->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['username'];
    $userreference = $_POST['userreference'];
    $language = $_POST['language'];
    $enabled = isset($_POST['enabled']) ? 1 : 0;
    $kinsman_ids = $_POST['kinsmans'];
    $device_ids = $_POST['devices'];
    $plan_id = $_POST['subscription'];

    // Insert new user
    $stmt = $pdo->prepare('INSERT INTO users (username, userreference, language, enabled) VALUES (?, ?, ?, ?)');
    $stmt->execute([$username, $userreference, $language, $enabled]);
    $userid = $pdo->lastInsertId();

    // Insert responsible kinsmans
    foreach ($kinsman_ids as $kinsman_id) {
        $pdo->prepare('INSERT INTO responsiblefor (userid, kinsmanid) VALUES (?, ?)')->execute([$userid, $kinsman_id]);
    }

    // Insert device ownership
    foreach ($device_ids as $device_id) {
        $pdo->prepare('INSERT INTO ownership (userid, deviceid) VALUES (?, ?)')->execute([$userid, $device_id]);
    }

    // Insert subscription plan
    $pdo->prepare('INSERT INTO subscriptions (userid, plandefinitionid) VALUES (?, ?)')->execute([$userid, $plan_id]);

    // Redirect back to the user list
    header('Location: users.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        .form-container {
            margin-top: 50px;
            max-width: 800px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: bold;
        }

        .btn-primary,
        .btn-secondary {
            width: 100%;
        }

        .preview-container {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
    </style>
</head>

<body>
<?php include 'navbar.php'; ?> <!-- Inclure la barre de navigation -->

    <div class="container">
        <div class="form-container mx-auto">
            <h2 class="text-center"><i class="fas fa-user-plus"></i> Add New User</h2>
            <form id="userForm" method="POST" onsubmit="event.preventDefault(); showPreview();">
                <!-- User Details -->
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="userreference" class="form-label">User Reference</label>
                    <input type="text" class="form-control" id="userreference" name="userreference" required>
                </div>
                <div class="mb-3">
                    <label for="language" class="form-label">Language</label>
                    <input type="text" class="form-control" id="language" name="language" required>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="enabled" name="enabled">
                    <label class="form-check-label" for="enabled">Enabled</label>
                </div>

                <!-- Kinsmans (Multi-select) -->
                <div class="mb-3">
                    <label for="kinsmans" class="form-label">Kinsmans</label>
                    <select multiple class="form-control" id="kinsmans" name="kinsmans[]">
                        <?php foreach ($kinsmans as $kinsman): ?>
                            <option value="<?php echo $kinsman['kinsmanid']; ?>">
                                <?php echo $kinsman['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Devices (Multi-select) -->
                <div class="mb-3">
                    <label for="devices" class="form-label">Devices</label>
                    <select multiple class="form-control" id="devices" name="devices[]">
                        <?php foreach ($devices as $device): ?>
                            <option value="<?php echo $device['deviceid']; ?>">
                                <?php echo $device['serialnumber']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Subscription Plan -->
                <div class="mb-3">
                    <label for="subscription" class="form-label">Subscription Plan</label>
                    <select class="form-control" id="subscription" name="subscription">
                        <?php foreach ($plans as $plan): ?>
                            <option value="<?php echo $plan['plandefinitionid']; ?>">
                                <?php echo $plan['planname']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Preview</button>
                <a href="users.php" class="btn btn-secondary mt-2"><i class="fas fa-times"></i> Cancel</a>
            </form>

            <!-- Preview Section -->
            <div id="previewSection" class="preview-container d-none">
                <h5>Confirm Your Details</h5>
                <ul id="previewList">
                    <li><strong>Username:</strong> <span id="previewUsername"></span></li>
                    <li><strong>User Reference:</strong> <span id="previewUserReference"></span></li>
                    <li><strong>Language:</strong> <span id="previewLanguage"></span></li>
                    <li><strong>Enabled:</strong> <span id="previewEnabled"></span></li>
                    <li><strong>Kinsmans:</strong> <span id="previewKinsmans"></span></li>
                    <li><strong>Devices:</strong> <span id="previewDevices"></span></li>
                    <li><strong>Subscription Plan:</strong> <span id="previewSubscription"></span></li>
                </ul>
                <button type="button" class="btn btn-secondary" onclick="goBack()">Back</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('userForm').submit();">Confirm</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show the preview section with form data
        function showPreview() {
            document.getElementById('previewUsername').innerText = document.getElementById('username').value;
            document.getElementById('previewUserReference').innerText = document.getElementById('userreference').value;
            document.getElementById('previewLanguage').innerText = document.getElementById('language').value;
            document.getElementById('previewEnabled').innerText = document.getElementById('enabled').checked ? 'Yes' : 'No';

            const kinsmans = [...document.getElementById('kinsmans').selectedOptions].map(opt => opt.text).join(', ');
            document.getElementById('previewKinsmans').innerText = kinsmans;

            const devices = [...document.getElementById('devices').selectedOptions].map(opt => opt.text).join(', ');
            document.getElementById('previewDevices').innerText = devices;

            const subscription = document.getElementById('subscription').selectedOptions[0].text;
            document.getElementById('previewSubscription').innerText = subscription;

            // Show the preview section
            document.getElementById('previewSection').classList.remove('d-none');
            document.getElementById('userForm').classList.add('d-none');
        }

        // Go back to the form
        function goBack() {
            document.getElementById('previewSection').classList.add('d-none');
            document.getElementById('userForm').classList.remove('d-none');
        }
    </script>
</body>

</html>
