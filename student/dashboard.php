<?php
require_once '../includes/auth.php';
requireRole('student');

// Get student info
$stmt = $pdo->prepare("
    SELECT s.*, c.name as class_name 
    FROM students s 
    LEFT JOIN classes c ON s.class_id = c.id 
    WHERE s.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$student_info = $stmt->fetch();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM assignments");
$assignment_count = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM events WHERE event_date >= CURDATE()");
$upcoming_events = $stmt->fetch()['count'];

// Get attendance percentage
$attendance_percentage = 0;
if ($student_info) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_classes,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count
        FROM attendance 
        WHERE student_id = ?
    ");
    $stmt->execute([$student_info['id']]);
    $attendance_data = $stmt->fetch();
    
    if ($attendance_data['total_classes'] > 0) {
        $attendance_percentage = round(($attendance_data['present_count'] / $attendance_data['total_classes']) * 100, 2);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Academic Planner</title>
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
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="view_assignments.php">Assignments</a></li>
                <li><a href="view_timetable.php">Timetable</a></li>
                <li><a href="view_exam_schedule.php">Exam Schedule</a></li>
                <li><a href="view_calendar.php">Calendar</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <h2>Student Dashboard</h2>
            
            <?php if ($student_info): ?>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
                    <p><strong>Class:</strong> <?php echo htmlspecialchars($student_info['class_name'] ?? 'Not assigned'); ?></p>
                    <p><strong>Roll Number:</strong> <?php echo htmlspecialchars($student_info['roll_no']); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-icon">📚</div>
                    <h3>Total Assignments</h3>
                    <p><?php echo $assignment_count; ?></p>
                </div>
                
                <div class="card">
                    <div class="card-icon">📊</div>
                    <h3>Attendance</h3>
                    <p style="color: <?php echo $attendance_percentage >= 75 ? 'green' : 'red'; ?>;">
                        <?php echo $attendance_percentage; ?>%
                    </p>
                </div>
                
                <div class="card">
                    <div class="card-icon">🏫</div>
                    <h3>Class</h3>
                    <p style="font-size: 1.2rem;"><?php echo htmlspecialchars($student_info['class_name'] ?? 'N/A'); ?></p>
                </div>
                
                <div class="card">
                    <div class="card-icon">📅</div>
                    <h3>Upcoming Events</h3>
                    <p><?php echo $upcoming_events; ?></p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div>
                    <h3>Quick Actions</h3>
                    <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                        <a href="view_assignments.php" class="btn">View Assignments</a>
                        <a href="view_timetable.php" class="btn">View Timetable</a>
                        <a href="view_exam_schedule.php" class="btn">Exam Schedule</a>
                        <a href="view_calendar.php" class="btn">Academic Calendar</a>
                    </div>
                </div>
                
                <div>
                    <h3>Recent Assignments</h3>
                    <div style="margin-top: 1rem;">
                        <?php
                        $stmt = $pdo->query("
                            SELECT a.title, a.due_date, s.name as subject_name 
                            FROM assignments a 
                            JOIN subjects s ON a.subject_id = s.id 
                            ORDER BY a.created_at DESC 
                            LIMIT 5
                        ");
                        $recent_assignments = $stmt->fetchAll();
                        
                        if ($recent_assignments): ?>
                            <ul style="list-style: none; padding: 0;">
                                <?php foreach ($recent_assignments as $assignment): ?>
                                    <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                        <strong><?php echo htmlspecialchars($assignment['title']); ?></strong><br>
                                        <small>Subject: <?php echo htmlspecialchars($assignment['subject_name']); ?></small><br>
                                        <small style="color: <?php echo strtotime($assignment['due_date']) < time() ? 'red' : 'green'; ?>;">
                                            Due: <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?>
                                        </small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No assignments available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>