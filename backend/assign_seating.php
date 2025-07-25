<?php
// backend/assign_seating.php
// Randomly assign students to seats in available halls, avoiding adjacent register numbers

require_once __DIR__ . '/config/db.php';
$pdo = require __DIR__ . '/config/db.php';

// Fetch all students (for a given exam or department, here all students)
$students = $pdo->query('SELECT id, register_number, name FROM students ORDER BY register_number')->fetchAll();

// Fetch all exam halls (ordered by capacity desc)
$halls = $pdo->query('SELECT id, name, capacity FROM exam_halls ORDER BY capacity DESC')->fetchAll();

if (!$students || !$halls) {
    echo 'No students or exam halls found.';
    exit;
}

// Shuffle students, then check for adjacent register numbers
function is_adjacent_conflict($arr) {
    for ($i = 1; $i < count($arr); $i++) {
        if (abs((int)$arr[$i]['register_number'] - (int)$arr[$i-1]['register_number']) == 1) {
            return true;
        }
    }
    return false;
}

$max_attempts = 1000;
$attempt = 0;
do {
    shuffle($students);
    $attempt++;
} while (is_adjacent_conflict($students) && $attempt < $max_attempts);

if ($attempt === $max_attempts) {
    echo 'Could not generate a seating plan without adjacent register numbers after many attempts.';
    exit;
}

// Assign seats
$assignments = [];
$student_idx = 0;
foreach ($halls as $hall) {
    for ($seat = 1; $seat <= $hall['capacity'] && $student_idx < count($students); $seat++) {
        $student = $students[$student_idx];
        $assignments[] = [
            'student_id' => $student['id'],
            'register_number' => $student['register_number'],
            'student_name' => $student['name'],
            'exam_hall_id' => $hall['id'],
            'exam_hall_name' => $hall['name'],
            'seat_number' => $seat
        ];
        $student_idx++;
    }
}

// Save to seating_arrangements table (for a given exam_id, here use exam_id = 1 as example)
$exam_id = 1; // TODO: Replace with actual exam_id
foreach ($assignments as $a) {
    $stmt = $pdo->prepare('INSERT INTO seating_arrangements (exam_id, student_id, exam_hall_id, seat_number) VALUES (?, ?, ?, ?)');
    $stmt->execute([$exam_id, $a['student_id'], $a['exam_hall_id'], $a['seat_number']]);
}

// Output summary as HTML table
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seating Assignment Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h2>Seating Assignment Result</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Register Number</th>
                <th>Name</th>
                <th>Exam Hall</th>
                <th>Seat Number</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($assignments as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['register_number']) ?></td>
                <td><?= htmlspecialchars($a['student_name']) ?></td>
                <td><?= htmlspecialchars($a['exam_hall_name']) ?></td>
                <td><?= htmlspecialchars($a['seat_number']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 