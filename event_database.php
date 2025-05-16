<?php
// Database connection (Update with your database credentials)
$host = "localhost"; // Change if using a remote database
$dbname = "attendance monitoring system";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
