<?php
// backend/signup.php
header('Content-Type: application/json');

require_once __DIR__ . '/config/db.php';
$pdo = require __DIR__ . '/config/db.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$role = trim($data['role'] ?? '');

if (!$name || !$email || !$password || !$role) {
    echo json_encode(['success' => false, 'error' => 'All fields are required.']);
    exit;
}

// Check if email already exists
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Email already registered.']);
    exit;
}

// Get role_id from roles table
$stmt = $pdo->prepare('SELECT id FROM roles WHERE name = ?');
$stmt->execute([$role]);
$roleRow = $stmt->fetch();
if (!$roleRow) {
    echo json_encode(['success' => false, 'error' => 'Invalid role selected.']);
    exit;
}
$role_id = $roleRow['id'];

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user into users table
try {
    $stmt = $pdo->prepare('INSERT INTO users (username, password, email, role_id) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $hashedPassword, $email, $role_id]);
    echo json_encode(['success' => true, 'message' => 'Registration successful.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Registration failed: ' . $e->getMessage()]);
} 