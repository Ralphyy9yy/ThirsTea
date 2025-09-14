<?php
// Allow requests from your frontend origin (or use * to allow all)
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true'); // if you use cookies or credentials

// Handle OPTIONS preflight request

// Database connection parameters
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Database connection settings
$host = 'localhost';
$dbname = 'thirstea';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Total Orders
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $totalOrders = (int) $stmt->fetchColumn();

    // Total Revenue (sum of TotalAmount)
    $stmt = $pdo->query("SELECT IFNULL(SUM(TotalAmount), 0) FROM orders where status = 'Delivered'");
    $totalRevenue = (float) $stmt->fetchColumn();

    // Active Couriers (assuming is_active column in courier table)
    $stmt = $pdo->query("SELECT COUNT(*) FROM courier WHERE is_active = 1");
    $activeCouriers = (int) $stmt->fetchColumn();

    // Pending Deliveries (assuming status='pending' in orders table)
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'");
    $pendingDeliveries = (int) $stmt->fetchColumn();

    // Return JSON response
    echo json_encode([
        'success' => true,
        'totalOrders' => $totalOrders,
        'totalRevenue' => $totalRevenue,
        'activeCouriers' => $activeCouriers,
        'pendingDeliveries' => $pendingDeliveries,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
    ]);
}