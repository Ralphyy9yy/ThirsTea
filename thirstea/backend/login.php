<?php
// Enable CORS for your frontend origin

header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();

$host = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "thirstea";

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize inputs
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Username and password are required."]);
        exit;
    }

    // Function to fetch user by username from given table
    function fetchUser($conn, $table, $username) {
        $sql = "SELECT * FROM $table WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Database query preparation failed."]);
            exit;
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    // Check admin first
    $user = fetchUser($conn, 'users', $username);
    if ($user) {
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = 'admin';

            echo json_encode([
                "success" => true,
                "role" => "admin",
                "userId" => $user['id'],
                "username" => $user['username']
            ]);
            $conn->close();
            exit;
        } else {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Incorrect password."]);
            $conn->close();
            exit;
        }
    }

    // Check customer if not admin
    $user = fetchUser($conn, 'customer', $username);
    if ($user) {
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['CustomerID'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = 'customer';
            $_SESSION['first_name'] = $user['FirstName'];
            $_SESSION['last_name'] = $user['LastName'];

            echo json_encode([
                "success" => true,
                "role" => "customer",
                "userId" => $user['CustomerID'],
                "username" => $user['username'],
                "firstName" => $user['FirstName'],
                "lastName" => $user['LastName']
            ]);
            $conn->close();
            exit;
        } else {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Incorrect password."]);
            $conn->close();
            exit;
        }
    }

    // User not found
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid username or password."]);
    $conn->close();
    exit;

} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    $conn->close();
    exit;
}
