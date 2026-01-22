
DROP DATABASE IF EXISTS pwd_project;


CREATE DATABASE pwd_project DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE pwd_project;


CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


INSERT INTO users (username, email, password) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('john', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('jane', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('bob', 'bob@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');


CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO products (name, price, description, user_id) VALUES
('Laptop Pro X1', 999.99, 'High-performance laptop with 16GB RAM', 1),
('Smartphone Ultra', 699.99, 'Latest smartphone with 5G', 1),
('Cotton T-Shirt', 19.99, '100% cotton t-shirt', 2),
('Programming Book', 49.99, 'Learn PHP and MySQL', 3),
('Wireless Headphones', 99.99, 'Noise-cancelling headphones', 1),
('Garden Tool Set', 79.99, 'Complete gardening tools', 2),
('Basketball', 29.99, 'Official size basketball', 3),
('Desk Lamp', 39.99, 'LED desk lamp with adjustable brightness', 4);


CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO categories (name, description) VALUES
('Electronics', 'Electronic devices and accessories'),
('Clothing', 'Apparel and fashion items'),
('Books', 'Books and reading materials'),
('Home & Garden', 'Home improvement supplies'),
('Sports', 'Sports equipment');