<?php
$host = "localhost"; 
$user = "root"; 
$pass = ""; 
$db   = "gmpc_store"; // your database name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>


CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tracking_id VARCHAR(50) NOT NULL UNIQUE,
  reference VARCHAR(100) NOT NULL,
  firstname VARCHAR(100),
  lastname VARCHAR(100),
  email VARCHAR(150),
  phone VARCHAR(50),
  address TEXT,
  city VARCHAR(100),
  zip VARCHAR(20),
  items JSON,
  total DECIMAL(10,2),
  status ENUM('Pending','Paid','Shipped','Delivered') DEFAULT 'Paid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


<!-- new database -->
 CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tracking_id VARCHAR(50) NOT NULL UNIQUE,
  reference VARCHAR(100) DEFAULT NULL,
  firstname VARCHAR(100),
  lastname VARCHAR(100),
  email VARCHAR(150),
  phone VARCHAR(50),
  address TEXT,
  city VARCHAR(100),
  zip VARCHAR(20),
  items JSON,
  total DECIMAL(10,2),
  status ENUM('Pending','Paid','Shipped','Delivered') DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
<!--  -->