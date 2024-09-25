<?php
$host = 'localhost:3307';  // Your MySQL server
$db = 'cadremessage-lab';  // Replace with your database name
$user = 'root';    // MySQL user
$pass = ''; // MySQL password

$dsn = "mysql:host=$host;dbname=$db;charset=utf8";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
