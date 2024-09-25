<?php
// Hash a password for a new user
$hashedPassword = password_hash('admin', PASSWORD_DEFAULT);
echo $hashedPassword;
?>
