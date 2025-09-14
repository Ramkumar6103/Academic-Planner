<?php
require_once '../includes/auth.php';
requireRole('faculty');

// Get faculty info
$stmt = $pdo->prepare("SELECT * FROM faculty WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$faculty_info = $stmt->fetch();

// Get faculty timetable
$stmt = $pdo->prepare("
    SELECT t.*, s.name as subject_name, c.name as class_name 
    FROM timetable t 
    JOIN subjects s ON t.subject_id = s.id 
    JOIN classes c ON t.class_id = c.id 
    WHERE t.faculty_id = ? 
    ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), t.time_slot
");
$stmt->execute([$faculty_info['id']]);
$timetable_data = $stmt->fetchAll();

// Organize timetable by day and time
$timetable = [];
foreach ($timetable_data as $entry) {
    $timetable[$entry['day_of_week']][$entry['time_slot']] = $entry;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Timetable - Academic Planner</title>
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
                <li><a href="view_timetable.php" class="active">My Timetable</a></li>
                <li><a href="upload_assignment.php">Assignments</a></li>
                <li><a href="post_notice.php">Post Notice</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <h2>My Timetable</h2>
            <p style="color: #666; margin-bottom: 2rem;">Subject: <strong><?php echo htmlspecialchars($faculty_info['subject']); ?></strong></p>
            
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
                                                <div style="background: #e8f5e8; padding: 0.5rem; border-radius: 4px; font-size: 0.9rem;">
                                                    <strong><?php echo htmlspecialchars($entry['subject_name']); ?></strong><br>
                                                    <small><?php echo htmlspecialchars($entry['class_name']); ?></small><br>
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
                
            <?php else: ?>
                <div class="alert alert-info">
                    No timetable assigned yet. Please contact the administrator.
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