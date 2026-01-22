<?php

require_once 'config.php';

echo "<h2>Reset Database PWD Project</h2>";
echo "<pre>";

try {
    $pdo = getDB();
    
    $tables = ['order_items', 'orders', 'products', 'categories', 'users'];
    
    foreach ($tables as $table) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS $table");
            echo "Dropped table: $table<br>";
        } catch (Exception $e) {
            echo "Error dropping $table: " . $e->getMessage() . "<br>";
        }
    }
    
    $pdo->exec("
        CREATE TABLE users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Created table: users<br>";
    
    $users = [
        ['admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'],
        ['john', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'],
        ['jane', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    foreach ($users as $user) {
        $stmt->execute($user);
    }
    echo "Inserted users<br>";
    
    $pdo->exec("
        CREATE TABLE categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Created table: categories<br>";
    
    $categories = [
        ['Electronics', 'Electronic devices'],
        ['Clothing', 'Fashion items'],
        ['Books', 'Books and magazines'],
        ['Home & Garden', 'Home supplies'],
        ['Sports', 'Sports equipment']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    foreach ($categories as $cat) {
        $stmt->execute($cat);
    }
    echo "Inserted categories<br>";
    
    $pdo->exec("
        CREATE TABLE products (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            description TEXT,
            category_id INT DEFAULT 1,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (category_id) REFERENCES categories(id)
        )
    ");
    echo "Created table: products<br>";
    
    $products = [
        ['Laptop', 999.99, 'High performance laptop', 1, 1],
        ['Smartphone', 699.99, 'Latest smartphone', 1, 1],
        ['T-Shirt', 19.99, 'Cotton t-shirt', 2, 2],
        ['Book', 49.99, 'Programming book', 3, 3]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO products (name, price, description, category_id, user_id) VALUES (?, ?, ?, ?, ?)");
    foreach ($products as $product) {
        $stmt->execute($product);
    }
    echo "Inserted products<br>";
    
    echo "<h3 style='color: green;'>Database reset successfully!</h3>";
    echo "<a href='login.php'>Go to Login</a>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error: " . $e->getMessage() . "</h3>";
}

echo "</pre>";
?>