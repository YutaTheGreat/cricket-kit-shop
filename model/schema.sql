CREATE DATABASE IF NOT EXISTS cricket_kit_shop_db;
USE cricket_kit_shop_db;

CREATE TABLE IF NOT EXISTS cricket_kits (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    item_brand VARCHAR(100) NOT NULL,
    item_type VARCHAR(100) NOT NULL,
    price INT NOT NULL, 
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS buyers (
    buyer_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_name VARCHAR(100) NOT NULL,
    item_id INT NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    shipping_address TEXT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES cricket_kits(item_id)
);
