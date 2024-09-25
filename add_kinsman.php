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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];

    try {
        // Insert new kinsman
        $stmt = $pdo->prepare('INSERT INTO kinsmans (name, email) VALUES (?, ?)');
        $stmt->execute([$name, $email]);

        // Redirect back to the kinsman list
        header('Location: kinsmans.php');
        exit;
    } catch (PDOException $e) {
        // Handle error - display error message or log it
        echo "Error inserting kinsman: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Kinsman</title>
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
            <h2 class="text-center"><i class="fas fa-plus"></i> Add New Kinsman</h2>
            <form id="kinsmanForm" method="POST" onsubmit="event.preventDefault(); showPreview();">
                <!-- Kinsman Details -->
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
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
