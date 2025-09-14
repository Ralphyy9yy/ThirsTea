<?php
// backend/register.php
include './db.php'; // Include the database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input data
    $firstName = htmlspecialchars(trim($_POST['firstName']));
    $lastName = htmlspecialchars(trim($_POST['lastName']));
    $address = htmlspecialchars(trim($_POST['address']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phoneNumber = htmlspecialchars(trim($_POST['phoneNumber']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password for security

    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO customer (FirstName, LastName , Address, username, Email, PhoneNumber, password) VALUES (?, ?, ?, ?, ?, ?, ?)");

    try {
        // Execute the statement with provided inputs
        if ($stmt->execute([$firstName, $lastName, $address, $username, $email, $phoneNumber, $password])) {
          
            header("Location: /thirstea/public/html/3rd.html");
            exit();
            
        } else {
            throw new Exception("Registration failed. Please try again.");
        }
    } catch (Exception $e) {
        // Log the error or handle it appropriately
        error_log($e->getMessage());
        echo "An error occurred: " . $e->getMessage(); // Display a generic error message
    }
}
?>
