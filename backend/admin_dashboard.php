<?php
// backend/admin_dashboard.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/db.php';
$pdo = require __DIR__ . '/config/db.php';

// Handle form submissions (add/edit/delete)
$messages = [];

// Add Department
if (isset($_POST['add_department'])) {
    $dept_name = trim($_POST['department_name'] ?? '');
    if ($dept_name) {
        $stmt = $pdo->prepare('INSERT INTO departments (name) VALUES (?)');
        try {
            $stmt->execute([$dept_name]);
            $messages[] = ['type' => 'success', 'text' => 'Department added.'];
        } catch (PDOException $e) {
            $messages[] = ['type' => 'danger', 'text' => 'Error: ' . $e->getMessage()];
        }
    }
}
// Delete Department
if (isset($_POST['delete_department'])) {
    $dept_id = intval($_POST['department_id']);
    $stmt = $pdo->prepare('DELETE FROM departments WHERE id = ?');
    $stmt->execute([$dept_id]);
    $messages[] = ['type' => 'success', 'text' => 'Department deleted.'];
}

// Add Subject under a Course
if (isset($_POST['add_subject'])) {
    $course_id = intval($_POST['course_id']);
    $subject_name = trim($_POST['subject_name'] ?? '');
    $subject_code = trim($_POST['subject_code'] ?? '');
    if ($course_id && $subject_name && $subject_code) {
        $stmt = $pdo->prepare('INSERT INTO subjects (course_id, name, code) VALUES (?, ?, ?)');
        try {
            $stmt->execute([$course_id, $subject_name, $subject_code]);
            $messages[] = ['type' => 'success', 'text' => 'Subject added.'];
        } catch (PDOException $e) {
            $messages[] = ['type' => 'danger', 'text' => 'Error: ' . $e->getMessage()];
        }
    }
}

// Add Exam Hall
if (isset($_POST['add_exam_hall'])) {
    $hall_name = trim($_POST['hall_name'] ?? '');
    $capacity = intval($_POST['capacity'] ?? 0);
    if ($hall_name && $capacity > 0) {
        $stmt = $pdo->prepare('INSERT INTO exam_halls (name, capacity) VALUES (?, ?)');
        try {
            $stmt->execute([$hall_name, $capacity]);
            $messages[] = ['type' => 'success', 'text' => 'Exam hall added.'];
        } catch (PDOException $e) {
            $messages[] = ['type' => 'danger', 'text' => 'Error: ' . $e->getMessage()];
        }
    }
}

// Assign Faculty Availability
if (isset($_POST['assign_faculty_availability'])) {
    $faculty_id = intval($_POST['faculty_id']);
    $day_of_week = trim($_POST['day_of_week'] ?? '');
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    if ($faculty_id && $day_of_week && $start_time && $end_time) {
        $stmt = $pdo->prepare('INSERT INTO faculty_availability (faculty_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)');
        try {
            $stmt->execute([$faculty_id, $day_of_week, $start_time, $end_time]);
            $messages[] = ['type' => 'success', 'text' => 'Faculty availability assigned.'];
        } catch (PDOException $e) {
            $messages[] = ['type' => 'danger', 'text' => 'Error: ' . $e->getMessage()];
        }
    }
}

// Fetch data for display
$departments = $pdo->query('SELECT * FROM departments ORDER BY name')->fetchAll();
$courses = $pdo->query('SELECT * FROM courses ORDER BY name')->fetchAll();
$subjects = $pdo->query('SELECT s.*, c.name as course_name FROM subjects s JOIN courses c ON s.course_id = c.id ORDER BY s.name')->fetchAll();
$exam_halls = $pdo->query('SELECT * FROM exam_halls ORDER BY name')->fetchAll();
$faculty = $pdo->query('SELECT f.id, f.name, d.name as department FROM faculty f JOIN departments d ON f.department_id = d.id ORDER BY f.name')->fetchAll();
$faculty_availability = $pdo->query('SELECT fa.*, f.name as faculty_name FROM faculty_availability fa JOIN faculty f ON fa.faculty_id = f.id ORDER BY fa.day_of_week, fa.start_time')->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Academic Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h1 class="mb-4">Admin Dashboard</h1>
    <?php foreach ($messages as $msg): ?>
        <div class="alert alert-<?= $msg['type'] ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($msg['text']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>
    <div class="row g-4">
        <!-- Departments -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">Departments</div>
                <div class="card-body">
                    <form method="post" class="row g-2 mb-3">
                        <div class="col-8">
                            <input type="text" name="department_name" class="form-control" placeholder="Add Department" required>
                        </div>
                        <div class="col-4">
                            <button type="submit" name="add_department" class="btn btn-success w-100">Add</button>
                        </div>
                    </form>
                    <ul class="list-group">
                        <?php foreach ($departments as $dept): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($dept['name']) ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="department_id" value="<?= $dept['id'] ?>">
                                    <button type="submit" name="delete_department" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Exam Halls -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">Exam Halls</div>
                <div class="card-body">
                    <form method="post" class="row g-2 mb-3">
                        <div class="col-6">
                            <input type="text" name="hall_name" class="form-control" placeholder="Hall Name" required>
                        </div>
                        <div class="col-4">
                            <input type="number" name="capacity" class="form-control" placeholder="Capacity" min="1" required>
                        </div>
                        <div class="col-2">
                            <button type="submit" name="add_exam_hall" class="btn btn-success w-100">Add</button>
                        </div>
                    </form>
                    <ul class="list-group">
                        <?php foreach ($exam_halls as $hall): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($hall['name']) ?> <span class="badge bg-secondary">Cap: <?= $hall['capacity'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Subjects under Course -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">Add Subject under Course</div>
                <div class="card-body">
                    <form method="post" class="row g-2 mb-3">
                        <div class="col-4">
                            <select name="course_id" class="form-select" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-4">
                            <input type="text" name="subject_name" class="form-control" placeholder="Subject Name" required>
                        </div>
                        <div class="col-3">
                            <input type="text" name="subject_code" class="form-control" placeholder="Code" required>
                        </div>
                        <div class="col-1">
                            <button type="submit" name="add_subject" class="btn btn-success w-100">Add</button>
                        </div>
                    </form>
                    <ul class="list-group">
                        <?php foreach ($subjects as $subject): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($subject['name']) ?> <span class="badge bg-info">Course: <?= htmlspecialchars($subject['course_name']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Faculty Availability -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">Assign Faculty Availability</div>
                <div class="card-body">
                    <form method="post" class="row g-2 mb-3">
                        <div class="col-4">
                            <select name="faculty_id" class="form-select" required>
                                <option value="">Select Faculty</option>
                                <?php foreach ($faculty as $f): ?>
                                    <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?> (<?= htmlspecialchars($f['department']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-3">
                            <select name="day_of_week" class="form-select" required>
                                <option value="">Day</option>
                                <option>Monday</option>
                                <option>Tuesday</option>
                                <option>Wednesday</option>
                                <option>Thursday</option>
                                <option>Friday</option>
                                <option>Saturday</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-2">
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                        <div class="col-1">
                            <button type="submit" name="assign_faculty_availability" class="btn btn-success w-100">Add</button>
                        </div>
                    </form>
                    <ul class="list-group">
                        <?php foreach ($faculty_availability as $fa): ?>
                            <li class="list-group-item">
                                <?= htmlspecialchars($fa['faculty_name']) ?> - <?= htmlspecialchars($fa['day_of_week']) ?> (<?= htmlspecialchars($fa['start_time']) ?> - <?= htmlspecialchars($fa['end_time']) ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 