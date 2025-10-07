<?php
require_once '../includes/auth.php';
requireRole('admin');

$message = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_student'])) {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $class_id = (int)$_POST['class_id'];
        $roll_no = sanitize($_POST['roll_no']);
        $phone = sanitize($_POST['phone']);
        $address = sanitize($_POST['address']);
        $password = password_hash('password', PASSWORD_DEFAULT);
        
        try {
            $pdo->beginTransaction();
            
            // Insert user
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
            $stmt->execute([$name, $email, $password]);
            $user_id = $pdo->lastInsertId();
            
            // Insert student
            $stmt = $pdo->prepare("INSERT INTO students (user_id, class_id, roll_no, phone, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $class_id, $roll_no, $phone, $address]);
            
            $pdo->commit();
            $message = '<div class="alert alert-success">Student added successfully! Default password: password</div>';
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
    
    if (isset($_POST['delete_student'])) {
        $student_id = (int)$_POST['student_id'];
        
        try {
            $stmt = $pdo->prepare("SELECT user_id FROM students WHERE id = ?");
            $stmt->execute([$student_id]);
            $user_id = $stmt->fetchColumn();
            
            if ($user_id) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $message = '<div class="alert alert-success">Student deleted successfully!</div>';
            }
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Get all students
$stmt = $pdo->query("
    SELECT s.*, u.name, u.email, c.name as class_name 
    FROM students s 
    JOIN users u ON s.user_id = u.id 
    LEFT JOIN classes c ON s.class_id = c.id 
    ORDER BY u.name
");
$students = $stmt->fetchAll();

// Get all classes
$stmt = $pdo->query("SELECT * FROM classes ORDER BY name");
$classes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Academic Planner</title>
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
                <li><a href="manage_students.php" class="active">Students</a></li>
                <li><a href="manage_faculty.php">Faculty</a></li>
                <li><a href="generate_timetable.php">Timetable</a></li>
                <li><a href="generate_exam_seating.php">Exam Seating</a></li>
                <li><a href="manage_events.php">Events</a></li>
                <!-- <li><a href="manage_attendance.php">Attendance</a></li> -->
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <h2>Manage Students</h2>
            
            <?php echo $message; ?>
            
            <!-- Add Student Form -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3>Add New Student</h3>
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
                        <label for="class_id">Class:</label>
                        <select id="class_id" name="class_id" class="form-control" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="roll_no">Roll Number:</label>
                        <input type="text" id="roll_no" name="roll_no" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone:</label>
                        <input type="text" id="phone" name="phone" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address:</label>
                        <textarea id="address" name="address" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div style="grid-column: 1 / -1;">
                        <button type="submit" name="add_student" class="btn">Add Student</button>
                    </div>
                </form>
            </div>
            
            <!-- Students List -->
            <h3>All Students</h3>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Class</th>
                            <th>Roll No</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
                                <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this student?')">
                                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                        <button type="submit" name="delete_student" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
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