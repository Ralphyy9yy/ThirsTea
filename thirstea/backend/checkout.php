<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "thirstea";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['items']) || !isset($data['delivery_address']) || !isset($data['customer_id'])) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input"]);
    exit;
}

$items = $data['items']; 
$deliveryAddress = $conn->real_escape_string($data['delivery_address']);
$customerID = intval($data['customer_id']);


$totalAmount = 0;
foreach ($items as $item) {
    $totalAmount += $item['price'] * $item['quantity'];
}


$orderStatus = 'pending';
$status = 'pending';


$stmt = $conn->prepare("INSERT INTO orders (CustomerID, OrderDate, DeliveryAddress, OrderStatus, TotalAmount, status) VALUES (?, NOW(), ?, ?, ?, ?)");
$stmt->bind_param("issds", $customerID, $deliveryAddress, $orderStatus, $totalAmount, $status);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["message" => "Failed to create order"]);
    exit;
}

$orderID = $stmt->insert_id;
$stmt->close();


$stmtItem = $conn->prepare("INSERT INTO orderitem (OrderID, MenuItemID, Quantity, Subtotal) VALUES (?, ?, ?, ?)");

foreach ($items as $item) {
    $menuItemID = intval($item['menu_item_id']);
    $quantity = intval($item['quantity']);
    $subtotal = floatval($item['price']) * $quantity;

    $stmtItem->bind_param("iiid", $orderID, $menuItemID, $quantity, $subtotal);
    if (!$stmtItem->execute()) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to insert order items"]);
        exit;
    }
}

$stmtItem->close();
$conn->close();


$response = [
    "message" => "Order created successfully",
    "order_id" => $orderID,
    "redirect_url" => "payment.html?order_id=" . $orderID
];

header('Content-Type: application/json');
echo json_encode($response);
?>
