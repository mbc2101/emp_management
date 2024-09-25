<?php
session_start();
include 'db_connect.php';

// Redirect to login if not logged in
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = $_POST['userid'];
    $language = $_POST['language'];
    $morning = $_POST['morning'];
    $noon = $_POST['noon'];
    $afternoon = $_POST['afternoon'];
    $evening = $_POST['evening'];
    $night = $_POST['night'];

    $stmt = $pdo->prepare('
        UPDATE parameters SET
            language = :language,
            morning = :morning,
            noon = :noon,
            afternoon = :afternoon,
            evening = :evening,
            night = :night
        WHERE userid = :userid
    ');

    $stmt->execute([
        'userid' => $userid,
        'language' => $language,
        'morning' => $morning,
        'noon' => $noon,
        'afternoon' => $afternoon,
        'evening' => $evening,
        'night' => $night,
    ]);

    header('Location: users.php'); // Redirect back to the user management page
    exit;
}
?>
