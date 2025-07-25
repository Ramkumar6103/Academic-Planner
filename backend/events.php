<?php
// backend/events.php
header('Content-Type: application/json');
require_once __DIR__ . '/config/db.php';
$pdo = require __DIR__ . '/config/db.php';

// Fetch all events
$stmt = $pdo->query('SELECT id, title, start, end, type FROM events');
$rows = $stmt->fetchAll();

$events = [];
foreach ($rows as $row) {
    $color = '#1976d2'; // Blue for classes by default
    if ($row['type'] === 'exam') {
        $color = '#e53935'; // Red
    } elseif ($row['type'] === 'assignment') {
        $color = '#43a047'; // Green
    }
    $events[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'start' => $row['start'],
        'end' => $row['end'],
        'color' => $color
    ];
}
echo json_encode($events); 