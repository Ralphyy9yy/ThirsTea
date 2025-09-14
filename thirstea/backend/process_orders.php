<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "You must be logged in as a customer to place an order."]);
    exit;
}

$customerID = $_SESSION['user_id'];

$host = 'localhost';
$dbName = 'thirstea';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get POST data
    $productId = $_POST['productId'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $deliveryAddress = trim($_POST['deliveryAddress'] ?? '');
    $orderStatus = trim($_POST['orderStatus'] ?? 'Pending');

    // Basic validation
    if (!is_numeric($productId) || !is_numeric($quantity) || $quantity <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid product ID or quantity."]);
        exit;
    }

    if (empty($deliveryAddress)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Delivery address is required."]);
        exit;
    }

    // Fetch product details (name and price)
    $stmt = $pdo->prepare("SELECT ItemName, ItemPrice FROM menuitem WHERE MenuItemID = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Product not found."]);
        exit;
    }

    $itemName = $product['ItemName'];
    $itemPrice = $product['ItemPrice'];
    $subtotal = $itemPrice * $quantity;

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Insert into orders table
        $insertOrder = $pdo->prepare("INSERT INTO orders (CustomerID, OrderDate, DeliveryAddress, OrderStatus, TotalAmount) VALUES (?, NOW(), ?, ?, ?)");
        $insertOrder->execute([$customerID, $deliveryAddress, $orderStatus, $subtotal]);

        // Get the new OrderID
        $orderId = $pdo->lastInsertId();

        // Insert into orderitem table
        $insertOrderItem = $pdo->prepare("INSERT INTO orderitem (OrderID, MenuItemID, Quantity, Subtotal) VALUES (?, ?, ?, ?)");
        $insertOrderItem->execute([$orderId, $productId, $quantity, $subtotal]);

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            "success" => true,
            "message" => "Order placed successfully!",
            "orderId" => $orderId,
            "item" => "{$quantity} x {$itemName}"
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to place order: " . $e->getMessage()]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $e->getMessage()]);
}
