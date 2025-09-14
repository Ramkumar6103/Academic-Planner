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

$timetable = [];
if ($student_info && $student_info['class_id']) {
    // Get class timetable
    $stmt = $pdo->prepare("
        SELECT t.*, s.name as subject_name, u.name as faculty_name 
        FROM timetable t 
        JOIN subjects s ON t.subject_id = s.id 
        JOIN faculty f ON t.faculty_id = f.id 
        JOIN users u ON f.user_id = u.id 
        WHERE t.class_id = ? 
        ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), t.time_slot
    ");
    $stmt->execute([$student_info['class_id']]);
    $timetable_data = $stmt->fetchAll();
    
    // Organize timetable by day and time
    foreach ($timetable_data as $entry) {
        $timetable[$entry['day_of_week']][$entry['time_slot']] = $entry;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Timetable - Academic Planner</title>
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
                <li><a href="view_assignments.php">Assignments</a></li>
                <li><a href="view_timetable.php" class="active">Timetable</a></li>
                <li><a href="view_exam_schedule.php">Exam Schedule</a></li>
                <li><a href="view_calendar.php">Calendar</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <h2>Class Timetable</h2>
            
            <?php if ($student_info): ?>
                <p style="color: #666; margin-bottom: 2rem;">
                    <strong>Class:</strong> <?php echo htmlspecialchars($student_info['class_name'] ?? 'Not assigned'); ?> | 
                    <strong>Roll No:</strong> <?php echo htmlspecialchars($student_info['roll_no']); ?>
                </p>
            <?php endif; ?>
            
            <?php if (!empty($timetable)): ?>
                <div class="timetable">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Monday</th>
                                <th>Tuesday</th>
                                <th>Wednesday</th>
                                <th>Thursday</th>
                                <th>Friday</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $time_slots = ['9:00-10:00', '10:00-11:00', '11:30-12:30', '12:30-1:30', '2:30-3:30', '3:30-4:30'];
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                            
                            foreach ($time_slots as $slot): ?>
                                <tr>
                                    <td><strong><?php echo $slot; ?></strong></td>
                                    <?php foreach ($days as $day): ?>
                                        <td>
                                            <?php if (isset($timetable[$day][$slot])): 
                                                $entry = $timetable[$day][$slot]; ?>
                                                <div style="background: #e3f2fd; padding: 0.5rem; border-radius: 4px; font-size: 0.9rem;">
                                                    <strong><?php echo htmlspecialchars($entry['subject_name']); ?></strong><br>
                                                    <small><?php echo htmlspecialchars($entry['faculty_name']); ?></small><br>
                                                    <small><?php echo htmlspecialchars($entry['room']); ?></small>
                                                </div>
                                            <?php else: ?>
                                                <div style="color: #999; font-style: italic;">Free</div>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Print Button -->
                <div style="margin-top: 2rem; text-align: center;">
                    <button onclick="window.print()" class="btn">Print Timetable</button>
                </div>
                
            <?php elseif ($student_info && $student_info['class_id']): ?>
                <div class="alert alert-info">
                    No timetable available for your class yet. Please contact the administrator.
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    You are not assigned to any class yet. Please contact the administrator.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        @media print {
            .header, .nav, .btn { display: none !important; }
            .main-content { box-shadow: none; }
        }
    </style>
</body>
</html>