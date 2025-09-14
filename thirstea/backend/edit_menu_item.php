<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");


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

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$itemName = isset($_POST['itemName']) ? trim($_POST['itemName']) : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$itemPrice = isset($_POST['itemPrice']) ? trim($_POST['itemPrice']) : '';

if ($id <= 0 || $itemName === '' || $category === '' || $itemPrice === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields or invalid ID']);
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

    if (!move_uploaded_file($_FILES['itemImage']['tmp_name'], $targetFile)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
        exit();
    }

    $image_url = '../images/' . $fileName;
}

if ($image_url !== '') {
    $stmt = $conn->prepare("UPDATE menuitem SET ItemName = ?, ItemPrice = ?, image_url = ?, category = ? WHERE MenuItemID = ?");
    $stmt->bind_param("sdssi", $itemName, $itemPrice, $image_url, $category, $id);
} else {
    $stmt = $conn->prepare("UPDATE menuitem SET ItemName = ?, ItemPrice = ?, category = ? WHERE MenuItemID = ?");
    $stmt->bind_param("sdsi", $itemName, $itemPrice, $category, $id);
}

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
exit();
