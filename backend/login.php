<?php
// backend/login.php
session_start();
require_once __DIR__ . '/config/db.php';
$pdo = require __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        echo 'Email and password are required.';
        exit;
    }

    // Fetch user by email
    $stmt = $pdo->prepare('SELECT u.id, u.username, u.password, u.role_id, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role_name'];

        // Redirect based on role
        if ($user['role_name'] === 'Admin') {
            header('Location: admin_dashboard.php');
            exit;
        } elseif ($user['role_name'] === 'Faculty') {
            header('Location: faculty_dashboard.php');
            exit;
        } elseif ($user['role_name'] === 'Student') {
            header('Location: student_dashboard.php');
            exit;
        } else {
            echo 'Unknown user role.';
            exit;
        }
    } else {
        echo 'Invalid email or password.';
        exit;
    }
} else {
    echo 'Invalid request method.';
    exit;
} 