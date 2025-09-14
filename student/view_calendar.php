<?php
require_once '../includes/auth.php';
requireRole('student');

// Get all events
$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
$events = $stmt->fetchAll();

// Separate upcoming and past events
$upcoming_events = [];
$past_events = [];
$today = date('Y-m-d');

foreach ($events as $event) {
    if ($event['event_date'] >= $today) {
        $upcoming_events[] = $event;
    } else {
        $past_events[] = $event;
    }
}

// Sort upcoming events by date (ascending)
usort($upcoming_events, function($a, $b) {
    return strtotime($a['event_date']) - strtotime($b['event_date']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Calendar - Academic Planner</title>
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
                <li><a href="view_timetable.php">Timetable</a></li>
                <li><a href="view_exam_schedule.php">Exam Schedule</a></li>
                <li><a href="view_calendar.php" class="active">Calendar</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <h2>Academic Calendar & Events</h2>
            
            <!-- Upcoming Events -->
            <?php if (!empty($upcoming_events)): ?>
                <div style="margin-bottom: 3rem;">
                    <h3 style="color: #27ae60; margin-bottom: 1.5rem;">🔜 Upcoming Events</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                        <?php foreach ($upcoming_events as $event): 
                            $days_until = ceil((strtotime($event['event_date']) - time()) / (60 * 60 * 24));
                        ?>
                            <div style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; border-left: 4px solid #27ae60;">
                                <?php if ($event['image_path']): ?>
                                    <img src="../<?php echo htmlspecialchars($event['image_path']); ?>" 
                                         alt="Event Image" 
                                         style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 1rem;">
                                <?php endif; ?>
                                
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                    <h4 style="margin: 0; color: #333;"><?php echo htmlspecialchars($event['title']); ?></h4>
                                    <div style="background: #27ae60; color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.8rem;">
                                        <?php if ($days_until == 0): ?>
                                            Today
                                        <?php elseif ($days_until == 1): ?>
                                            Tomorrow
                                        <?php else: ?>
                                            <?php echo $days_until; ?> days
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <p style="color: #666; margin-bottom: 1rem;">
                                    <strong>📅 Date:</strong> <?php echo date('l, M d, Y', strtotime($event['event_date'])); ?>
                                </p>
                                
                                <?php if ($event['description']): ?>
                                    <p style="color: #555; line-height: 1.6; margin: 0;">
                                        <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Past Events -->
            <?php if (!empty($past_events)): ?>
                <div>
                    <h3 style="color: #666; margin-bottom: 1.5rem;">📚 Past Events</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                        <?php foreach (array_slice($past_events, 0, 6) as $event): ?>
                            <div style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; opacity: 0.8;">
                                <?php if ($event['image_path']): ?>
                                    <img src="../<?php echo htmlspecialchars($event['image_path']); ?>" 
                                         alt="Event Image" 
                                         style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 1rem;">
                                <?php endif; ?>
                                
                                <h4 style="margin: 0 0 1rem 0; color: #333;"><?php echo htmlspecialchars($event['title']); ?></h4>
                                
                                <p style="color: #666; margin-bottom: 1rem;">
                                    <strong>📅 Date:</strong> <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                </p>
                                
                                <?php if ($event['description']): ?>
                                    <p style="color: #555; line-height: 1.6; margin: 0; font-size: 0.9rem;">
                                        <?php echo nl2br(htmlspecialchars(substr($event['description'], 0, 150))); ?>
                                        <?php if (strlen($event['description']) > 150): ?>...<?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($past_events) > 6): ?>
                        <div style="text-align: center; margin-top: 2rem;">
                            <p style="color: #666;">Showing recent 6 events. Total past events: <?php echo count($past_events); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- No Events Message -->
            <?php if (empty($upcoming_events) && empty($past_events)): ?>
                <div class="alert alert-info" style="text-align: center; padding: 3rem;">
                    <h3>📅 No Events Available</h3>
                    <p>There are no events scheduled at the moment. Check back later for updates!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>