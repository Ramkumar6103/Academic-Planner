<?php
require_once '../includes/auth.php';
requireRole('faculty');

// Get faculty info
$stmt = $pdo->prepare("SELECT * FROM faculty WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$faculty_info = $stmt->fetch();

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM assignments WHERE faculty_id = ?");
$stmt->execute([$faculty_info['id']]);
$assignment_count = $stmt->fetch()['count'];

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT t.class_id) as count 
    FROM timetable t 
    WHERE t.faculty_id = ?
");
$stmt->execute([$faculty_info['id']]);
$class_count = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM events WHERE event_date >= CURDATE()");
$upcoming_events = $stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - Academic Planner</title>
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
                <li><a href="view_timetable.php">My Timetable</a></li>
                <li><a href="upload_assignment.php">Assignments</a></li>
                <li><a href="post_notice.php">Post Notice</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <h2>Faculty Dashboard</h2>
            
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-icon">📚</div>
                    <h3>My Assignments</h3>
                    <p><?php echo $assignment_count; ?></p>
                </div>
                
                <div class="card">
                    <div class="card-icon">🏫</div>
                    <h3>Classes Teaching</h3>
                    <p><?php echo $class_count; ?></p>
                </div>
                
                <div class="card">
                    <div class="card-icon">📖</div>
                    <h3>Subject</h3>
                    <p style="font-size: 1.2rem;"><?php echo htmlspecialchars($faculty_info['subject']); ?></p>
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
                        <a href="view_timetable.php" class="btn">View My Timetable</a>
                        <a href="upload_assignment.php" class="btn">Upload Assignment</a>
                        <a href="post_notice.php" class="btn">Post Notice</a>
                    </div>
                </div>
                
                <div>
                    <h3>Recent Assignments</h3>
                    <div style="margin-top: 1rem;">
                        <?php
                        $stmt = $pdo->prepare("
                            SELECT a.title, a.due_date, s.name as subject_name 
                            FROM assignments a 
                            JOIN subjects s ON a.subject_id = s.id 
                            WHERE a.faculty_id = ? 
                            ORDER BY a.created_at DESC 
                            LIMIT 5
                        ");
                        $stmt->execute([$faculty_info['id']]);
                        $recent_assignments = $stmt->fetchAll();
                        
                        if ($recent_assignments): ?>
                            <ul style="list-style: none; padding: 0;">
                                <?php foreach ($recent_assignments as $assignment): ?>
                                    <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                        <strong><?php echo htmlspecialchars($assignment['title']); ?></strong><br>
                                        <small>Subject: <?php echo htmlspecialchars($assignment['subject_name']); ?></small><br>
                                        <small>Due: <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No assignments uploaded yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>