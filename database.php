<?php
$host = "localhost"; // Change if using a remote database
$dbname = "attendance monitoring system";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

