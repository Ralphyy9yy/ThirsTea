<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$host = 'localhost';
$db   = 'thirstea';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$sql = "SELECT CustomerID, FirstName, LastName, Email, PhoneNumber 
        FROM customer 
        ORDER BY FirstName, LastName";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve customers: ' . $conn->error]);
    $conn->close();
    exit();
}

$customers = [];
while ($row = $result->fetch_assoc()) {
    $customers[] = [
        'id' => (int)$row['CustomerID'],
        'name' => htmlspecialchars(trim($row['FirstName'] . ' ' . $row['LastName'])),
        'email' => htmlspecialchars($row['Email']),
        'phone' => htmlspecialchars($row['PhoneNumber'])
    ];
}

$conn->close();

echo json_encode([
    'success' => true,
    'customers' => $customers
]);
