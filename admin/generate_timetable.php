<?php
require_once '../includes/auth.php';
requireRole('admin');

$message = '';

// Handle timetable generation
if ($_POST && isset($_POST['generate_timetable'])) {
    $class_id = (int)$_POST['class_id'];
    
    try {
        // Clear existing timetable for this class
        $stmt = $pdo->prepare("DELETE FROM timetable WHERE class_id = ?");
        $stmt->execute([$class_id]);
        
        // Get subjects and faculty
        $subjects = $pdo->query("SELECT * FROM subjects")->fetchAll();
        $faculty = $pdo->query("SELECT * FROM faculty")->fetchAll();
        
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $time_slots = ['9:00-10:00', '10:00-11:00', '11:30-12:30', '12:30-1:30', '2:30-3:30', '3:30-4:30'];
        
        // Generate random timetable
        foreach ($days as $day) {
            foreach ($time_slots as $slot) {
                if (rand(0, 1)) { // 50% chance of having a class
                    $subject = $subjects[array_rand($subjects)];
                    $faculty_member = $faculty[array_rand($faculty)];
                    $room = 'Room ' . rand(101, 110);
                    
                    $stmt = $pdo->prepare("INSERT INTO timetable (class_id, subject_id, faculty_id, day_of_week, time_slot, room) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$class_id, $subject['id'], $faculty_member['id'], $day, $slot, $room]);
                }
            }
        }
        
        $message = '<div class="alert alert-success">Timetable generated successfully!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}

// Get classes
$classes = $pdo->query("SELECT * FROM classes ORDER BY name")->fetchAll();

// Get current timetable if class is selected
$selected_class = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$timetable = [];

if ($selected_class) {
    $stmt = $pdo->prepare("
        SELECT t.*, s.name as subject_name, u.name as faculty_name 
        FROM timetable t 
        JOIN subjects s ON t.subject_id = s.id 
        JOIN faculty f ON t.faculty_id = f.id 
        JOIN users u ON f.user_id = u.id 
        WHERE t.class_id = ? 
        ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), t.time_slot
    ");
    $stmt->execute([$selected_class]);
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
    <title>Generate Timetable - Academic Planner</title>
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
                <li><a href="generate_timetable.php" class="active">Timetable</a></li>
                <li><a href="generate_exam_seating.php">Exam Seating</a></li>
                <li><a href="manage_events.php">Events</a></li>
                <!-- <li><a href="manage_attendance.php">Attendance</a></li> -->
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <h2>Generate Timetable</h2>
            
            <?php echo $message; ?>
            
            <!-- Generate Timetable Form -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3>Generate New Timetable</h3>
                <form method="POST" style="display: flex; gap: 1rem; align-items: end; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="class_id">Select Class:</label>
                        <select id="class_id" name="class_id" class="form-control" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="generate_timetable" class="btn">Generate Timetable</button>
                </form>
            </div>
            
            <!-- View Timetable -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3>View Existing Timetable</h3>
                <form method="GET" style="display: flex; gap: 1rem; align-items: end; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="view_class_id">Select Class:</label>
                        <select id="view_class_id" name="class_id" class="form-control" onchange="this.form.submit()">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo $selected_class == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            
            <?php if ($selected_class && !empty($timetable)): ?>
                <h3>Timetable for <?php 
                    $class_name = '';
                    foreach ($classes as $class) {
                        if ($class['id'] == $selected_class) {
                            $class_name = $class['name'];
                            break;
                        }
                    }
                    echo htmlspecialchars($class_name);
                ?></h3>
                
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
            <?php elseif ($selected_class): ?>
                <div class="alert alert-info">No timetable found for this class. Generate one using the form above.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>