<?php
require_once __DIR__ . '/config/db.php';
$pdo = require __DIR__ . '/config/db.php';

// Create admin user
$password = 'admin123';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('INSERT INTO users (username, password, email, role_id) VALUES (?, ?, ?, ?)');
$stmt->execute(['admin2', $hashedPassword, 'admin2@gmail.com', 1]);

echo "Admin user created successfully!";
?>