<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'thirstea';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}


$itemName = isset($_POST['itemName']) ? trim($_POST['itemName']) : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$itemPrice = isset($_POST['itemPrice']) ? trim($_POST['itemPrice']) : '';


if ($itemName === '' || $category === '' || $itemPrice === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}


if (!is_numeric($itemPrice) || floatval($itemPrice) <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid price value']);
    exit();
}


$image_url = '';
if (isset($_FILES['itemImage']) && $_FILES['itemImage']['error'] === UPLOAD_ERR_OK) {
    
    $targetDir = __DIR__ . '/../public/images/';
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    
    $originalName = basename($_FILES['itemImage']['name']);
    $safeName = preg_replace('/\s+/', '_', $originalName);

    
    $fileName = uniqid() . '_' . $safeName;
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($_FILES['itemImage']['tmp_name'], $targetFile)) {
        
        $image_url = '../images/' . $fileName;
    }
}


$stmt = $conn->prepare("INSERT INTO menuitem (ItemName, ItemPrice, image_url, category) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("sdss", $itemName, $itemPrice, $image_url, $category);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
exit();
