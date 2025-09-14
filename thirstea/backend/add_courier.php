<?php
header('Content-Type: application/json');


$host = 'localhost';
$dbname = 'thirstea';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}


function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $firstName = sanitize($_POST['firstName'] ?? '');
    $lastName = sanitize($_POST['lastName'] ?? '');
    $contactNumber = sanitize($_POST['contactNumber'] ?? '');
    $vehicleType = sanitize($_POST['vehicleType'] ?? '');
    $vehicleLicensePlate = sanitize($_POST['vehicleLicensePlate'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
        exit;
    }

    

   
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM courier WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Username already exists.']);
        exit;
    }

    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    
    $sql = "INSERT INTO courier (FirstName, LastName, ContactNumber, VehicleType, VehicleLicensePlate, username, password, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            $firstName,
            $lastName,
            $contactNumber,
            $vehicleType,
            $vehicleLicensePlate,
            $username,
            $passwordHash
        ]);
        echo json_encode(['success' => true, 'message' => 'Courier added successfully.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to add courier: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
