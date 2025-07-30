<?php
// backend/signup_form.php
session_start();
require_once __DIR__ . '/config/db.php';
$pdo = require __DIR__ . '/config/db.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = trim($_POST['role'] ?? '');

    if (!$name || !$email || !$password || !$role) {
        $message = 'All fields are required.';
        $message_type = 'danger';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = 'Email already registered.';
            $message_type = 'danger';
        } else {
            // Get role_id from roles table
            $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = ?');
            $stmt->execute([$role]);
            $roleRow = $stmt->fetch();
            if (!$roleRow) {
                $message = 'Invalid role selected.';
                $message_type = 'danger';
            } else {
                $role_id = $roleRow['id'];
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                // Insert user into users table
                try {
                    $stmt = $pdo->prepare('INSERT INTO users (username, password, email, role_id) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$name, $hashedPassword, $email, $role_id]);
                    $user_id = $pdo->lastInsertId();
                    
                    // If registering as Student, create student record
                    if ($role === 'Student') {
                        // Get default department (first department)
                        $dept_stmt = $pdo->query('SELECT id FROM departments LIMIT 1');
                        $dept = $dept_stmt->fetch();
                        $department_id = $dept ? $dept['id'] : 1;
                        
                        // Generate register number (you can modify this logic)
                        $register_number = 'STU' . date('Y') . str_pad($user_id, 4, '0', STR_PAD_LEFT);
                        
                        $stmt = $pdo->prepare('INSERT INTO students (user_id, department_id, register_number, name) VALUES (?, ?, ?, ?)');
                        $stmt->execute([$user_id, $department_id, $register_number, $name]);
                    }
                    
                    // If registering as Faculty, create faculty record
                    if ($role === 'Faculty') {
                        // Get default department (first department)
                        $dept_stmt = $pdo->query('SELECT id FROM departments LIMIT 1');
                        $dept = $dept_stmt->fetch();
                        $department_id = $dept ? $dept['id'] : 1;
                        
                        $stmt = $pdo->prepare('INSERT INTO faculty (user_id, department_id, name) VALUES (?, ?, ?)');
                        $stmt->execute([$user_id, $department_id, $name]);
                    }
                    
                    $message = 'Registration successful! You can now login.';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $message = 'Registration failed: ' . $e->getMessage();
                    $message_type = 'danger';
                }
            }
        }
    }
}

// Fetch available roles
$roles = $pdo->query('SELECT name FROM roles ORDER BY name')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Academic Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e3f0ff 0%, #f8fbff 100%);
            min-height: 100vh;
        }
        .signup-container {
            max-width: 500px;
            margin: 50px auto;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(26,35,126,0.1);
        }
        .card-header {
            background: linear-gradient(120deg, #1976d2 60%, #1a237e 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            text-align: center;
            padding: 20px;
        }
        .btn-primary {
            background: #1976d2;
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: #1565c0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="signup-container">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-user-plus me-2"></i>Sign Up</h3>
                    <p class="mb-0">Create your Academic Planner account</p>
                </div>
                <div class="card-body p-4">
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fa-solid fa-user me-2"></i>Full Name
                            </label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fa-solid fa-envelope me-2"></i>Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fa-solid fa-lock me-2"></i>Password
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="role" class="form-label">
                                <i class="fa-solid fa-users me-2"></i>Role
                            </label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select your role</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= htmlspecialchars($role['name']) ?>">
                                        <?= htmlspecialchars($role['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-user-plus me-2"></i>Sign Up
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p class="mb-0">Already have an account? 
                            <a href="login_form.php" class="text-decoration-none">
                                <i class="fa-solid fa-sign-in-alt me-1"></i>Login here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 