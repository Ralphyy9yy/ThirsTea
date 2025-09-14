<?php
header('Content-Type: application/json');

// Database connection settings — replace with your actual credentials
$host = 'localhost';
$dbname = 'thirstea';
$user = 'root';
$pass = '';

try {
    // Create PDO connection with UTF-8 charset
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare and execute query to fetch couriers
    $stmt = $pdo->query("SELECT CourierID, FirstName, LastName, ContactNumber, VehicleType, VehicleLicensePlate FROM courier ORDER BY CourierID DESC");

    $couriers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'couriers' => $couriers
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}
