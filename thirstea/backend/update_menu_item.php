<?php
// CORS headers to allow cross-origin requests from your frontend origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // You can restrict this to your frontend origin, e.g. 'http://127.0.0.1:5500'
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400'); // Cache preflight response for 1 day
}

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    } else {
        header("Access-Control-Allow-Headers: Content-Type");
    }
    http_response_code(200);
    exit();
}

// Set response content type to JSON
header('Content-Type: application/json');

// Database connection parameters - replace with your actual credentials
$host = 'localhost';
$dbname = 'thirstea';
$username = 'root';
$password = '';

// Connect to MySQL database using mysqli
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection success
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

// Get JSON input from request body
$input = json_decode(file_get_contents('php://input'), true);

// Validate input parameters
if (!isset($input['id'], $input['status'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters: id and status.'
    ]);
    exit;
}

$id = intval($input['id']);
$status = $conn->real_escape_string($input['status']);

// Validate status value - only allow 'available' or 'unavailable'
$allowed_statuses = ['available', 'unavailable'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status value. Allowed values: available, unavailable.'
    ]);
    exit;
}

// Prepare SQL statement to update status
$stmt = $conn->prepare("UPDATE menuitem SET status = ? WHERE MenuItemID = ?");
if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Prepare statement failed: ' . $conn->error
    ]);
    exit;
}

// Bind parameters and execute
$stmt->bind_param('si', $status, $id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Update failed: ' . $stmt->error
    ]);
}

// Close statement and connection
$stmt->close();
$conn->close();
