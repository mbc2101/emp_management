<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = $_POST['userid'];
    $deviceid = $_POST['deviceid'];
    $action = $_POST['action'];

    if ($action === 'assign') {
        // Begin a transaction to ensure both actions (assignment and status update) succeed together
        $pdo->beginTransaction();

        try {
            // Assign the device
            $stmt = $pdo->prepare("INSERT INTO ownership (userid, deviceid, assigned_at) VALUES (:userid, :deviceid, NOW())");
            $stmt->execute(['userid' => $userid, 'deviceid' => $deviceid]);

            // Update the device status to 'In use' (assuming statusid = 2 is "In use")
            $stmt = $pdo->prepare("UPDATE devices SET statusid = 2 WHERE deviceid = :deviceid");
            $stmt->execute(['deviceid' => $deviceid]);

            // Commit the transaction
            $pdo->commit();

            echo json_encode(['message' => 'Device assigned successfully.']);
        } catch (Exception $e) {
            // Rollback the transaction if something goes wrong
            $pdo->rollBack();
            echo json_encode(['message' => 'Error assigning device.']);
        }

    } elseif ($action === 'unassign') {
        // Begin a transaction to handle unassignment and status update
        $pdo->beginTransaction();

        try {
            // Check if the device is already unassigned
            $stmt = $pdo->prepare("SELECT unassigned_at FROM ownership WHERE userid = :userid AND deviceid = :deviceid");
            $stmt->execute(['userid' => $userid, 'deviceid' => $deviceid]);
            $result = $stmt->fetch();

            if (!$result['unassigned_at']) {
                // Unassign the device
                $stmt = $pdo->prepare("UPDATE ownership SET unassigned_at = NOW() WHERE userid = :userid AND deviceid = :deviceid");
                $stmt->execute(['userid' => $userid, 'deviceid' => $deviceid]);

                // Update the device status back to 'Available' (assuming statusid = 1 is "Available")
                $stmt = $pdo->prepare("UPDATE devices SET statusid = 1 WHERE deviceid = :deviceid");
                $stmt->execute(['deviceid' => $deviceid]);

                // Commit the transaction
                $pdo->commit();

                echo json_encode(['message' => 'Device unassigned successfully.']);
            } else {
                echo json_encode(['message' => 'Device is already unassigned.']);
            }
        } catch (Exception $e) {
            // Rollback the transaction if something goes wrong
            $pdo->rollBack();
            echo json_encode(['message' => 'Error unassigning device.']);
        }
    }
}
?>
