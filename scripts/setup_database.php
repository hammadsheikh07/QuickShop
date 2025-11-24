<?php

require_once __DIR__ . '/../vendor/autoload.php';

// MySQL configuration for XAMPP
$host = 'localhost';
$dbname = 'quickshop';
$username = 'root';
$password = ''; // Default XAMPP password (empty)

try {
    // Step 1: Connect to MySQL server (without database)
    $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Step 2: Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '{$dbname}' created or already exists.\n";
    
    // Step 3: Select the database
    $pdo->exec("USE `{$dbname}`");
    
    // Step 4: Create products table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10, 2) NOT NULL,
            stock INT NOT NULL DEFAULT 0
        )
    ");
    echo "Table 'products' created or already exists.\n";
    
    // Step 5: Clear existing data (optional - uncomment if you want to reset)
     $pdo->exec("TRUNCATE TABLE products");
    
    // Step 6: Insert sample products (skip if already exists)
    $sampleProducts = [
        ['Laptop Pro 15', 'High-performance laptop with 16GB RAM and 512GB SSD', 1299.99, 15],
        ['Wireless Mouse', 'Ergonomic wireless mouse with long battery life', 29.99, 50],
        ['Mechanical Keyboard', 'RGB backlit mechanical keyboard with blue switches', 89.99, 30],
        ['4K Monitor', '27-inch 4K UHD monitor with HDR support', 399.99, 12],
        ['USB-C Hub', '7-in-1 USB-C hub with HDMI, USB 3.0, and card reader', 49.99, 25],
        ['Webcam HD', '1080p HD webcam with auto-focus and noise cancellation', 79.99, 18],
        ['Noise-Cancelling Headphones', 'Premium over-ear headphones with active noise cancellation', 199.99, 20],
        ['SSD 1TB', 'NVMe SSD 1TB with read speeds up to 3500MB/s', 129.99, 40]
    ];

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO products (name, description, price, stock)
        VALUES (?, ?, ?, ?)
    ");

    $inserted = 0;
    foreach ($sampleProducts as $product) {
        $stmt->execute($product);
        if ($stmt->rowCount() > 0) {
            $inserted++;
        }
    }

    echo "Setup completed successfully!\n";
    echo "Inserted {$inserted} new sample products.\n";
    echo "Total products in database: " . $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() . "\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}