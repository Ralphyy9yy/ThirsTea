<?php
session_start();
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'thirstea';
$user = 'root';
$pass = '';
if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'loggedIn' => true,
        'username' => $_SESSION['username'],
        'firstName' => $_SESSION['first_name'] ?? '',
        'lastName' => $_SESSION['last_name'] ?? '',
        'role' => $_SESSION['role'] ?? ''
    ]);
} else {
    echo json_encode(['loggedIn' => false]);
}
