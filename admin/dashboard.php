<?php
require_once '../includes/auth.php';
requireRole('admin');

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
$student_count = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM faculty");
$faculty_count = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM classes");
$class_count = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM events WHERE event_date >= CURDATE()");
$upcoming_events = $stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Academic Planner</title>
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
                <li><a href="manage_students.php">Students</a></li>
                <li><a href="manage_faculty.php">Faculty</a></li>
                <li><a href="generate_timetable.php">Timetable</a></li>
                <li><a href="generate_exam_seating.php">Exam Seating</a></li>
                <li><a href="manage_events.php">Events</a></li>
                <li><a href="manage_attendance.php">Attendance</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <h2>Admin Dashboard</h2>
            
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-icon">👥</div>
                    <h3>Total Students</h3>
                    <p><?php echo $student_count; ?></p>
                </div>
                
                <div class="card">
                    <div class="card-icon">👨‍🏫</div>
                    <h3>Total Faculty</h3>
                    <p><?php echo $faculty_count; ?></p>
                </div>
                
                <div class="card">
                    <div class="card-icon">🏫</div>
                    <h3>Total Classes</h3>
                    <p><?php echo $class_count; ?></p>
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
                        <a href="manage_students.php" class="btn">Manage Students</a>
                        <a href="manage_faculty.php" class="btn">Manage Faculty</a>
                        <a href="generate_timetable.php" class="btn">Generate Timetable</a>
                        <a href="generate_exam_seating.php" class="btn">Generate Exam Seating</a>
                    </div>
                </div>
                
                <div>
                    <h3>Recent Activities</h3>
                    <div style="margin-top: 1rem;">
                        <?php
                        $stmt = $pdo->query("SELECT e.title, e.event_date FROM events e ORDER BY e.created_at DESC LIMIT 5");
                        $recent_events = $stmt->fetchAll();
                        
                        if ($recent_events): ?>
                            <ul style="list-style: none; padding: 0;">
                                <?php foreach ($recent_events as $event): ?>
                                    <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                        <strong><?php echo htmlspecialchars($event['title']); ?></strong><br>
                                        <small>Date: <?php echo date('M d, Y', strtotime($event['event_date'])); ?></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No recent events</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>