<?php
session_start();
include './db.php'; // Include the database connection file
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Adjust this to your frontend domain in production
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['items']) || !is_array($data['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    exit();
}

// Optionally validate each item here (productId, quantity, price, etc.)

// Save order in session
$_SESSION['order'] = $data['items'];

echo json_encode(['success' => true]);
