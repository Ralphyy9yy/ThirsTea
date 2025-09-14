<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$courierId = isset($_GET['courierId']) ? intval($_GET['courierId']) : 0;

if ($courierId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid courier ID']);
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'thirstea');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$sql = "SELECT o.OrderID, c.FirstName, c.LastName, o.DeliveryAddress, o.TotalAmount, o.OrderDate, o.OrderStatus
        FROM orders o
        JOIN customer c ON o.CustomerID = c.CustomerID
        WHERE o.CourierID = ? AND o.OrderStatus = 'To be Delivered'
        ORDER BY o.OrderDate DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
    $conn->close();
    exit();
}

$stmt->bind_param('i', $courierId);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to execute query']);
    $stmt->close();
    $conn->close();
    exit();
}

$result = $stmt->get_result();
$orders = [];

while ($row = $result->fetch_assoc()) {
    $orders[] = [
        'orderId' => (int)$row['OrderID'],
        'customerName' => htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']),
        'address' => htmlspecialchars($row['DeliveryAddress']),
        'amount' => round(floatval($row['TotalAmount']), 2),
        'date' => date('d-m-Y', strtotime($row['OrderDate'])),
        'status' => htmlspecialchars($row['OrderStatus'])
    ];
}

$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'orders' => $orders]);
