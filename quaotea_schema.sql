-- Quào Tea - schema + dữ liệu mẫu
CREATE DATABASE IF NOT EXISTS quaotea CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quaotea;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  description VARCHAR(500) DEFAULT '',
  price INT NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE,
  customer_name VARCHAR(100) NOT NULL,
  phone VARCHAR(15) NOT NULL,
  address VARCHAR(255) NOT NULL,
  note VARCHAR(255) DEFAULT '',
  total INT NOT NULL,
  status ENUM('pending','preparing','delivering','done','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_phone (phone),
  INDEX idx_created (created_at)
);

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NULL,
  product_name VARCHAR(150) NOT NULL,
  quantity INT NOT NULL,
  unit_price INT NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

INSERT INTO categories (name) VALUES ('Trà sữa'),('Trà trái cây'),('Cà phê'),('Đá xay');

INSERT INTO products (category_id,name,description,price) VALUES
(1,'Trà sữa trân châu đường đen','Trà đen đậm vị, sữa tươi, trân châu nấu đường đen',38000),
(1,'Trà sữa matcha','Matcha Nhật xay mịn, sữa tươi béo nhẹ',42000),
(1,'Hồng trà sữa','Hồng trà ủ lạnh với sữa, vị thanh dễ uống',32000),
(1,'Trà sữa khoai môn','Khoai môn nghiền, thơm bùi, có kèm kem sữa',42000),
(2,'Trà đào cam sả','Trà đào, cam vàng, sả đập dập',40000),
(2,'Trà vải hoa hồng','Vải thiều, cánh hồng sấy, trà oolong',40000),
(2,'Trà chanh mật ong','Chanh tươi vắt, mật ong rừng',30000),
(2,'Trà dâu tằm','Dâu tằm ép, trà xanh lài',38000),
(3,'Cà phê sữa đá','Robusta rang đậm pha phin, sữa đặc',28000),
(3,'Bạc xỉu','Nhiều sữa, ít cà phê, ngọt dịu',30000),
(4,'Matcha đá xay','Matcha xay đá mịn, phủ kem tươi',48000),
(4,'Socola đá xay','Socola đắng pha sữa, xay cùng đá',48000);
