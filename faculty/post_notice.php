<?php
require_once '../includes/auth.php';
requireRole('faculty');

$message = '';

// Get faculty info
$stmt = $pdo->prepare("SELECT * FROM faculty WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$faculty_info = $stmt->fetch();

// Handle notice posting (using events table for notices)
if ($_POST && isset($_POST['post_notice'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $notice_date = $_POST['notice_date'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, $notice_date]);
        $message = '<div class="alert alert-success">Notice posted successfully!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}

// Get recent notices posted by this faculty (last 10)
$stmt = $pdo->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 10");
$recent_notices = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Notice - Academic Planner</title>
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
                <li><a href="upload_assignment.php">Assignments</a></li>
                <li><a href="post_notice.php" class="active">Post Notice</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <h2>Post Notice</h2>
            
            <?php echo $message; ?>
            
            <!-- Post Notice Form -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3>Create New Notice</h3>
                <form method="POST" style="display: grid; gap: 1rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="title">Notice Title:</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="notice_date">Notice Date:</label>
                        <input type="date" id="notice_date" name="notice_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Notice Content:</label>
                        <textarea id="description" name="description" class="form-control" rows="5" required placeholder="Enter the notice content here..."></textarea>
                    </div>
                    
                    <div>
                        <button type="submit" name="post_notice" class="btn">Post Notice</button>
                    </div>
                </form>
            </div>
            
            <!-- Recent Notices -->
            <h3>Recent Notices</h3>
            <?php if (!empty($recent_notices)): ?>
                <div style="display: grid; gap: 1rem;">
                    <?php foreach ($recent_notices as $notice): ?>
                        <div style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem;">
                            <div style="display: flex; justify-content: between; align-items: start; margin-bottom: 1rem;">
                                <h4 style="margin: 0; color: #333;"><?php echo htmlspecialchars($notice['title']); ?></h4>
                                <small style="color: #666; margin-left: auto;">
                                    <?php echo date('M d, Y', strtotime($notice['event_date'])); ?>
                                </small>
                            </div>
                            
                            <?php if ($notice['description']): ?>
                                <p style="color: #555; line-height: 1.6; margin: 0;">
                                    <?php echo nl2br(htmlspecialchars($notice['description'])); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
                                <small style="color: #999;">
                                    Posted on <?php echo date('M d, Y \a\t g:i A', strtotime($notice['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No notices posted yet. Use the form above to post your first notice.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>