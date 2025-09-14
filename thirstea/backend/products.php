<?php
// products.php - RESTful API endpoint for products with sizes

// Database configuration
$host = 'localhost';
$dbName = 'thirstea';
$username = 'root';
$password = '';

try {
    // Create PDO connection with error mode exception
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set JSON response header
    header('Content-Type: application/json');

    // Check if a product ID is specified
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);

        // Prepare and execute query for single product
        $stmt = $pdo->prepare("SELECT MenuItemID, ItemName, ItemPrice, image_url, category, status FROM menuitem WHERE MenuItemID = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            // Product not found
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            exit;
        }

        // Fetch sizes for this product
        $sizeStmt = $pdo->prepare("SELECT SizeLabel, SizePrice FROM product_sizes WHERE MenuItemID = ? ORDER BY SizePrice ASC");
        $sizeStmt->execute([$id]);
        $sizes = $sizeStmt->fetchAll(PDO::FETCH_ASSOC);

        // Attach sizes array to product
        $product['sizes'] = array_map(function($size) {
            return [
                'label' => $size['SizeLabel'],
                'price' => (float)$size['SizePrice']
            ];
        }, $sizes);

        // Return single product as an array for consistency
        echo json_encode([$product]);
        exit;
    }

    // Fetch all products
    $stmt = $pdo->query("SELECT MenuItemID, ItemName, ItemPrice, image_url, category, status FROM menuitem");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch sizes for each product and attach
    $sizeStmt = $pdo->prepare("SELECT SizeLabel, SizePrice FROM product_sizes WHERE MenuItemID = ? ORDER BY SizePrice ASC");
    foreach ($products as &$product) {
        $sizeStmt->execute([$product['MenuItemID']]);
        $sizes = $sizeStmt->fetchAll(PDO::FETCH_ASSOC);

        $product['sizes'] = array_map(function($size) {
            return [
                'label' => $size['SizeLabel'],
                'price' => (float)$size['SizePrice']
            ];
        }, $sizes);
    }
    unset($product); // break reference

    // Return all products with sizes
    echo json_encode($products);
    exit;

} catch (PDOException $e) {
    // Database error handling
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
