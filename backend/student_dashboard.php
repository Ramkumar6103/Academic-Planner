<?php
// backend/student_dashboard.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/db.php';
$pdo = require __DIR__ . '/config/db.php';

$user_id = $_SESSION['user_id'];
// Get student id and department
$stmt = $pdo->prepare('SELECT id, department_id, register_number, name FROM students WHERE user_id = ?');
$stmt->execute([$user_id]);
$student = $stmt->fetch();
if (!$student) {
    echo 'Student record not found.';
    exit;
}
$student_id = $student['id'];
$department_id = $student['department_id'];

// Fetch timetable
$timetable = $pdo->prepare('SELECT t.*, s.name as subject_name, c.name as course_name FROM timetable t JOIN subjects s ON t.subject_id = s.id JOIN courses c ON t.course_id = c.id WHERE t.department_id = ? ORDER BY FIELD(t.day_of_week, "Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"), t.start_time');
$timetable->execute([$department_id]);
$timetable_rows = $timetable->fetchAll();

// Fetch upcoming exams
$exams = $pdo->prepare('SELECT e.*, sub.name as subject_name, eh.name as hall_name FROM exams e JOIN subjects sub ON e.subject_id = sub.id JOIN exam_halls eh ON e.exam_hall_id = eh.id WHERE sub.id IN (SELECT subject_id FROM timetable WHERE department_id = ?) AND e.date >= CURDATE() ORDER BY e.date, e.start_time');
$exams->execute([$department_id]);
$exam_rows = $exams->fetchAll();

// Fetch notifications
$notifications = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
$notifications->execute([$user_id]);
$notification_rows = $notifications->fetchAll();

// Fetch seating plan for upcoming exam (if any)
$seating = $pdo->prepare('SELECT sa.*, eh.name as hall_name FROM seating_arrangements sa JOIN exam_halls eh ON sa.exam_hall_id = eh.id WHERE sa.student_id = ? ORDER BY sa.id DESC LIMIT 1');
$seating->execute([$student_id]);
$seating_row = $seating->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Student Dashboard</h1>
        <div>
            <span class="me-3">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="logout.php" class="btn btn-outline-danger">
                <i class="fa-solid fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
    </div>
    <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="timetable-tab" data-bs-toggle="tab" data-bs-target="#timetable" type="button" role="tab">Timetable</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="exams-tab" data-bs-toggle="tab" data-bs-target="#exams" type="button" role="tab">Upcoming Exams</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">Notifications</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="seating-tab" data-bs-toggle="tab" data-bs-target="#seating" type="button" role="tab">Seating Plan</button>
        </li>
    </ul>
    <div class="tab-content">
        <!-- Timetable Tab -->
        <div class="tab-pane fade show active" id="timetable" role="tabpanel">
            <h4>Your Timetable</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Subject</th>
                        <th>Course</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($timetable_rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['day_of_week']) ?></td>
                        <td><?= htmlspecialchars($row['start_time']) ?></td>
                        <td><?= htmlspecialchars($row['end_time']) ?></td>
                        <td><?= htmlspecialchars($row['subject_name']) ?></td>
                        <td><?= htmlspecialchars($row['course_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Exams Tab -->
        <div class="tab-pane fade" id="exams" role="tabpanel">
            <h4>Upcoming Exams</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Hall</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($exam_rows as $exam): ?>
                    <tr>
                        <td><?= htmlspecialchars($exam['subject_name']) ?></td>
                        <td><?= htmlspecialchars($exam['date']) ?></td>
                        <td><?= htmlspecialchars($exam['start_time']) ?></td>
                        <td><?= htmlspecialchars($exam['end_time']) ?></td>
                        <td><?= htmlspecialchars($exam['hall_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Notifications Tab -->
        <div class="tab-pane fade" id="notifications" role="tabpanel">
            <h4>Notifications</h4>
            <ul class="list-group">
                <?php foreach ($notification_rows as $note): ?>
                    <li class="list-group-item">
                        <strong><?= htmlspecialchars(ucfirst($note['type'])) ?>:</strong> <?= htmlspecialchars($note['message']) ?>
                        <span class="text-muted float-end small"><?= htmlspecialchars($note['created_at']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <!-- Seating Plan Tab -->
        <div class="tab-pane fade" id="seating" role="tabpanel">
            <h4>Your Seating Plan</h4>
            <?php if ($seating_row): ?>
                <table class="table table-bordered w-50">
                    <tr><th>Hall</th><td><?= htmlspecialchars($seating_row['hall_name']) ?></td></tr>
                    <tr><th>Seat Number</th><td><?= htmlspecialchars($seating_row['seat_number']) ?></td></tr>
                </table>
                <a href="export_seating_pdf.php" class="btn btn-primary">Download PDF</a>
            <?php else: ?>
                <div class="alert alert-info">No seating plan available yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 