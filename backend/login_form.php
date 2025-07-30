<?php
// backend/login_form.php
session_start();
require_once __DIR__ . '/config/db.php';
$pdo = require __DIR__ . '/config/db.php';

$message = '';
$message_type = '';

// Check for logout message
if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $message_type = 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $message = 'Email and password are required.';
        $message_type = 'danger';
    } else {
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
                $message = 'Unknown user role.';
                $message_type = 'danger';
            }
        } else {
            $message = 'Invalid email or password.';
            $message_type = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Academic Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e3f0ff 0%, #f8fbff 100%);
            min-height: 100vh;
        }
        .login-container {
            max-width: 400px;
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
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-sign-in-alt me-2"></i>Login</h3>
                    <p class="mb-0">Welcome to Academic Planner</p>
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
                            <label for="email" class="form-label">
                                <i class="fa-solid fa-envelope me-2"></i>Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="fa-solid fa-lock me-2"></i>Password
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-sign-in-alt me-2"></i>Login
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p class="mb-0">Don't have an account? 
                            <a href="signup_form.php" class="text-decoration-none">
                                <i class="fa-solid fa-user-plus me-1"></i>Sign up here
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