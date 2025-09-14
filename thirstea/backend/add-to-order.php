<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

require 'db.php'; 


$menuItemId = isset($_POST['menuItemId']) ? intval($_POST['menuItemId']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
$orderId = isset($_POST['orderId']) ? intval($_POST['orderId']) : 0; // You may generate or receive this from frontend/session

if ($menuItemId <= 0 || $quantity <= 0) {
    http_response_code(400);
    echo "Invalid input data.";
    exit;
}


if ($orderId <= 0) {
    
    $stmt = $pdo->prepare("INSERT INTO Orders (OrderDate) VALUES (NOW())");
    $stmt->execute();
    $orderId = $pdo->lastInsertId();
}


$stmt = $pdo->prepare("SELECT Price FROM MenuItems WHERE MenuItemID = ?");
$stmt->execute([$menuItemId]);
$menuItem = $stmt->fetch();

if (!$menuItem) {
    http_response_code(404);
    echo "Menu item not found.";
    exit;
}

$price = $menuItem['Price'];
$subtotal = $price * $quantity;


$stmt = $pdo->prepare("INSERT INTO OrderItems (OrderID, MenuItemID, Quantity, Subtotal) VALUES (?, ?, ?, ?)");
try {
    $stmt->execute([$orderId, $menuItemId, $quantity, $subtotal]);
    echo json_encode([
        'message' => 'Item added to order successfully.',
        'orderId' => $orderId
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Failed to add item to order: " . $e->getMessage();
}
