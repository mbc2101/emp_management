// update_ownership.php
session_start();
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = $_POST['userid']; // User ID
    $deviceid = $_POST['deviceid']; // Device ID

    // Check if the device is already assigned to the user
    $stmt = $pdo->prepare('SELECT * FROM Ownership WHERE userid = :userid AND deviceid = :deviceid');
    $stmt->execute(['userid' => $userid, 'deviceid' => $deviceid]);
    $ownership = $stmt->fetch();

    if ($ownership) {
        // If it exists, remove the assignment
        $stmt = $pdo->prepare('DELETE FROM Ownership WHERE userid = :userid AND deviceid = :deviceid');
        $stmt->execute(['userid' => $userid, 'deviceid' => $deviceid]);
    } else {
        // If it doesn't exist, create the assignment
        $stmt = $pdo->prepare('INSERT INTO Ownership (userid, deviceid) VALUES (:userid, :deviceid)');
        $stmt->execute(['userid' => $userid, 'deviceid' => $deviceid]);
    }

    echo json_encode(['success' => true]);
}
