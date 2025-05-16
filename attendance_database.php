
<?php
$host = 'localhost';
$dbname = 'attendance monitoring system';  // Palitan ito ng tunay na database name
$username = 'root';  // Default user sa XAMPP
$password = '';  // Walang password sa default XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
