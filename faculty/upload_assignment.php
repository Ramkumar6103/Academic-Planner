<?php
require_once '../includes/auth.php';
requireRole('faculty');

$message = '';

// Get faculty info
$stmt = $pdo->prepare("SELECT * FROM faculty WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$faculty_info = $stmt->fetch();

// Handle assignment upload
if ($_POST && isset($_POST['upload_assignment'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $subject_id = (int)$_POST['subject_id'];
    $due_date = $_POST['due_date'];
    $file_path = '';
    
    // Handle file upload
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
        $upload_dir = '../assets/assignments/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['assignment_file']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $upload_path)) {
            $file_path = 'assets/assignments/' . $new_filename;
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO assignments (title, description, subject_id, faculty_id, file_path, due_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $subject_id, $faculty_info['id'], $file_path, $due_date]);
        $message = '<div class="alert alert-success">Assignment uploaded successfully!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}

// Handle assignment deletion
if ($_POST && isset($_POST['delete_assignment'])) {
    $assignment_id = (int)$_POST['assignment_id'];
    
    try {
        // Get file path to delete file
        $stmt = $pdo->prepare("SELECT file_path FROM assignments WHERE id = ? AND faculty_id = ?");
        $stmt->execute([$assignment_id, $faculty_info['id']]);
        $file_path = $stmt->fetchColumn();
        
        // Delete assignment
        $stmt = $pdo->prepare("DELETE FROM assignments WHERE id = ? AND faculty_id = ?");
        $stmt->execute([$assignment_id, $faculty_info['id']]);
        
        // Delete file if exists
        if ($file_path && file_exists('../' . $file_path)) {
            unlink('../' . $file_path);
        }
        
        $message = '<div class="alert alert-success">Assignment deleted successfully!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}

// Get subjects
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();

// Get faculty's assignments
$stmt = $pdo->prepare("
    SELECT a.*, s.name as subject_name 
    FROM assignments a 
    JOIN subjects s ON a.subject_id = s.id 
    WHERE a.faculty_id = ? 
    ORDER BY a.created_at DESC
");
$stmt->execute([$faculty_info['id']]);
$assignments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Assignment - Academic Planner</title>
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
                <li><a href="view_timetable.php">My Timetable</a></li>
                <li><a href="upload_assignment.php" class="active">Assignments</a></li>
                <li><a href="post_notice.php">Post Notice</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <h2>Manage Assignments</h2>
            
            <?php echo $message; ?>
            
            <!-- Upload Assignment Form -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3>Upload New Assignment</h3>
                <form method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="title">Assignment Title:</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject_id">Subject:</label>
                        <select id="subject_id" name="subject_id" class="form-control" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="due_date">Due Date:</label>
                        <input type="date" id="due_date" name="due_date" class="form-control" required>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="assignment_file">Assignment File:</label>
                        <input type="file" id="assignment_file" name="assignment_file" class="form-control" accept=".pdf,.doc,.docx,.txt">
                        <small style="color: #666;">Supported formats: PDF, DOC, DOCX, TXT</small>
                    </div>
                    
                    <div style="grid-column: 1 / -1;">
                        <button type="submit" name="upload_assignment" class="btn">Upload Assignment</button>
                    </div>
                </form>
            </div>
            
            <!-- Assignments List -->
            <h3>My Assignments</h3>
            <?php if (!empty($assignments)): ?>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Subject</th>
                                <th>Due Date</th>
                                <th>File</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $assignment): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($assignment['title']); ?></strong>
                                        <?php if ($assignment['description']): ?>
                                            <br><small style="color: #666;"><?php echo htmlspecialchars(substr($assignment['description'], 0, 100)); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($assignment['subject_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></td>
                                    <td>
                                        <?php if ($assignment['file_path']): ?>
                                            <a href="../<?php echo htmlspecialchars($assignment['file_path']); ?>" target="_blank" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Download</a>
                                        <?php else: ?>
                                            <span style="color: #999;">No file</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($assignment['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this assignment?')">
                                            <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                                            <button type="submit" name="delete_assignment" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No assignments uploaded yet. Use the form above to upload your first assignment.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>