<?php
// Enable error reporting (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit();
}

// Validate required fields including customerName and paymentMethod
$required = ['customerId', 'customerName', 'address', 'paymentMethod', 'cart'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing or empty field: $field"]);
        exit();
    }
}

$customerId = intval($data['customerId']);
$customerName = trim($data['customerName']);
$address = trim($data['address']);
$paymentMethod = trim($data['paymentMethod']);
$cart = $data['cart'];

if (!is_array($cart) || count($cart) === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit();
}

// Calculate total amount from cart items (using sizePrice)
$totalAmount = 0;
foreach ($cart as $item) {
    $price = isset($item['sizePrice']) ? floatval($item['sizePrice']) : 0;
    $quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;

    if ($price <= 0 || $quantity <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid price or quantity in cart item']);
        exit();
    }

    $totalAmount += $price * $quantity;
}

// Add delivery fee (adjust this value if delivery fee is dynamic)
$deliveryFee = 40;
$totalAmountWithDelivery = $totalAmount + $deliveryFee;

// Database connection
$conn = new mysqli('localhost', 'root', '', 'thirstea');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Assign a random active courier
$courierQuery = "SELECT CourierID FROM courier WHERE is_active = 1 ORDER BY RAND() LIMIT 1";
$courierResult = $conn->query($courierQuery);

$assignedCourierId = null;
if ($courierResult && $courierResult->num_rows > 0) {
    $courierRow = $courierResult->fetch_assoc();
    $assignedCourierId = $courierRow['CourierID'];
} else {
    // No active courier available
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'No active courier available']);
    $conn->close();
    exit();
}

// Insert the order
$orderSql = "INSERT INTO orders (CustomerID, DeliveryAddress, OrderStatus, TotalAmount, CourierID, status, OrderDate) 
             VALUES (?, ?, 'To be Delivered', ?, ?, 'pending', NOW())";
$stmt = $conn->prepare($orderSql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to prepare order insert']);
    $conn->close();
    exit();
}

$stmt->bind_param('isdi', $customerId, $address, $totalAmountWithDelivery, $assignedCourierId);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to insert order']);
    $stmt->close();
    $conn->close();
    exit();
}

$orderId = $stmt->insert_id;
$stmt->close();

// Insert order items with size info
$itemSql = "INSERT INTO orderitem (OrderID, MenuItemID, Quantity, Subtotal, SizeLabel, SizePrice) VALUES (?, ?, ?, ?, ?, ?)";
$stmtItem = $conn->prepare($itemSql);
if (!$stmtItem) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to prepare order items insert']);
    $conn->close();
    exit();
}

foreach ($cart as $item) {
    $menuItemId = isset($item['productId']) ? intval($item['productId']) : 0;
    $quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;
    $sizeLabel = isset($item['sizeLabel']) ? $item['sizeLabel'] : '';
    $sizePrice = isset($item['sizePrice']) ? floatval($item['sizePrice']) : 0;
    $subtotal = $sizePrice * $quantity;

    if ($menuItemId > 0 && $quantity > 0 && $sizePrice > 0) {
        $stmtItem->bind_param('iiidsd', $orderId, $menuItemId, $quantity, $subtotal, $sizeLabel, $sizePrice);
        if (!$stmtItem->execute()) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to insert order item']);
            $stmtItem->close();
            $conn->close();
            exit();
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid order item data']);
        $stmtItem->close();
        $conn->close();
        exit();
    }
}

$stmtItem->close();

// Insert payment record
$paymentStatus = 'Completed'; // or 'Completed' if payment confirmed immediately

$paymentSql = "INSERT INTO payments (order_id, customer_name, customer_address, payment_method, amount, payment_status) 
               VALUES (?, ?, ?, ?, ?, ?)";
$stmtPayment = $conn->prepare($paymentSql);
if (!$stmtPayment) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to prepare payment insert']);
    $conn->close();
    exit();
}

$stmtPayment->bind_param('isssds', $orderId, $customerName, $address, $paymentMethod, $totalAmountWithDelivery, $paymentStatus);

if (!$stmtPayment->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to insert payment']);
    $stmtPayment->close();
    $conn->close();
    exit();
}

$stmtPayment->close();
$conn->close();

echo json_encode(['success' => true, 'message' => 'Order and payment placed successfully', 'orderId' => $orderId]);
