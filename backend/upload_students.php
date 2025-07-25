<?php
// backend/upload_students.php
session_start();
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Faculty')) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/db.php';
$pdo = require __DIR__ . '/config/db.php';

$message = '';
$department_id = 1; // TODO: Replace with dynamic department selection if needed

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    if (($handle = fopen($file, 'r')) !== false) {
        $row = 0;
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            // Expecting: Register Number, Name
            if ($row === 0 && (stripos($data[0], 'register') !== false || stripos($data[1], 'name') !== false)) {
                $row++;
                continue; // Skip header
            }
            $register_number = trim($data[0]);
            $name = trim($data[1]);
            if ($register_number && $name) {
                // Check if student already exists
                $stmt = $pdo->prepare('SELECT id FROM students WHERE register_number = ?');
                $stmt->execute([$register_number]);
                if (!$stmt->fetch()) {
                    // Insert into students (user_id is NULL for now)
                    $stmt = $pdo->prepare('INSERT INTO students (user_id, department_id, register_number, name) VALUES (NULL, ?, ?, ?)');
                    $stmt->execute([$department_id, $register_number, $name]);
                }
            }
            $row++;
        }
        fclose($handle);
        $message = 'Students uploaded successfully.';
    } else {
        $message = 'Failed to open the uploaded file.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Students CSV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h2>Upload Students CSV</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"> <?= htmlspecialchars($message) ?> </div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="mb-3">
        <div class="mb-3">
            <label for="csv_file" class="form-label">CSV File (Register Number, Name)</label>
            <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 