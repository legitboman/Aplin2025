CREATE DATABASE IF NOT EXISTS UAS;
USE UAS;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  display_name VARCHAR(255) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  profile_picture VARCHAR(255),
  saldo INT NOT NULL DEFAULT 0,
  about_me TEXT,
  `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  `status` ENUM('active', 'nonactive') NOT NULL DEFAULT 'active'
);
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE
);
CREATE TABLE posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  caption TEXT,
  image VARCHAR(255),
  harga INT NOT NULL DEFAULT 0,
  stok INT NOT NULL DEFAULT 0,
  category_id INT,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (category_id) REFERENCES categories(id)
);
CREATE TABLE likes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  post_id INT NOT NULL,
  UNIQUE KEY unique_like (user_id, post_id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (post_id) REFERENCES posts(id)
);
CREATE TABLE saved_posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  post_id INT NOT NULL,
  UNIQUE KEY unique_save (user_id, post_id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (post_id) REFERENCES posts(id)
);
CREATE TABLE TRANSACTION (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  post_title VARCHAR(255) NOT NULL,
  category_id INT,
  category_name VARCHAR(100),
  buyer_id INT NOT NULL,
  seller_id INT NOT NULL,
  price INT NOT NULL,
  transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES posts(id),
  FOREIGN KEY (category_id) REFERENCES categories(id),
  FOREIGN KEY (buyer_id) REFERENCES users(id),
  FOREIGN KEY (seller_id) REFERENCES users(id)
);
INSERT INTO categories (NAME) VALUES
('Otomotif'),
('Pakaian'),
('Dapur'),
('Olahraga'),
('Elektronik'),
('Kesehatan'),
('Rumah Tangga'),
('Hobby'),
('Lain-lain');
INSERT INTO users (
  display_name, username, email, `password`, profile_picture, saldo, about_me, `role`, `status`
) VALUES (
  'admin1', 'admin', 'admin@gmail.com', '12', 'default_img.png', 0, NULL, 'admin', 'active'
);
CREATE TABLE brands (
  id INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE
);
INSERT INTO brands (NAME) VALUES
('Toyota'),
('H&M'),
('Sunlight'),
('Nike'),
('Samsung'),
('Herbalife'),
('IKEA'),
('Tamiya'),
('No Brand');
ALTER TABLE posts
ADD brand_id INT,
ADD FOREIGN KEY (brand_id) REFERENCES brands(id);
ALTER TABLE TRANSACTION
ADD COLUMN metode VARCHAR(20) DEFAULT 'saldo',
ADD COLUMN STATUS VARCHAR(20) DEFAULT 'berhasil';
ALTER TABLE TRANSACTION ADD COLUMN order_id VARCHAR(50);
ALTER TABLE posts ADD COLUMN total INT DEFAULT 0;
CREATE TABLE supplier (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  supplier_name VARCHAR(100),
  supplier_price INT NOT NULL,
  FOREIGN KEY (post_id) REFERENCES posts(id)
);
UPDATE posts p
JOIN supplier s ON p.id = s.post_id
SET p.total = ROUND(((p.harga + s.supplier_price) / 2) * 1.1);
INSERT INTO supplier (post_id, supplier_name, supplier_price) VALUES
(1, 'Supplier A', 80000),
(2, 'Supplier B', 120000),
(3, 'Supplier C', 95000);
ALTER TABLE posts
ADD supplier_id INT,
ADD FOREIGN KEY (supplier_id) REFERENCES supplier(id);
UPDATE posts p
JOIN supplier s ON p.supplier_id = s.id
SET p.total = ROUND(((p.harga + s.supplier_price) / 2) * 1.1);
ALTER TABLE supplier
DROP FOREIGN KEY supplier_ibfk_1,
DROP COLUMN post_id;
DELIMITER ;
SET @i := 0;
UPDATE posts
SET supplier_id = ((@i := @i + 1) % 3) + 1
ORDER BY id;