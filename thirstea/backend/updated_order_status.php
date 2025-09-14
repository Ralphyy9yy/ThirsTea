<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['orderId'], $data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing orderId or status']);
    exit();
}

$orderId = intval($data['orderId']);
$status = trim($data['status']);

if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid orderId']);
    exit();
}

// Validate status
$allowedStatuses = ['Pending', 'Picked Up', 'Delivered'];
if (!in_array($status, $allowedStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'thirstea');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Prepare update statement
$sql = "UPDATE orders SET OrderStatus = ?, status = ? WHERE OrderID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
    $conn->close();
    exit();
}

// Bind parameters: OrderStatus (human-readable), status (machine-friendly lowercase), orderId
$machineStatus = strtolower(str_replace(' ', '', $status));
$stmt->bind_param('ssi', $status, $machineStatus, $orderId);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
    $stmt->close();
    $conn->close();
    exit();
}

// Check if any row was updated
if ($stmt->affected_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Order not found or status unchanged']);
    $stmt->close();
    $conn->close();
    exit();
}

$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'message' => "Order status updated to $status"]);
