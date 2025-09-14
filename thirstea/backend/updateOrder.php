<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "thirstea";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get JSON input from frontend
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['order_id']) || !isset($data['payment_method'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$orderId = intval($data['order_id']);
$paymentMethod = $conn->real_escape_string($data['payment_method']);

// Determine the order status based on the payment method
$orderStatus = ($paymentMethod === 'cash') ? 'pending' : 'processing';

// Update order status in the orders table
$stmt = $conn->prepare("UPDATE orders SET OrderStatus = ?, status = ? WHERE OrderID = ?");
$stmt->bind_param("ssi", $orderStatus, $paymentMethod, $orderId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update order status: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
