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