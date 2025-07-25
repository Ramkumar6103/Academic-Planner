<?php
// backend/export_seating_pdf.php
// Display seating arrangements and allow export to PDF using jsPDF

require_once __DIR__ . '/config/db.php';
$pdo = require __DIR__ . '/config/db.php';

$exam_id = 1; // TODO: Make dynamic if needed

$stmt = $pdo->prepare('SELECT sa.seat_number, sa.exam_hall_id, sa.student_id, s.register_number, eh.name as hall_name
    FROM seating_arrangements sa
    JOIN students s ON sa.student_id = s.id
    JOIN exam_halls eh ON sa.exam_hall_id = eh.id
    WHERE sa.exam_id = ?
    ORDER BY eh.name, sa.seat_number');
$stmt->execute([$exam_id]);
$seating = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Seating Arrangements PDF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
<div class="container py-4">
    <h2>Seating Arrangements</h2>
    <button class="btn btn-primary mb-3" onclick="exportPDF()">Export to PDF</button>
    <table class="table table-bordered" id="seatingTable">
        <thead>
            <tr>
                <th>Hall Name</th>
                <th>Seat Number</th>
                <th>Register Number</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($seating as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['hall_name']) ?></td>
                <td><?= htmlspecialchars($row['seat_number']) ?></td>
                <td><?= htmlspecialchars($row['register_number']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
function exportPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.setFontSize(14);
    doc.text('Seating Arrangements', 14, 16);
    let startY = 26;
    const table = document.getElementById('seatingTable');
    const rows = table.querySelectorAll('tbody tr');
    doc.setFontSize(11);
    doc.text('Hall Name', 14, startY);
    doc.text('Seat Number', 70, startY);
    doc.text('Register Number', 130, startY);
    startY += 6;
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        doc.text(cells[0].innerText, 14, startY);
        doc.text(cells[1].innerText, 70, startY);
        doc.text(cells[2].innerText, 130, startY);
        startY += 6;
        if (startY > 280) {
            doc.addPage();
            startY = 16;
        }
    });
    doc.save('seating_arrangements.pdf');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 