<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Adjust this to your frontend domain in production

// Allow only GET requests for fetching menu
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use GET.']);
    exit();
}

$host = 'localhost';
$dbUser = 'root';
$dbPass = ''; // your DB password
$dbName = 'thirstea';

// Create database connection
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Prepare SQL query
$sql = "SELECT MenuItemID, ItemName, ItemPrice, image_url, category, status FROM menuitem ORDER BY MenuItemID DESC";
$result = $conn->query($sql);

if ($result) {
    $menu = [];
    while ($row = $result->fetch_assoc()) {
        $menu[] = $row;
    }
    echo json_encode(['success' => true, 'menu' => $menu]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to fetch menu: ' . $conn->error]);
}

$conn->close();
exit();
