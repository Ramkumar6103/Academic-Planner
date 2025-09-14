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

// Get exam seating arrangements for this student
$exam_seating = [];
if ($student_info) {
    $stmt = $pdo->prepare("
        SELECT es.*, c.name as class_name 
        FROM exam_seating es 
        LEFT JOIN students s ON es.student_id = s.id 
        LEFT JOIN classes c ON s.class_id = c.id 
        WHERE es.student_id = ? 
        ORDER BY es.exam_date DESC
    ");
    $stmt->execute([$student_info['id']]);
    $exam_seating = $stmt->fetchAll();
}

// Get all exam dates for viewing seating arrangements
$stmt = $pdo->query("SELECT DISTINCT exam_date FROM exam_seating ORDER BY exam_date DESC");
$exam_dates = $stmt->fetchAll();

// Get seating arrangement for selected date
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
    <title>Exam Schedule - Academic Planner</title>
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
                <li><a href="view_exam_schedule.php" class="active">Exam Schedule</a></li>
                <li><a href="view_calendar.php">Calendar</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <h2>Exam Schedule & Seating</h2>
            
            <?php if ($student_info): ?>
                <p style="color: #666; margin-bottom: 2rem;">
                    <strong>Class:</strong> <?php echo htmlspecialchars($student_info['class_name'] ?? 'Not assigned'); ?> | 
                    <strong>Roll No:</strong> <?php echo htmlspecialchars($student_info['roll_no']); ?>
                </p>
            <?php endif; ?>
            
            <!-- My Exam Seating -->
            <?php if (!empty($exam_seating)): ?>
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                    <h3>My Exam Seating Arrangements</h3>
                    <div style="overflow-x: auto; margin-top: 1rem;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Exam Date</th>
                                    <th>Room</th>
                                    <th>Bench Number</th>
                                    <th>Seat Position</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($exam_seating as $seating): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($seating['exam_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($seating['room']); ?></td>
                                        <td><?php echo $seating['bench_number']; ?></td>
                                        <td style="text-transform: capitalize;"><?php echo htmlspecialchars($seating['seat_position']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- View All Seating Arrangements -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3>View Complete Seating Arrangement</h3>
                <form method="GET" style="display: flex; gap: 1rem; align-items: end; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="date">Select Exam Date:</label>
                        <select id="date" name="date" class="form-control" onchange="this.form.submit()">
                            <option value="">Select Date</option>
                            <?php foreach ($exam_dates as $date): ?>
                                <option value="<?php echo $date['exam_date']; ?>" <?php echo $selected_date == $date['exam_date'] ? 'selected' : ''; ?>>
                                    <?php echo date('M d, Y', strtotime($date['exam_date'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                                                    $is_current_student = ($student_info && $student['student_name'] == $_SESSION['user_name']);
                                            ?>
                                                <div class="seat" style="<?php echo $is_current_student ? 'background: #ffeb3b; border: 2px solid #ff9800;' : ''; ?>">
                                                    <strong><?php echo htmlspecialchars($student['student_name']); ?></strong><br>
                                                    <small><?php echo htmlspecialchars($student['roll_no']); ?></small><br>
                                                    <small style="color: #666;"><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></small>
                                                    <?php if ($is_current_student): ?>
                                                        <br><small style="color: #ff9800; font-weight: bold;">YOU</small>
                                                    <?php endif; ?>
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
                <div class="alert alert-info">No seating arrangement found for this date.</div>
            <?php elseif (empty($exam_dates)): ?>
                <div class="alert alert-info">No exam schedules available yet.</div>
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