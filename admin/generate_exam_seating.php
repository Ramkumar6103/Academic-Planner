<?php
require_once '../includes/auth.php';
requireRole('admin');

$message = '';

// Handle seating arrangement generation
if ($_POST && isset($_POST['generate_seating'])) {
    $exam_date = $_POST['exam_date'];
    $rooms = explode(',', $_POST['rooms']);
    
    try {
        // Clear existing seating for this date
        $stmt = $pdo->prepare("DELETE FROM exam_seating WHERE exam_date = ?");
        $stmt->execute([$exam_date]);
        
        // Get all students grouped by class
        $stmt = $pdo->query("
            SELECT s.*, c.name as class_name 
            FROM students s 
            LEFT JOIN classes c ON s.class_id = c.id 
            ORDER BY s.class_id, s.roll_no
        ");
        $students = $stmt->fetchAll();
        
        // Group students by class
        $students_by_class = [];
        foreach ($students as $student) {
            $class_id = $student['class_id'] ?? 'no_class';
            $students_by_class[$class_id][] = $student;
        }
        
        // Shuffle students within each class to randomize seating
        foreach ($students_by_class as &$class_students) {
            shuffle($class_students);
        }
        
        $seat_positions = ['left', 'center', 'right'];
        $current_room_index = 0;
        $current_bench = 1;
        $current_seat_index = 0;
        
        // Create a mixed array ensuring no adjacent same-class students
        $mixed_students = [];
        $class_keys = array_keys($students_by_class);
        $class_indices = array_fill_keys($class_keys, 0);
        
        while (array_sum($class_indices) < count($students)) {
            foreach ($class_keys as $class_id) {
                if ($class_indices[$class_id] < count($students_by_class[$class_id])) {
                    $mixed_students[] = $students_by_class[$class_id][$class_indices[$class_id]];
                    $class_indices[$class_id]++;
                    
                    if (count($mixed_students) >= count($students)) break;
                }
            }
        }
        
        // Assign seats
        foreach ($mixed_students as $student) {
            $room = trim($rooms[$current_room_index]);
            $position = $seat_positions[$current_seat_index];
            
            $stmt = $pdo->prepare("INSERT INTO exam_seating (student_id, exam_date, room, bench_number, seat_position) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$student['id'], $exam_date, $room, $current_bench, $position]);
            
            $current_seat_index++;
            if ($current_seat_index >= 3) {
                $current_seat_index = 0;
                $current_bench++;
                
                // Move to next room after 10 benches (30 students)
                if ($current_bench > 10) {
                    $current_bench = 1;
                    $current_room_index = ($current_room_index + 1) % count($rooms);
                }
            }
        }
        
        $message = '<div class="alert alert-success">Exam seating arrangement generated successfully!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}

// Get existing seating arrangements
$selected_date = isset($_GET['date']) ? $_GET['date'] : '';
$seating_data = [];

if ($selected_date) {
    $stmt = $pdo->prepare("
        SELECT es.*, s.roll_no, u.name as student_name, c.name as class_name 
        FROM exam_seating es 
        JOIN students s ON es.student_id = s.id 
        JOIN users u ON s.user_id = u.id 
        LEFT JOIN classes c ON s.class_id = c.id 
        WHERE es.exam_date = ? 
        ORDER BY es.room, es.bench_number, FIELD(es.seat_position, 'left', 'center', 'right')
    ");
    $stmt->execute([$selected_date]);
    $seating_raw = $stmt->fetchAll();
    
    // Organize by room and bench
    foreach ($seating_raw as $seat) {
        $seating_data[$seat['room']][$seat['bench_number']][$seat['seat_position']] = $seat;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Exam Seating - Academic Planner</title>
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
                <li><a href="generate_exam_seating.php" class="active">Exam Seating</a></li>
                <li><a href="manage_events.php">Events</a></li>
                <li><a href="manage_attendance.php">Attendance</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <h2>Generate Exam Seating Arrangement</h2>
            
            <?php echo $message; ?>
            
            <!-- Generate Seating Form -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3>Generate New Seating Arrangement</h3>
                <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="exam_date">Exam Date:</label>
                        <input type="date" id="exam_date" name="exam_date" class="form-control" required>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="rooms">Exam Rooms (comma-separated):</label>
                        <input type="text" id="rooms" name="rooms" class="form-control" 
                               placeholder="Room 101, Room 102, Room 103" required>
                        <small style="color: #666;">Enter room names separated by commas</small>
                    </div>
                    
                    <div style="grid-column: 1 / -1;">
                        <button type="submit" name="generate_seating" class="btn">Generate Seating Arrangement</button>
                    </div>
                </form>
            </div>
            
            <!-- View Seating Arrangement -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3>View Existing Seating Arrangement</h3>
                <form method="GET" style="display: flex; gap: 1rem; align-items: end; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="date">Select Date:</label>
                        <input type="date" id="date" name="date" class="form-control" value="<?php echo $selected_date; ?>" onchange="this.form.submit()">
                    </div>
                </form>
            </div>
            
            <?php if ($selected_date && !empty($seating_data)): ?>
                <h3>Seating Arrangement for <?php echo date('M d, Y', strtotime($selected_date)); ?></h3>
                
                <div class="seating-arrangement">
                    <?php foreach ($seating_data as $room => $benches): ?>
                        <div class="room">
                            <h3><?php echo htmlspecialchars($room); ?></h3>
                            <div class="benches">
                                <?php for ($bench_num = 1; $bench_num <= max(array_keys($benches)); $bench_num++): ?>
                                    <div class="bench">
                                        <div class="bench-header">Bench <?php echo $bench_num; ?></div>
                                        <div class="seats">
                                            <?php 
                                            $positions = ['left', 'center', 'right'];
                                            foreach ($positions as $pos): 
                                                if (isset($benches[$bench_num][$pos])):
                                                    $student = $benches[$bench_num][$pos];
                                            ?>
                                                <div class="seat">
                                                    <strong><?php echo htmlspecialchars($student['student_name']); ?></strong><br>
                                                    <small><?php echo htmlspecialchars($student['roll_no']); ?></small><br>
                                                    <small style="color: #666;"><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></small>
                                                </div>
                                            <?php else: ?>
                                                <div class="seat" style="background: #f0f0f0;">Empty</div>
                                            <?php endif; endforeach; ?>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Print Button -->
                <div style="margin-top: 2rem; text-align: center;">
                    <button onclick="window.print()" class="btn">Print Seating Arrangement</button>
                </div>
                
            <?php elseif ($selected_date): ?>
                <div class="alert alert-info">No seating arrangement found for this date. Generate one using the form above.</div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        @media print {
            .header, .nav, .btn, form { display: none !important; }
            .main-content { box-shadow: none; }
            .room { page-break-inside: avoid; margin-bottom: 2rem; }
        }
    </style>
</body>
</html>