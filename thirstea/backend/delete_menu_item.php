<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Adjust this in production for security
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Read the raw POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !is_numeric($input['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid or missing menu item ID']);
    exit();
}

$id = intval($input['id']);

// Database connection credentials
$host = 'localhost';
$dbUser = 'root';
$dbPass = ''; // your DB password
$dbName = 'thirstea';

// Connect to database
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Prepare DELETE statement
$stmt = $conn->prepare("DELETE FROM menuitem WHERE MenuItemID = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Menu item not found']);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Delete failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
exit();
