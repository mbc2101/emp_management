<?php
session_start(); // Start the session

// Redirect to login if not logged in
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
include 'db_connect.php';

// Initialize plan variable
$plan = null;

// Fetch existing plan details if editing
if (isset($_GET['id'])) {
    $planId = $_GET['id'];
    $stmt = $pdo->prepare('SELECT * FROM plansdefinitions WHERE plandefinitionid = ?');
    $stmt->execute([$planId]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if plan exists
    if (!$plan) {
        echo "Error: Plan not found.";
        exit; // Stop further processing
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['planname'])) {
    // Get form data
    $planname = $_POST['planname'];
    $price = $_POST['price'];
    $maxmessages = $_POST['maxmessages'];
    $maxphotos = $_POST['maxphotos'];
    $officialplan = isset($_POST['officialplan']) ? 1 : 0; // Checkbox for official plan
    $enabled = isset($_POST['enabled']) ? 1 : 0; // Checkbox for enabled

    try {
        // Update existing plan
        $stmt = $pdo->prepare('UPDATE plansdefinitions SET planname = ?, price = ?, maxmessages = ?, maxphotos = ?, officialplan = ?, enabled = ? WHERE plandefinitionid = ?');
        $stmt->execute([$planname, $price, $maxmessages, $maxphotos, $officialplan, $enabled, $planId]);

        // Redirect back to the plan list
        header('Location: plans.php');
        exit;
    } catch (PDOException $e) {
        echo "Error updating plan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Plan</title>
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
<?php include 'navbar.php'; ?> <!-- Include the navbar -->

    <div class="container">
        <div class="form-container mx-auto">
            <h2 class="text-center">Edit Plan</h2>
            <form id="planForm" method="POST" onsubmit="event.preventDefault(); showPreview();">
                <div class="mb-3">
                    <label for="planname" class="form-label">Plan Name</label>
                    <input type="text" class="form-control" id="planname" name="planname" value="<?php echo htmlspecialchars($plan['planname']); ?>" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($plan['price']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="maxmessages" class="form-label">Max Messages</label>
                    <input type="number" class="form-control" id="maxmessages" name="maxmessages" value="<?php echo htmlspecialchars($plan['maxmessages']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="maxphotos" class="form-label">Max Photos</label>
                    <input type="number" class="form-control" id="maxphotos" name="maxphotos" value="<?php echo htmlspecialchars($plan['maxphotos']); ?>" required>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="officialplan" name="officialplan" <?php echo $plan['officialplan'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="officialplan">Official Plan</label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="enabled" name="enabled" <?php echo $plan['enabled'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="enabled">Enabled</label>
                </div>
                <button type="submit" class="btn btn-primary">Preview</button>
                <a href="plans.php" class="btn btn-secondary mt-2">Cancel</a>
            </form>

            <!-- Preview Section -->
            <div id="previewSection" class="preview-container d-none">
                <h5>Confirm Your Details</h5>
                <ul>
                    <li><strong>Plan Name:</strong> <span id="previewPlanName"></span></li>
                    <li><strong>Price:</strong> <span id="previewPrice"></span></li>
                    <li><strong>Max Messages:</strong> <span id="previewMaxMessages"></span></li>
                    <li><strong>Max Photos:</strong> <span id="previewMaxPhotos"></span></li>
                    <li><strong>Official Plan:</strong> <span id="previewOfficialPlan"></span></li>
                    <li><strong>Enabled:</strong> <span id="previewEnabled"></span></li>
                </ul>
                <button type="button" class="btn btn-secondary" onclick="goBack()">Back</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('planForm').submit();">Confirm</button>
                <input type="hidden" name="planname" id="confirmPlanName">
                <input type="hidden" name="price" id="confirmPrice">
                <input type="hidden" name="maxmessages" id="confirmMaxMessages">
                <input type="hidden" name="maxphotos" id="confirmMaxPhotos">
                <input type="hidden" name="officialplan" id="confirmOfficialPlan">
                <input type="hidden" name="enabled" id="confirmEnabled">
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show the preview section with form data
        function showPreview() {
            document.getElementById('previewPlanName').innerText = document.getElementById('planname').value;
            document.getElementById('previewPrice').innerText = document.getElementById('price').value;
            document.getElementById('previewMaxMessages').innerText = document.getElementById('maxmessages').value;
            document.getElementById('previewMaxPhotos').innerText = document.getElementById('maxphotos').value;
            document.getElementById('previewOfficialPlan').innerText = document.getElementById('officialplan').checked ? 'Yes' : 'No';
            document.getElementById('previewEnabled').innerText = document.getElementById('enabled').checked ? 'Yes' : 'No';

            // Show the preview section
            document.getElementById('previewSection').classList.remove('d-none');
            document.getElementById('planForm').classList.add('d-none');

            // Set hidden inputs for final submission
            document.getElementById('confirmPlanName').value = document.getElementById('planname').value;
            document.getElementById('confirmPrice').value = document.getElementById('price').value;
            document.getElementById('confirmMaxMessages').value = document.getElementById('maxmessages').value;
            document.getElementById('confirmMaxPhotos').value = document.getElementById('maxphotos').value;
            document.getElementById('confirmOfficialPlan').value = document.getElementById('officialplan').checked ? 1 : 0;
            document.getElementById('confirmEnabled').value = document.getElementById('enabled').checked ? 1 : 0;
        }

        // Go back to the form
        function goBack() {
            document.getElementById('previewSection').classList.add('d-none');
            document.getElementById('planForm').classList.remove('d-none');
        }
    </script>
</body>
</html>
