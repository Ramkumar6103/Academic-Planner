<?php
// Academic Planner Setup Script
// Run this file once to set up the application

$setup_complete = false;
$messages = [];

if ($_POST) {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? 'password';
    $db_name = $_POST['db_name'] ?? 'academic_planner';
    
    try {
        // Test database connection
        $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
        $pdo->exec("USE `$db_name`");
        
        // Read and execute SQL file
        $sql = file_get_contents('database.sql');
        $sql = str_replace('USE academic_planner;', '', $sql); // Remove USE statement
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        // Update database configuration
        $db_config = "<?php
// Database configuration
define('DB_HOST', '$db_host');
define('DB_USER', '$db_user');
define('DB_PASS', '$db_pass');
define('DB_NAME', '$db_name');

// Create connection
try {
    \$pdo = new PDO(\"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME, DB_USER, DB_PASS);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException \$e) {
    die(\"Connection failed: \" . \$e->getMessage());
}

// Function to sanitize input
function sanitize(\$data) {
    return htmlspecialchars(strip_tags(trim(\$data)));
}

// Function to generate random password
function generatePassword(\$length = 8) {
    \$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle(\$chars), 0, \$length);
}
?>";
        
        file_put_contents('includes/db.php', $db_config);
        
        // Create necessary directories
        $directories = [
            'assets/images/events',
            'assets/assignments'
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }
        
        $messages[] = ['type' => 'success', 'text' => 'Database setup completed successfully!'];
        $messages[] = ['type' => 'success', 'text' => 'Required directories created.'];
        $messages[] = ['type' => 'info', 'text' => 'Default admin login: admin@college.edu / password'];
        $setup_complete = true;
        
    } catch (Exception $e) {
        $messages[] = ['type' => 'error', 'text' => 'Setup failed: ' . $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Planner Setup</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .setup-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
        }
        .setup-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 1rem;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .alert {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .success-actions {
            text-align: center;
            margin-top: 2rem;
        }
        .success-actions a {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 0 0.5rem;
        }
        .success-actions a:hover {
            background: #229954;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>🎓 Academic Planner Setup</h1>
        
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?php echo $message['type']; ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endforeach; ?>
        
        <?php if (!$setup_complete): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="db_host">Database Host:</label>
                    <input type="text" id="db_host" name="db_host" class="form-control" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label for="db_user">Database Username:</label>
                    <input type="text" id="db_user" name="db_user" class="form-control" value="root" required>
                </div>
                
                <div class="form-group">
                    <label for="db_pass">Database Password:</label>
                    <input type="password" id="db_pass" name="db_pass" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="db_name">Database Name:</label>
                    <input type="text" id="db_name" name="db_name" class="form-control" value="academic_planner" required>
                </div>
                
                <button type="submit" class="btn">Setup Academic Planner</button>
            </form>
            
            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #eee; font-size: 0.9rem; color: #666;">
                <p><strong>What this setup will do:</strong></p>
                <ul style="margin: 0.5rem 0; padding-left: 1.5rem;">
                    <li>Create the database and tables</li>
                    <li>Insert sample data (admin, faculty, students)</li>
                    <li>Create required directories</li>
                    <li>Configure database connection</li>
                </ul>
            </div>
        <?php else: ?>
            <div class="success-actions">
                <h3>🎉 Setup Complete!</h3>
                <p>Your Academic Planner is ready to use.</p>
                <a href="index.php">Go to Login Page</a>
                <a href="admin/dashboard.php" style="background: #667eea;">Admin Dashboard</a>
            </div>
            
            <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 5px; font-size: 0.9rem;">
                <p><strong>Sample Login Credentials:</strong></p>
                <ul style="margin: 0.5rem 0; padding-left: 1.5rem;">
                    <li><strong>Admin:</strong> admin@college.edu / password</li>
                    <li><strong>Faculty:</strong> john.smith@college.edu / password</li>
                    <li><strong>Student:</strong> alice.wilson@student.edu / password</li>
                </ul>
                <p style="margin-top: 1rem; color: #666;"><em>Remember to delete this setup.php file after installation for security.</em></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>