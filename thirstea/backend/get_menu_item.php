<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'thirstea';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB connection failed: ' . $conn->connect_error]);
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit();
}

// Use MenuItemID as the primary key
$stmt = $conn->prepare("SELECT MenuItemID as id, ItemName, ItemPrice, image_url, category FROM menuitem WHERE MenuItemID = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
    exit();
}

$item = $result->fetch_assoc();

if ($item) {
    echo json_encode(['success' => true, 'item' => $item]);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Menu item not found']);
}

$stmt->close();
$conn->close();
exit();
