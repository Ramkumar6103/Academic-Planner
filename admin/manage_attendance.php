<?php
require_once '../includes/auth.php';
requireRole('admin');

$message = '';

// Handle attendance marking
if ($_POST && isset($_POST['mark_attendance'])) {
    $date = $_POST['date'];
    $subject_id = (int)$_POST['subject_id'];
    $attendance_data = $_POST['attendance'] ?? [];
    
    try {
        // Delete existing attendance for this date and subject
        $stmt = $pdo->prepare("DELETE FROM attendance WHERE date = ? AND subject_id = ?");
        $stmt->execute([$date, $subject_id]);
        
        // Insert new attendance records
        foreach ($attendance_data as $student_id => $status) {
            $stmt = $pdo->prepare("INSERT INTO attendance (student_id, subject_id, date, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$student_id, $subject_id, $date, $status]);
        }
        
        $message = '<div class="alert alert-success">Attendance marked successfully!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}

// Get subjects and classes for filters
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
$classes = $pdo->query("SELECT * FROM classes ORDER BY name")->fetchAll();

// Get students based on selected class
$selected_class = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$selected_subject = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

$students = [];
if ($selected_class) {
    $stmt = $pdo->prepare("
        SELECT s.*, u.name 
        FROM students s 
        JOIN users u ON s.user_id = u.id 
        WHERE s.class_id = ? 
        ORDER BY s.roll_no
    ");
    $stmt->execute([$selected_class]);
    $students = $stmt->fetchAll();
}

// Get existing attendance for the selected date and subject
$existing_attendance = [];
if ($selected_date && $selected_subject) {
    $stmt = $pdo->prepare("SELECT student_id, status FROM attendance WHERE date = ? AND subject_id = ?");
    $stmt->execute([$selected_date, $selected_subject]);
    $attendance_records = $stmt->fetchAll();
    
    foreach ($attendance_records as $record) {
        $existing_attendance[$record['student_id']] = $record['status'];
    }
}

// Get attendance statistics
$attendance_stats = [];
if ($selected_class && $selected_subject) {
    $stmt = $pdo->prepare("
        SELECT s.id, u.name, s.roll_no,
               COUNT(a.id) as total_classes,
               SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
               ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 2) as attendance_percentage
        FROM students s 
        JOIN users u ON s.user_id = u.id 
        LEFT JOIN attendance a ON s.id = a.student_id AND a.subject_id = ?
        WHERE s.class_id = ?
        GROUP BY s.id, u.name, s.roll_no
        ORDER BY s.roll_no
    ");
    $stmt->execute([$selected_subject, $selected_class]);
    $attendance_stats = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendance - Academic Planner</title>
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
                <li><a href="manage_faculty.php">Faculty</a></li>
                <li><a href="generate_timetable.php">Timetable</a></li>
                <li><a href="generate_exam_seating.php">Exam Seating</a></li>
                <li><a href="manage_events.php">Events</a></li>
                <!-- <li><a href="manage_attendance.php" class="active">Attendance</a></li> -->
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <h2>Manage Attendance</h2>
            
            <?php echo $message; ?>
            
            <!-- Filters -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3>Select Class and Subject</h3>
                <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="class_id">Class:</label>
                        <select id="class_id" name="class_id" class="form-control" onchange="this.form.submit()">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo $selected_class == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject_id">Subject:</label>
                        <select id="subject_id" name="subject_id" class="form-control" onchange="this.form.submit()">
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>" <?php echo $selected_subject == $subject['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($subject['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date">Date:</label>
                        <input type="date" id="date" name="date" class="form-control" value="<?php echo $selected_date; ?>" onchange="this.form.submit()">
                    </div>
                    
                    <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>">
                    <input type="hidden" name="subject_id" value="<?php echo $selected_subject; ?>">
                </form>
            </div>
            
            <?php if ($selected_class && $selected_subject && !empty($students)): ?>
                <!-- Mark Attendance -->
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                    <h3>Mark Attendance for <?php echo date('M d, Y', strtotime($selected_date)); ?></h3>
                    <form method="POST">
                        <input type="hidden" name="date" value="<?php echo $selected_date; ?>">
                        <input type="hidden" name="subject_id" value="<?php echo $selected_subject; ?>">
                        
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Roll No</th>
                                    <th>Student Name</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Late</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): 
                                    $current_status = $existing_attendance[$student['id']] ?? 'absent';
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td>
                                            <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="present" 
                                                   <?php echo $current_status == 'present' ? 'checked' : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="absent" 
                                                   <?php echo $current_status == 'absent' ? 'checked' : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="late" 
                                                   <?php echo $current_status == 'late' ? 'checked' : ''; ?>>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <button type="submit" name="mark_attendance" class="btn">Save Attendance</button>
                    </form>
                </div>
                
                <!-- Attendance Statistics -->
                <?php if (!empty($attendance_stats)): ?>
                    <h3>Attendance Statistics</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Total Classes</th>
                                <th>Present</th>
                                <th>Attendance %</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance_stats as $stat): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($stat['roll_no']); ?></td>
                                    <td><?php echo htmlspecialchars($stat['name']); ?></td>
                                    <td><?php echo $stat['total_classes']; ?></td>
                                    <td><?php echo $stat['present_count']; ?></td>
                                    <td>
                                        <span style="color: <?php echo $stat['attendance_percentage'] >= 75 ? 'green' : 'red'; ?>;">
                                            <?php echo $stat['attendance_percentage'] ?? 0; ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($stat['attendance_percentage'] >= 75): ?>
                                            <span style="color: green;">Good</span>
                                        <?php elseif ($stat['attendance_percentage'] >= 60): ?>
                                            <span style="color: orange;">Average</span>
                                        <?php else: ?>
                                            <span style="color: red;">Poor</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                
            <?php elseif ($selected_class && $selected_subject): ?>
                <div class="alert alert-info">No students found for the selected class.</div>
            <?php else: ?>
                <div class="alert alert-info">Please select a class and subject to manage attendance.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>