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
            stock INT NOT NULL DEFAULT 0,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            INDEX idx_deleted_at (deleted_at)
        )
    ");
    echo "Table 'products' created or already exists.\n";
    
    // Step 4a: Add deleted_at column if it doesn't exist (for existing databases)
    try {
        $columnExists = $pdo->query("SHOW COLUMNS FROM products LIKE 'deleted_at'")->fetch();
        if (!$columnExists) {
            $pdo->exec("ALTER TABLE products ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
            $pdo->exec("ALTER TABLE products ADD INDEX idx_deleted_at (deleted_at)");
            echo "Added deleted_at column to products table.\n";
        }
    } catch (PDOException $e) {
        // Column might already exist, ignore error
        echo "Note: " . $e->getMessage() . "\n";
    }
    
    // Step 4a2: Create admins table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "Table 'admins' created or already exists.\n";
    
    // Step 4b: Create cart_items table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cart_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(255) NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_cart_item (session_id, product_id),
            INDEX idx_session_id (session_id)
        )
    ");
    echo "Table 'cart_items' created or already exists.\n";
    
    // Step 4c: Create orders table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(255) NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(50),
            shipping_address TEXT NOT NULL,
            total_amount DECIMAL(10, 2) NOT NULL,
            status VARCHAR(50) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_session_id (session_id),
            INDEX idx_status (status)
        )
    ");
    echo "Table 'orders' created or already exists.\n";
    
    // Step 4d: Create order_items table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            product_price DECIMAL(10, 2) NOT NULL,
            quantity INT NOT NULL,
            subtotal DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id),
            INDEX idx_order_id (order_id)
        )
    ");
    echo "Table 'order_items' created or already exists.\n";
    
    // Step 5: Clear existing data (optional - uncomment if you want to reset)
    // Need to clear dependent tables first due to foreign key constraints
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE order_items");
    $pdo->exec("TRUNCATE TABLE cart_items");
    $pdo->exec("TRUNCATE TABLE orders");
    $pdo->exec("TRUNCATE TABLE products");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Cleared existing data.\n";
    
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

    // Step 7: Create default admin user (username: admin, password: admin123)
    $adminCheck = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    if ($adminCheck == 0) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO admins (username, password_hash) VALUES ('admin', '{$adminPassword}')");
        echo "Default admin user created (username: admin, password: admin123).\n";
    } else {
        echo "Admin user already exists.\n";
    }

    echo "Setup completed successfully!\n";
    echo "Inserted {$inserted} new sample products.\n";
    echo "Total products in database: " . $pdo->query("SELECT COUNT(*) FROM products WHERE deleted_at IS NULL")->fetchColumn() . "\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}