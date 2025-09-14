<?php
session_start();
require_once 'db.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Check user role
function hasRole($required_role) {
    return isLoggedIn() && $_SESSION['role'] === $required_role;
}

// Redirect if not authorized
function requireRole($required_role) {
    if (!hasRole($required_role)) {
        header('Location: ../index.php');
        exit();
    }
}

// Login function
function login($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

// Logout function
function logout() {
    session_destroy();
    header('Location: ../index.php');
    exit();
}

// Handle logout request
if (isset($_GET['logout'])) {
    logout();
}

// Get current user info
function getCurrentUser() {
    global $pdo;
    
    if (!isLoggedIn()) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Get user dashboard URL based on role
function getDashboardUrl($role) {
    switch($role) {
        case 'admin': return 'admin/dashboard.php';
        case 'faculty': return 'faculty/dashboard.php';
        case 'student': return 'student/dashboard.php';
        default: return 'index.php';
    }
}
?>