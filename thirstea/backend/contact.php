<?php

$host = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "thirstea"; 

header("Content-Type: application/json");

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    
    if (empty($username) || empty($email) || empty($message)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO help (username, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $message);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Message sent successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to send message."]);
    }

    $stmt->close();
}
$conn->close();
?>
