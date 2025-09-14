<?php
// Database connection parameters
$host = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "thirstea";

// User info to update
$usernameToUpdate = 'thirsteaAdmin';
$plainPassword = 'meowmeow';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Hash the plain password
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

// Prepare update statement
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("ss", $hashedPassword, $usernameToUpdate);

if ($stmt->execute()) {
    echo "Password updated successfully for user: $usernameToUpdate\n";
} else {
    echo "Error updating password: " . $stmt->error . "\n";
}

$stmt->close();
$conn->close();
