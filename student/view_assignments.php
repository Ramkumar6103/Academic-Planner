<?php
require_once '../includes/auth.php';
requireRole('student');

// Get all assignments with subject and faculty info
$stmt = $pdo->query("
    SELECT a.*, s.name as subject_name, u.name as faculty_name 
    FROM assignments a 
    JOIN subjects s ON a.subject_id = s.id 
    JOIN faculty f ON a.faculty_id = f.id 
    JOIN users u ON f.user_id = u.id 
    ORDER BY a.due_date ASC
");
$assignments = $stmt->fetchAll();

// Get subjects for filtering
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();

// Filter by subject if selected
$selected_subject = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
if ($selected_subject) {
    $stmt = $pdo->prepare("
        SELECT a.*, s.name as subject_name, u.name as faculty_name 
        FROM assignments a 
        JOIN subjects s ON a.subject_id = s.id 
        JOIN faculty f ON a.faculty_id = f.id 
        JOIN users u ON f.user_id = u.id 
        WHERE a.subject_id = ?
        ORDER BY a.due_date ASC
    ");
    $stmt->execute([$selected_subject]);
    $assignments = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assignments - Academic Planner</title>
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
                <li><a href="view_assignments.php" class="active">Assignments</a></li>
                <li><a href="view_timetable.php">Timetable</a></li>
                <li><a href="view_exam_schedule.php">Exam Schedule</a></li>
                <li><a href="view_calendar.php">Calendar</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <h2>Assignments</h2>
            
            <!-- Filter by Subject -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3>Filter by Subject</h3>
                <form method="GET" style="display: flex; gap: 1rem; align-items: end; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="subject_id">Subject:</label>
                        <select id="subject_id" name="subject_id" class="form-control" onchange="this.form.submit()">
                            <option value="">All Subjects</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>" <?php echo $selected_subject == $subject['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($subject['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            
            <!-- Assignments List -->
            <?php if (!empty($assignments)): ?>
                <div style="display: grid; gap: 1.5rem;">
                    <?php foreach ($assignments as $assignment): 
                        $is_overdue = strtotime($assignment['due_date']) < time();
                        $is_due_soon = strtotime($assignment['due_date']) < strtotime('+3 days');
                    ?>
                        <div style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; <?php echo $is_overdue ? 'border-left: 4px solid #e74c3c;' : ($is_due_soon ? 'border-left: 4px solid #f39c12;' : 'border-left: 4px solid #27ae60;'); ?>">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h4 style="margin: 0; color: #333;"><?php echo htmlspecialchars($assignment['title']); ?></h4>
                                    <p style="margin: 0.5rem 0; color: #666;">
                                        <strong>Subject:</strong> <?php echo htmlspecialchars($assignment['subject_name']); ?> | 
                                        <strong>Faculty:</strong> <?php echo htmlspecialchars($assignment['faculty_name']); ?>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="background: <?php echo $is_overdue ? '#e74c3c' : ($is_due_soon ? '#f39c12' : '#27ae60'); ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.8rem; margin-bottom: 0.5rem;">
                                        <?php echo $is_overdue ? 'Overdue' : ($is_due_soon ? 'Due Soon' : 'Active'); ?>
                                    </div>
                                    <small style="color: #666;">
                                        Due: <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <?php if ($assignment['description']): ?>
                                <div style="margin-bottom: 1rem;">
                                    <h5>Description:</h5>
                                    <p style="color: #555; line-height: 1.6;">
                                        <?php echo nl2br(htmlspecialchars($assignment['description'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #eee;">
                                <small style="color: #999;">
                                    Posted on <?php echo date('M d, Y', strtotime($assignment['created_at'])); ?>
                                </small>
                                
                                <?php if ($assignment['file_path']): ?>
                                    <a href="../<?php echo htmlspecialchars($assignment['file_path']); ?>" target="_blank" class="btn" style="padding: 0.5rem 1rem;">
                                        📎 Download File
                                    </a>
                                <?php else: ?>
                                    <span style="color: #999; font-style: italic;">No file attached</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <?php if ($selected_subject): ?>
                        No assignments found for the selected subject.
                    <?php else: ?>
                        No assignments available at the moment.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>