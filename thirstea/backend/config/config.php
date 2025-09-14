<?php
// Database configuration
$host = "localhost";
$dbname = "thirstea";
$username = "root";
$password = "";

// DSN
$dsn = "mysql:host=$host;dbname=$dbname;charset=UTF8";

try {
    $pdo = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
