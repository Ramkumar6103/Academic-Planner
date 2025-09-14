<?php
require_once '../includes/auth.php';
requireRole('admin');

$message = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_faculty'])) {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $subject = sanitize($_POST['subject']);
        $phone = sanitize($_POST['phone']);
        $qualification = sanitize($_POST['qualification']);
        $password = password_hash('password', PASSWORD_DEFAULT);
        
        try {
            $pdo->beginTransaction();
            
            // Insert user
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'faculty')");
            $stmt->execute([$name, $email, $password]);
            $user_id = $pdo->lastInsertId();
            
            // Insert faculty
            $stmt = $pdo->prepare("INSERT INTO faculty (user_id, subject, phone, qualification) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $subject, $phone, $qualification]);
            
            $pdo->commit();
            $message = '<div class="alert alert-success">Faculty added successfully! Default password: password</div>';
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
    
    if (isset($_POST['delete_faculty'])) {
        $faculty_id = (int)$_POST['faculty_id'];
        
        try {
            $stmt = $pdo->prepare("SELECT user_id FROM faculty WHERE id = ?");
            $stmt->execute([$faculty_id]);
            $user_id = $stmt->fetchColumn();
            
            if ($user_id) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $message = '<div class="alert alert-success">Faculty deleted successfully!</div>';
            }
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Get all faculty
$stmt = $pdo->query("
    SELECT f.*, u.name, u.email 
    FROM faculty f 
    JOIN users u ON f.user_id = u.id 
    ORDER BY u.name
");
$faculty = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty - Academic Planner</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>Academic Planner</h1>
                </div>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['user_name']; ?></span>
                    <a href="../includes/auth.php?logout=1" class="logout-btn">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <nav class="nav">
        <div class="container">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage_students.php">Students</a></li>
                <li><a href="manage_faculty.php" class="active">Faculty</a></li>
                <li><a href="generate_timetable.php">Timetable</a></li>
                <li><a href="generate_exam_seating.php">Exam Seating</a></li>
                <li><a href="manage_events.php">Events</a></li>
                <li><a href="manage_attendance.php">Attendance</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <h2>Manage Faculty</h2>
            
            <?php echo $message; ?>
            
            <!-- Add Faculty Form -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3>Add New Faculty</h3>
                <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject:</label>
                        <input type="text" id="subject" name="subject" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone:</label>
                        <input type="text" id="phone" name="phone" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="qualification">Qualification:</label>
                        <input type="text" id="qualification" name="qualification" class="form-control">
                    </div>
                    
                    <div style="grid-column: 1 / -1;">
                        <button type="submit" name="add_faculty" class="btn">Add Faculty</button>
                    </div>
                </form>
            </div>
            
            <!-- Faculty List -->
            <h3>All Faculty</h3>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Phone</th>
                            <th>Qualification</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($faculty as $member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['name']); ?></td>
                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                <td><?php echo htmlspecialchars($member['subject']); ?></td>
                                <td><?php echo htmlspecialchars($member['phone'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($member['qualification'] ?? 'N/A'); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this faculty member?')">
                                        <input type="hidden" name="faculty_id" value="<?php echo $member['id']; ?>">
                                        <button type="submit" name="delete_faculty" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>