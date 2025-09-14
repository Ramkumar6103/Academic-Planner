<?php
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . getDashboardUrl($_SESSION['role']));
    exit();
}

$error = '';

if ($_POST) {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (login($email, $password)) {
        header('Location: ' . getDashboardUrl($_SESSION['role']));
        exit();
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Planner - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <form class="login-form" method="POST">
            <h2>Academic Planner</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Login</button>
            
            <div style="margin-top: 1rem; text-align: center; font-size: 0.9rem; color: #666;">
                <p><strong>Demo Credentials:</strong></p>
                <p>Admin: admin@college.edu / password</p>
                <p>Faculty: faculty@college.edu / password</p>
                <p>Student: student@college.edu / password</p>
            </div>
        </form>
    </div>
</body>
</html>