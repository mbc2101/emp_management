<?php
session_start();
include 'db_connect.php';

if (isset($_GET['userid'])) {
    $userid = $_GET['userid'];

    // Fetch devices and subscription plan for the user
    $stmt = $pdo->prepare('
        SELECT GROUP_CONCAT(d.serialnumber SEPARATOR ", ") AS devices,
               p.planname AS subscription
        FROM ownership o
        LEFT JOIN devices d ON o.deviceid = d.deviceid
        LEFT JOIN subscriptions s ON o.userid = s.userid
        LEFT JOIN plansdefinitions p ON s.plandefinitionid = p.plandefinitionid
        WHERE o.userid = ?
        GROUP BY o.userid
    ');
    $stmt->execute([$userid]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($data);
}
?>
