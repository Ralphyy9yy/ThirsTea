<?php
header('Content-Type: application/json');
include './db.php';  // Your PDO connection file

try {
    $sql = "SELECT 
              payment_id AS Transaction_ID, 
              order_id AS Order_ID, 
              amount AS Amount, 
              payment_method AS Payment_Method, 
              created_at AS Timestamp, 
              payment_status AS Status 
            FROM payments 
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch all rows as associative arrays
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($transactions);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed: ' . $e->getMessage()]);
}
