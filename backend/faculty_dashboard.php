<?php
// backend/faculty_dashboard.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Faculty') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/db.php';
$pdo = require __DIR__ . '/config/db.php';

$user_id = $_SESSION['user_id'];
// Get faculty id
$stmt = $pdo->prepare('SELECT id FROM faculty WHERE user_id = ?');
$stmt->execute([$user_id]);
$faculty = $stmt->fetch();
if (!$faculty) {
    echo 'Faculty record not found.';
    exit;
}
$faculty_id = $faculty['id'];

// Fetch personal timetable
$timetable = $pdo->prepare('SELECT t.*, s.name as subject_name, c.name as course_name, d.name as department_name FROM timetable t JOIN subjects s ON t.subject_id = s.id JOIN courses c ON t.course_id = c.id JOIN departments d ON t.department_id = d.id WHERE t.faculty_id = ? ORDER BY FIELD(t.day_of_week, "Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"), t.start_time');
$timetable->execute([$faculty_id]);
$timetable_rows = $timetable->fetchAll();

// Handle assignment upload
$assignment_msg = '';
if (isset($_POST['upload_assignment'])) {
    $subject_id = intval($_POST['subject_id']);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?? '';
    $file_path = null;
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/assignments/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = uniqid('assgn_') . '_' . basename($_FILES['assignment_file']['name']);
        $target = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target)) {
            $file_path = 'uploads/assignments/' . $filename;
        }
    }
    if ($subject_id && $title && $due_date) {
        $stmt = $pdo->prepare('INSERT INTO assignments (subject_id, faculty_id, title, description, due_date, file_path) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$subject_id, $faculty_id, $title, $description, $due_date, $file_path]);
        $assignment_msg = 'Assignment uploaded.';
    } else {
        $assignment_msg = 'Please fill all required fields.';
    }
}

// Fetch subjects taught by this faculty
$subjects = $pdo->prepare('SELECT DISTINCT s.id, s.name FROM timetable t JOIN subjects s ON t.subject_id = s.id WHERE t.faculty_id = ?');
$subjects->execute([$faculty_id]);
$subjects_list = $subjects->fetchAll();

// Handle attendance marking
$attendance_msg = '';
if (isset($_POST['mark_attendance'])) {
    $subject_id = intval($_POST['attend_subject_id']);
    $date = $_POST['attend_date'] ?? date('Y-m-d');
    $statuses = $_POST['attendance_status'] ?? [];
    foreach ($statuses as $student_id => $status) {
        $stmt = $pdo->prepare('INSERT INTO attendance (student_id, subject_id, date, status) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)');
        $stmt->execute([$student_id, $subject_id, $date, $status]);
    }
    $attendance_msg = 'Attendance marked.';
}

// Fetch students for attendance (for the first subject by default)
$students = [];
if (!empty($subjects_list)) {
    $first_subject_id = $subjects_list[0]['id'];
    $students_stmt = $pdo->prepare('SELECT s.id, s.name, s.register_number FROM students s JOIN timetable t ON t.department_id = s.department_id WHERE t.subject_id = ? AND t.faculty_id = ? GROUP BY s.id');
    $students_stmt->execute([$first_subject_id, $faculty_id]);
    $students = $students_stmt->fetchAll();
}

// Fetch invigilation schedule (exams assigned to this faculty)
$invigilation = $pdo->prepare('SELECT e.*, sub.name as subject_name, eh.name as hall_name FROM exams e JOIN subjects sub ON e.subject_id = sub.id JOIN exam_halls eh ON e.exam_hall_id = eh.id WHERE e.id IN (SELECT DISTINCT exam_id FROM seating_arrangements WHERE exam_hall_id IN (SELECT exam_hall_id FROM seating_arrangements WHERE student_id IN (SELECT id FROM students)))');
$invigilation->execute();
$invigilation_rows = $invigilation->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h1 class="mb-4">Faculty Dashboard</h1>
    <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="timetable-tab" data-bs-toggle="tab" data-bs-target="#timetable" type="button" role="tab">Personal Timetable</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="assignment-tab" data-bs-toggle="tab" data-bs-target="#assignment" type="button" role="tab">Upload Assignments</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab">Mark Attendance</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="invigilation-tab" data-bs-toggle="tab" data-bs-target="#invigilation" type="button" role="tab">Invigilation Schedule</button>
        </li>
    </ul>
    <div class="tab-content">
        <!-- Timetable Tab -->
        <div class="tab-pane fade show active" id="timetable" role="tabpanel">
            <h4>Personal Timetable</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Subject</th>
                        <th>Course</th>
                        <th>Department</th>
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
                        <td><?= htmlspecialchars($row['department_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Assignment Tab -->
        <div class="tab-pane fade" id="assignment" role="tabpanel">
            <h4>Upload Assignment</h4>
            <?php if ($assignment_msg): ?>
                <div class="alert alert-info"> <?= htmlspecialchars($assignment_msg) ?> </div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data" class="mb-3">
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <label for="subject_id" class="form-label">Subject</label>
                        <select name="subject_id" id="subject_id" class="form-select" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects_list as $sub): ?>
                                <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control" required>
                    </div>
                </div>
                <div class="mb-2">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control"></textarea>
                </div>
                <div class="mb-2">
                    <label for="assignment_file" class="form-label">File (optional)</label>
                    <input type="file" name="assignment_file" id="assignment_file" class="form-control">
                </div>
                <button type="submit" name="upload_assignment" class="btn btn-primary">Upload</button>
            </form>
        </div>
        <!-- Attendance Tab -->
        <div class="tab-pane fade" id="attendance" role="tabpanel">
            <h4>Mark Attendance</h4>
            <?php if ($attendance_msg): ?>
                <div class="alert alert-info"> <?= htmlspecialchars($attendance_msg) ?> </div>
            <?php endif; ?>
            <form method="post" class="mb-3">
                <input type="hidden" name="attend_subject_id" value="<?= !empty($subjects_list) ? $subjects_list[0]['id'] : '' ?>">
                <input type="date" name="attend_date" class="form-control mb-2" value="<?= date('Y-m-d') ?>" required>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Register Number</th>
                            <th>Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($students as $stu): ?>
                        <tr>
                            <td><?= htmlspecialchars($stu['register_number']) ?></td>
                            <td><?= htmlspecialchars($stu['name']) ?></td>
                            <td>
                                <select name="attendance_status[<?= $stu['id'] ?>]" class="form-select">
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="late">Late</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="mark_attendance" class="btn btn-primary">Mark Attendance</button>
            </form>
        </div>
        <!-- Invigilation Tab -->
        <div class="tab-pane fade" id="invigilation" role="tabpanel">
            <h4>Invigilation Schedule</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Exam</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Hall</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($invigilation_rows as $inv): ?>
                    <tr>
                        <td><?= htmlspecialchars($inv['subject_name']) ?></td>
                        <td><?= htmlspecialchars($inv['date']) ?></td>
                        <td><?= htmlspecialchars($inv['start_time']) ?></td>
                        <td><?= htmlspecialchars($inv['end_time']) ?></td>
                        <td><?= htmlspecialchars($inv['hall_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 