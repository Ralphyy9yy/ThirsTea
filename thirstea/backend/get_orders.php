<?php
// Enable error reporting for debugging (remove or disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set JSON response headers and allow CORS (adjust origin for production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$db   = 'thirstea'; // Your database name
$user = 'root';     // Your DB username
$pass = '';         // Your DB password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepares if possible
];

try {
    // Create PDO instance
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Prepare and execute SQL query
    $sql = "
        SELECT 
            o.OrderID, 
            CONCAT(COALESCE(c.FirstName, ''), ' ', COALESCE(c.LastName, '')) AS Customer,
            o.status as Status,
            o.TotalAmount AS Amount,
            DATE_FORMAT(o.OrderDate, '%d-%m-%y') AS Date
        FROM orders o
        LEFT JOIN customer c ON o.CustomerID = c.CustomerID
        ORDER BY o.OrderDate DESC
        LIMIT 20
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $orders = $stmt->fetchAll();

    // Return JSON response
    echo json_encode([
        'success' => true,
        'orders' => $orders
    ]);
} catch (PDOException $e) {
    // Return error as JSON with 500 HTTP status code
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}
