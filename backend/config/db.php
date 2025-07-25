<?php
// backend/config/db.php
// Database connection using PDO

$host = 'localhost'; // Database host
$db   = 'academic_planner'; // Database name
$user = 'root'; // Database username
$pass = 'password'; // Database password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Connection successful
    return $pdo;
} catch (PDOException $e) {
    // Handle connection error
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
} 