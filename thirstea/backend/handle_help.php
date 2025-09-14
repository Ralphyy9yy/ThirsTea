<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customerID = $_POST['customerID'] ?? null;
    $message = $_POST['message'] ?? '';
    $status = 'Pending';

    if ($message !== '') {
        $stmt = $pdo->prepare("INSERT INTO Help (CustomerID, Message, Status) VALUES (?, ?, ?)");
        $stmt->execute([$customerID, $message, $status]);

        echo "Thank you for your message!";
    } else {
        echo "Message cannot be empty.";
    }
}
?>
