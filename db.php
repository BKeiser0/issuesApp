<?php
$host = 'localhost';
$dbname = 'finalproject';
$username = 'root'; // Change if needed
$password = ''; // Change if needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
