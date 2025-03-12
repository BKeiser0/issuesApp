<?php
// Database configuration
$host = 'localhost'; // Database server
$db = 'finalproject'; // Database name
$user = 'root'; // Database username
$pass = ''; // Database password (set appropriately)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
