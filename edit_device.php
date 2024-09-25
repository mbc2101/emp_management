<?php
session_start(); // Start the session

// Redirect to login if not logged in
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
include 'db_connect.php';

// Fetch device types and status definitions for selection
$deviceTypes = $pdo->query('SELECT devicetypeid, brand FROM devicetypes')->fetchAll();
$statusDefinitions = $pdo->query('SELECT statusid, description FROM statusdefinitions')->fetchAll();

// Fetch existing device details if editing
if (isset($_GET['deviceid'])) {
    $deviceId = $_GET['deviceid'];
    $stmt = $pdo->prepare('SELECT * FROM devices WHERE deviceid = ?');
    $stmt->execute([$deviceId]);
    $device = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serialnumber'])) {
    // Get form data
    $serialnumber = $_POST['serialnumber'];
    $macaddress = $_POST['macaddress'];
    $devicetypeid = $_POST['devicetype'];
    $statusid = $_POST['status'];

    // Server-side MAC address validation
    if (strlen($macaddress) !== 12) {
        echo "Error: MAC Address must be exactly 12 characters long.";
        exit; // Optionally, redirect back to the form or show an error message
    }

    try {
        // Update device
        $stmt = $pdo->prepare('UPDATE devices SET serialnumber = ?, macaddress = ?, devicetypeid = ?, statusid = ? WHERE deviceid = ?');
        $stmt->execute([$serialnumber, $macaddress, $devicetypeid, $statusid, $deviceId]);

        // Redirect back to the device list
        header('Location: devices.php');
        exit;
    } catch (PDOException $e) {
        // Handle error - display error message or log it
        echo "Error updating device: " . $e->getMessage();
        // Optionally redirect back to the form or show an error page
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Device</title>
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
        .btn-primary, .btn-secondary {
            width: 100%;
        }
        .preview-container {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?> <!-- Inclure la barre de navigation -->

    <div class="container">
        <div class="form-container mx-auto">
            <h2 class="text-center"><i class="fas fa-edit"></i> Edit Device</h2>
            <form id="deviceForm" method="POST" onsubmit="event.preventDefault(); validateForm();">
                <!-- Device Details -->
                <div class="mb-3">
                    <label for="serialnumber" class="form-label">Serial Number</label>
                    <input type="text" class="form-control" id="serialnumber" name="serialnumber" value="<?php echo htmlspecialchars($device['serialnumber']); ?>" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="macaddress" class="form-label">MAC Address</label>
                    <input type="text" class="form-control" id="macaddress" name="macaddress" value="<?php echo htmlspecialchars($device['macaddress']); ?>" required maxlength="12">
                    <div class="error-message" id="macError"></div>
                </div>

                <!-- Device Type -->
                <div class="mb-3">
                    <label for="devicetype" class="form-label">Device Type</label>
                    <select class="form-control" id="devicetype" name="devicetype" required>
                        <?php foreach ($deviceTypes as $type): ?>
                            <option value="<?php echo $type['devicetypeid']; ?>" <?php echo ($device['devicetypeid'] == $type['devicetypeid']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['brand']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" name="status" required>
                        <?php foreach ($statusDefinitions as $status): ?>
                            <option value="<?php echo $status['statusid']; ?>" <?php echo ($device['statusid'] == $status['statusid']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($status['description']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Preview</button>
                <a href="devices.php" class="btn btn-secondary mt-2"><i class="fas fa-times"></i> Cancel</a>
            </form>

            <!-- Preview Section -->
            <div id="previewSection" class="preview-container d-none">
                <h5>Confirm Your Details</h5>
                <ul id="previewList">
                    <li><strong>Serial Number:</strong> <span id="previewSerialNumber"></span></li>
                    <li><strong>MAC Address:</strong> <span id="previewMacAddress"></span></li>
                    <li><strong>Device Type:</strong> <span id="previewDeviceType"></span></li>
                    <li><strong>Status:</strong> <span id="previewStatus"></span></li>
                </ul>
                <button type="button" class="btn btn-secondary" onclick="goBack()">Back</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('deviceForm').submit();">Confirm</button>
                <input type="hidden" name="serialnumber" id="confirmSerialNumber">
                <input type="hidden" name="macaddress" id="confirmMacAddress">
                <input type="hidden" name="devicetype" id="confirmDeviceType">
                <input type="hidden" name="status" id="confirmStatus">
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validate the form before submitting
        function validateForm() {
            const macAddress = document.getElementById('macaddress').value;
            const macError = document.getElementById('macError');
            macError.innerText = ''; // Clear previous error message

            if (macAddress.length !== 12) {
                macError.innerText = 'MAC Address must be exactly 12 characters long.';
                return; // Stop submission
            }

            // If validation passes, show preview
            showPreview();
        }

        // Show the preview section with form data
        function showPreview() {
            document.getElementById('previewSerialNumber').innerText = document.getElementById('serialnumber').value;
            document.getElementById('previewMacAddress').innerText = document.getElementById('macaddress').value;
            document.getElementById('previewDeviceType').innerText = document.getElementById('devicetype').options[document.getElementById('devicetype').selectedIndex].text;
            document.getElementById('previewStatus').innerText = document.getElementById('status').options[document.getElementById('status').selectedIndex].text;

            // Show the preview section
            document.getElementById('previewSection').classList.remove('d-none');
            document.getElementById('deviceForm').classList.add('d-none');

            // Set hidden inputs for final submission
            document.getElementById('confirmSerialNumber').value = document.getElementById('serialnumber').value;
            document.getElementById('confirmMacAddress').value = document.getElementById('macaddress').value;
            document.getElementById('confirmDeviceType').value = document.getElementById('devicetype').value;
            document.getElementById('confirmStatus').value = document.getElementById('status').value;
        }

        // Go back to the form
        function goBack() {
            document.getElementById('previewSection').classList.add('d-none');
            document.getElementById('deviceForm').classList.remove('d-none');
        }
    </script>
</body>
</html>
