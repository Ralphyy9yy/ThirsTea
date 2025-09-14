<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for security in production
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

// Validate required fields
$customer = $data['customer'] ?? null;
$orderItems = $data['items'] ?? null;
$deliveryAddress = $data['deliveryAddress'] ?? null;
$totalAmount = $data['totalAmount'] ?? null;

if (!$customer || !$orderItems || !$deliveryAddress || !$totalAmount) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required order data']);
    exit;
}

// Database connection
$host = 'localhost';
$db   = 'thirstea';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->beginTransaction();

    // Check if customer exists by username or email
    $stmt = $pdo->prepare("SELECT CustomerID FROM customer WHERE username = :username OR Email = :email");
    $stmt->execute(['username' => $customer['username'], 'email' => $customer['Email']]);
    $existingCustomer = $stmt->fetch();

    if ($existingCustomer) {
        $customerID = $existingCustomer['CustomerID'];

        // Optionally update customer info here if needed
    } else {
        // Insert new customer
        $stmt = $pdo->prepare("INSERT INTO customer (FirstName, LastName, Email, PhoneNumber, Address, username, password) VALUES (:fname, :lname, :email, :phone, :address, :username, :password)");
        $stmt->execute([
            'fname' => $customer['FirstName'],
            'lname' => $customer['LastName'],
            'email' => $customer['Email'],
            'phone' => $customer['PhoneNumber'],
            'address' => $customer['Address'],
            'username' => $customer['username'],
            'password' => password_hash($customer['password'], PASSWORD_DEFAULT) // hash password
        ]);
        $customerID = $pdo->lastInsertId();
    }

    // Insert order
    $stmt = $pdo->prepare("INSERT INTO orders (CustomerID, OrderDate, DeliveryAddress, OrderStatus, TotalAmount) VALUES (:custID, NOW(), :address, 'Pending', :total)");
    $stmt->execute([
        'custID' => $customerID,
        'address' => $deliveryAddress,
        'total' => $totalAmount
    ]);
    $orderID = $pdo->lastInsertId();

    // Insert order items
    $stmtItem = $pdo->prepare("INSERT INTO order_items (OrderID, ItemName, Price, Quantity) VALUES (:orderID, :name, :price, :qty)");

    foreach ($orderItems as $item) {
        $stmtItem->execute([
            'orderID' => $orderID,
            'name' => $item['name'],
            'price' => $item['price'],
            'qty' => $item['quantity']
        ]);
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Order placed successfully', 'orderID' => $orderID]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
