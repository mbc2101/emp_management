<?php
session_start(); // Start the session

// Redirect to login if not logged in
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
include 'db_connect.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kinsmanid'])) {
    // Get form data
    $kinsmanid = $_POST['kinsmanid'];
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format";
        exit;
    }

    try {
        // Update kinsman data
        $stmt = $pdo->prepare('UPDATE kinsmans SET name = ?, email = ?, updated_at = NOW() WHERE kinsmanid = ?');
        $stmt->execute([$name, $email, $kinsmanid]);

        // Redirect back to the kinsman list with a success message
        $_SESSION['success_message'] = "Kinsman updated successfully!";
        header('Location: kinsmans.php');
        exit;
    } catch (PDOException $e) {
        // Handle error - display error message or log it
        echo "Error updating kinsman: " . $e->getMessage();
    }
}

// Fetch the current kinsman data
if (isset($_GET['kinsmanid'])) {
    $kinsmanid = $_GET['kinsmanid'];
    $stmt = $pdo->prepare('SELECT * FROM kinsmans WHERE kinsmanid = ?');
    $stmt->execute([$kinsmanid]);
    $kinsman = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kinsman) {
        echo "Kinsman not found.";
        exit;
    }
} else {
    echo "No kinsman ID specified.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kinsman</title>
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
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?> <!-- Include the navbar -->

    <div class="container">
        <div class="form-container mx-auto">
            <h2 class="text-center"><i class="fas fa-edit"></i> Edit Kinsman</h2>
            <form id="kinsmanForm" method="POST" onsubmit="event.preventDefault(); showPreview();">
                <input type="hidden" name="kinsmanid" value="<?php echo htmlspecialchars($kinsman['kinsmanid']); ?>">

                <!-- Kinsman Details -->
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($kinsman['name']); ?>" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($kinsman['email']); ?>" required>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Preview</button>
                <a href="kinsmans.php" class="btn btn-secondary mt-2"><i class="fas fa-times"></i> Cancel</a>
            </form>

            <!-- Preview Section -->
            <div id="previewSection" class="preview-container d-none">
                <h5>Confirm Your Details</h5>
                <ul id="previewList">
                    <li><strong>Name:</strong> <span id="previewName"></span></li>
                    <li><strong>Email:</strong> <span id="previewEmail"></span></li>
                </ul>
                <button type="button" class="btn btn-secondary" onclick="goBack()">Back</button>
                <button type="button" class="btn btn-primary" onclick="submitForm()">Confirm</button>
                <input type="hidden" name="name" id="confirmName">
                <input type="hidden" name="email" id="confirmEmail">
                <input type="hidden" name="kinsmanid" id="confirmKinsmanId" value="<?php echo htmlspecialchars($kinsman['kinsmanid']); ?>">
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show the preview section with form data
        function showPreview() {
            document.getElementById('previewName').innerText = document.getElementById('name').value;
            document.getElementById('previewEmail').innerText = document.getElementById('email').value;

            // Show the preview section
            document.getElementById('previewSection').classList.remove('d-none');
            document.getElementById('kinsmanForm').classList.add('d-none');

            // Set hidden inputs for final submission
            document.getElementById('confirmName').value = document.getElementById('name').value;
            document.getElementById('confirmEmail').value = document.getElementById('email').value;
            document.getElementById('confirmKinsmanId').value = document.getElementById('kinsmanForm').kinsmanid.value;
        }

        // Go back to the form
        function goBack() {
            document.getElementById('previewSection').classList.add('d-none');
            document.getElementById('kinsmanForm').classList.remove('d-none');
        }

        // Submit the form
        function submitForm() {
            document.getElementById('kinsmanForm').submit();
        }
    </script>
</body>
</html>
