CREATE DATABASE IF NOT EXISTS restaurant_app;
USE restaurant_app;

-- Users tablosu
CREATE TABLE IF NOT EXISTS users (
    u_id INT AUTO_INCREMENT PRIMARY KEY,
    u_company_id INT,
    u_name VARCHAR(255) NOT NULL,
    u_email VARCHAR(255) UNIQUE NOT NULL,
    u_password VARCHAR(255) NOT NULL,
    u_role ENUM('admin', 'company', 'customer') NOT NULL,
    u_default_saldo DECIMAL(10,2) DEFAULT 0,
    u_deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- Company tablosu
CREATE TABLE IF NOT EXISTS company (
    c_id INT AUTO_INCREMENT PRIMARY KEY,
    c_name VARCHAR(255) NOT NULL,
    c_description TEXT,
    c_image_path VARCHAR(255),
    c_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Restaurant tablosu
CREATE TABLE IF NOT EXISTS restaurant (
    r_id INT AUTO_INCREMENT PRIMARY KEY,
    r_company_id INT NOT NULL,
    r_name VARCHAR(255) NOT NULL,
    r_description TEXT,
    r_image_path VARCHAR(255),
    r_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES company(id)
);

-- Food tablosu
CREATE TABLE IF NOT EXISTS food (
    f_id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    f_name VARCHAR(255) NOT NULL,
    f_description TEXT,
    f_price DECIMAL(10,2) NOT NULL,
    f_discount DECIMAL(10,2) DEFAULT 0,
    f_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    f_deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (restaurant_id) REFERENCES restaurant(id)
);

-- Order tablosu
CREATE TABLE IF NOT EXISTS `order` (
    o_id INT AUTO_INCREMENT PRIMARY KEY,
    o_user_id INT NOT NULL,
    o_total_price DECIMAL(10,2) NOT NULL,
    o_order_status ENUM('preparing', 'on_the_way', 'delivered') NOT NULL,
    o_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Basket tablosu
CREATE TABLE IF NOT EXISTS basket (
    b_user_id INT NOT NULL,
    b_food_id INT NOT NULL,
    b_note TEXT,
    b_quantity INT NOT NULL,
    b_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (food_id) REFERENCES food(id)
);

-- Coupon tablosu
CREATE TABLE IF NOT EXISTS coupon (
    cou_id INT AUTO_INCREMENT PRIMARY KEY,
    cou_code VARCHAR(50) NOT NULL,
    cou_discount DECIMAL(10,2) NOT NULL,
    cou_restaurant_id INT NOT NULL,
    cou_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurant(id)
);

-- Comment tablosu
CREATE TABLE IF NOT EXISTS comments (
    comm_id INT AUTO_INCREMENT PRIMARY KEY,
    comm_user_id INT NOT NULL,
    comm_restaurant_id INT NOT NULL,
    comm_title VARCHAR(255),
    comm_description TEXT,
    comm_rating DECIMAL(3,2) CHECK (rating BETWEEN 0 AND 10),
    comm_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    comm_deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (restaurant_id) REFERENCES restaurant(id)
);

-- Order Items tablosu
CREATE TABLE IF NOT EXISTS order_items (
    oi_id INT AUTO_INCREMENT PRIMARY KEY,
    oi_order_id INT NOT NULL,
    oi_food_id INT NOT NULL,
    oi_quantity INT NOT NULL,
    oi_price DECIMAL(10,2) NOT NULL,
    oi_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES `order`(id),
    FOREIGN KEY (food_id) REFERENCES food(id)
);
