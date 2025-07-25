<?php
// backend/generate_timetable.php
// Generates a conflict-free timetable by assigning subjects to faculty based on availability
header('Content-Type: application/json');

require_once __DIR__ . '/config/db.php';
$pdo = require __DIR__ . '/config/db.php';

// Fetch all subjects with their course and department
$subjects = $pdo->query('SELECT s.id as subject_id, s.name as subject_name, s.course_id, c.name as course_name, c.department_id, d.name as department_name FROM subjects s JOIN courses c ON s.course_id = c.id JOIN departments d ON c.department_id = d.id')->fetchAll();

// Fetch all faculty and their availability
$faculty = $pdo->query('SELECT f.id as faculty_id, f.name as faculty_name, f.department_id, fa.day_of_week, fa.start_time, fa.end_time FROM faculty f JOIN faculty_availability fa ON f.id = fa.faculty_id')->fetchAll();

// Build a map of faculty availability: [faculty_id][day_of_week][] = [start_time, end_time]
$faculty_avail_map = [];
foreach ($faculty as $f) {
    $faculty_avail_map[$f['faculty_id']][$f['day_of_week']][] = [
        'start_time' => $f['start_time'],
        'end_time' => $f['end_time'],
        'faculty_name' => $f['faculty_name'],
        'department_id' => $f['department_id']
    ];
}

// Timetable assignments: [day][start_time][faculty_id|subject_id] to check for conflicts
$timetable = [];
$assignments = [];

foreach ($subjects as $subject) {
    $assigned = false;
    // Try to assign a faculty from the same department
    foreach ($faculty_avail_map as $faculty_id => $days) {
        // Only consider faculty from the same department
        if ($faculty[array_search($faculty_id, array_column($faculty, 'faculty_id'))]['department_id'] != $subject['department_id']) continue;
        foreach ($days as $day => $slots) {
            foreach ($slots as $slot) {
                $start = $slot['start_time'];
                $end = $slot['end_time'];
                // Check for conflicts: no subject or faculty overlap at this time
                $conflict = false;
                foreach ($assignments as $a) {
                    if ($a['day_of_week'] === $day && (
                        ($a['faculty_id'] == $faculty_id) ||
                        ($a['subject_id'] == $subject['subject_id'])
                    )) {
                        // Check time overlap
                        if (!(($end <= $a['start_time']) || ($start >= $a['end_time']))) {
                            $conflict = true;
                            break;
                        }
                    }
                }
                if (!$conflict) {
                    // Assign
                    $assignments[] = [
                        'department' => $subject['department_name'],
                        'course' => $subject['course_name'],
                        'subject' => $subject['subject_name'],
                        'subject_id' => $subject['subject_id'],
                        'faculty' => $slot['faculty_name'],
                        'faculty_id' => $faculty_id,
                        'day_of_week' => $day,
                        'start_time' => $start,
                        'end_time' => $end
                    ];
                    $assigned = true;
                    break 3;
                }
            }
        }
    }
    if (!$assigned) {
        $assignments[] = [
            'department' => $subject['department_name'],
            'course' => $subject['course_name'],
            'subject' => $subject['subject_name'],
            'subject_id' => $subject['subject_id'],
            'faculty' => null,
            'faculty_id' => null,
            'day_of_week' => null,
            'start_time' => null,
            'end_time' => null,
            'error' => 'No available faculty slot found.'
        ];
    }
}

echo json_encode($assignments); 