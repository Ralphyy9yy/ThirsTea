<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // For CORS, adjust for production

$host = 'localhost';
$db   = 'thirstea';  // your DB name
$user = 'root';      // your DB user
$pass = '';          // your DB password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);

  // Fetch all messages ordered by submission date descending
  $stmt = $pdo->query("SELECT username, email, Message, submitted_at FROM help ORDER BY submitted_at DESC");
  $messages = $stmt->fetchAll();

  echo json_encode([
    'success' => true,
    'messages' => $messages
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'message' => 'Database error: ' . $e->getMessage()
  ]);
}
