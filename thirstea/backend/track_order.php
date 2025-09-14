<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Database credentials
$host = 'localhost';
$db = 'thirstea';
$user = 'root';
$pass = '';

// Validate input
if (!isset($_GET['orderId']) || !is_numeric($_GET['orderId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit();
}

$orderId = intval($_GET['orderId']);

// Connect to database
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Fetch order info based on orderId
$sql = "SELECT OrderID, OrderDate, DeliveryAddress, OrderStatus, TotalAmount FROM orders WHERE OrderID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    $stmt->close();
    $conn->close();
    exit();
}

$order = $result->fetch_assoc();
$stmt->close();

// Return order data
echo json_encode([
    'success' => true,
    'order' => [
        'orderId' => (int)$order['OrderID'],
        'date' => $order['OrderDate'],
        'deliveryAddress' => $order['DeliveryAddress'],
        'status' => $order['OrderStatus'],
        'totalAmount' => (float)$order['TotalAmount']
    ]
]);

$conn->close();
