<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
session_start();

// Database credentials
$host = 'localhost';
$db = 'thirstea';
$user = 'root';
$pass = '';

// Connect to database
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// For demo: get user ID from session or token. Replace with your auth mechanism
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}
$customerId = intval($_SESSION['user_id']); // customerId is the id from Session

// Fetch recent orders for the customer (limit 10)
$sql = "SELECT OrderID, OrderDate, DeliveryAddress, TotalAmount, OrderStatus
        FROM orders
        WHERE CustomerID = ?
        ORDER BY OrderDate DESC
        LIMIT 10";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];

while ($order = $result->fetch_assoc()) {
  /*
  ** No code for order_item, to include, send database for order item
  */
    $orders[] = [
        'orderId' => (int)$order['OrderID'],
        'date' => $order['OrderDate'],
        'items' => [], //Replace if u had order item, database needed
        'total' => (float)$order['TotalAmount'],
        'status' => $order['OrderStatus']
    ];
}

$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'orders' => $orders]);
