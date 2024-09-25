<?php
session_start(); // Démarrer la session

// Rediriger vers login si l'utilisateur n'est pas connecté
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Inclure la connexion à la base de données
include 'db_connect.php';

// Gérer la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_submit'])) {
    // Récupérer les données du formulaire
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $size = $_POST['size'];
    $memory = $_POST['memory'];
    $os = $_POST['os'];
    $sim = $_POST['sim']; // récupère soit 1, soit 0
    $resolution = $_POST['resolution'];

    // Validation de la valeur SIM (valeurs acceptées : "1" ou "0")
    if ($sim !== '1' && $sim !== '0') {
        echo "Invalid SIM value. Please select 'Device with SIM' or 'Device without SIM'.";
        exit;
    }

    // Insérer un nouveau type de périphérique dans la base de données
    $insertStmt = $pdo->prepare('
        INSERT INTO devicetypes (brand, model, size, memory, os, sim, resolution) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    $insertStmt->execute([$brand, $model, $size, $memory, $os, $sim, $resolution]);

    // Rediriger vers la liste des types de périphériques
    header('Location: devicetypes.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Device Type</title>
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
    <?php include 'navbar.php'; ?> <!-- Inclure la barre de navigation -->

    <div class="container form-container mx-auto">
        <h2>Add Device Type</h2>
        <form id="deviceForm" method="POST" action="">
            <div class="form-group">
                <label for="brand">Brand</label>
                <input type="text" class="form-control" id="brand" name="brand" required autofocus>
            </div>
            <div class="form-group">
                <label for="model">Model</label>
                <input type="text" class="form-control" id="model" name="model" required>
            </div>
            <div class="form-group">
                <label for="size">Size</label>
                <input type="text" class="form-control" id="size" name="size" required>
            </div>
            <div class="form-group">
                <label for="memory">Memory</label>
                <input type="text" class="form-control" id="memory" name="memory" required>
            </div>
            <div class="form-group">
                <label for="os">Operating System</label>
                <input type="text" class="form-control" id="os" name="os" required>
            </div>
            <div class="form-group">
                <label for="sim">SIM</label>
                <div>
                    <input type="radio" id="simWith" name="sim" value="1" required>
                    <label for="simWith">Device with SIM</label>
                </div>
                <div>
                    <input type="radio" id="simWithout" name="sim" value="0" required>
                    <label for="simWithout">Device without SIM</label>
                </div>
            </div>
            <div class="form-group">
                <label for="resolution">Resolution</label>
                <input type="text" class="form-control" id="resolution" name="resolution" required>
            </div>
            <button type="button" class="btn btn-primary" onclick="showPreview()">Preview</button>
            <a href="devicetypes.php" class="btn btn-secondary">Cancel</a>
        </form>

        <!-- Section de prévisualisation -->
        <div id="previewSection" class="preview-container d-none">
            <h5>Confirm Device Type Details</h5>
            <ul>
                <li><strong>Brand:</strong> <span id="previewBrand"></span></li>
                <li><strong>Model:</strong> <span id="previewModel"></span></li>
                <li><strong>Size:</strong> <span id="previewSize"></span></li>
                <li><strong>Memory:</strong> <span id="previewMemory"></span></li>
                <li><strong>OS:</strong> <span id="previewOS"></span></li>
                <li><strong>SIM:</strong> <span id="previewSIM"></span></li>
                <li><strong>Resolution:</strong> <span id="previewResolution"></span></li>
            </ul>
            <button type="button" class="btn btn-secondary" onclick="goBack()">Back</button>
            <button type="button" class="btn btn-primary" onclick="submitForm()">Confirm</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Afficher la section de prévisualisation avec les données du formulaire
        function showPreview() {
            document.getElementById('previewBrand').innerText = document.getElementById('brand').value;
            document.getElementById('previewModel').innerText = document.getElementById('model').value;
            document.getElementById('previewSize').innerText = document.getElementById('size').value;
            document.getElementById('previewMemory').innerText = document.getElementById('memory').value;
            document.getElementById('previewOS').innerText = document.getElementById('os').value;
            document.getElementById('previewSIM').innerText = document.querySelector('input[name="sim"]:checked').value === '1' ? 'Device with SIM' : 'Device without SIM';
            document.getElementById('previewResolution').innerText = document.getElementById('resolution').value;

            // Afficher la section de prévisualisation
            document.getElementById('previewSection').classList.remove('d-none');
            document.getElementById('deviceForm').classList.add('d-none');
        }

        // Revenir au formulaire
        function goBack() {
            document.getElementById('previewSection').classList.add('d-none');
            document.getElementById('deviceForm').classList.remove('d-none');
        }

        // Soumettre le formulaire
        function submitForm() {
            const form = document.getElementById('deviceForm');
            const brand = document.getElementById('brand').value;
            const model = document.getElementById('model').value;
            const size = document.getElementById('size').value;
            const memory = document.getElementById('memory').value;
            const os = document.getElementById('os').value;
            const sim = document.querySelector('input[name="sim"]:checked').value;
            const resolution = document.getElementById('resolution').value;

            // Créer des inputs cachés pour chaque champ du formulaire
            form.insertAdjacentHTML('beforeend', `<input type="hidden" name="brand" value="${brand}">`);
            form.insertAdjacentHTML('beforeend', `<input type="hidden" name="model" value="${model}">`);
            form.insertAdjacentHTML('beforeend', `<input type="hidden" name="size" value="${size}">`);
            form.insertAdjacentHTML('beforeend', `<input type="hidden" name="memory" value="${memory}">`);
            form.insertAdjacentHTML('beforeend', `<input type="hidden" name="os" value="${os}">`);
            form.insertAdjacentHTML('beforeend', `<input type="hidden" name="sim" value="${sim}">`);
            form.insertAdjacentHTML('beforeend', `<input type="hidden" name="resolution" value="${resolution}">`);
            
            // Ajouter l'input caché 'form_submit'
            form.insertAdjacentHTML('beforeend', `<input type="hidden" name="form_submit" value="true">`);

            // Soumettre le formulaire
            form.submit();
        }
    </script>
</body>
</html>
