<?php
require_once '../includes/auth.php';
requireRole('admin');

$message = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_event'])) {
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $event_date = $_POST['event_date'];
        $image_path = '';
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_dir = '../assets/images/events/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = 'assets/images/events/' . $new_filename;
            }
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, image_path) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $description, $event_date, $image_path]);
            $message = '<div class="alert alert-success">Event added successfully!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
    
    if (isset($_POST['delete_event'])) {
        $event_id = (int)$_POST['event_id'];
        
        try {
            // Get image path to delete file
            $stmt = $pdo->prepare("SELECT image_path FROM events WHERE id = ?");
            $stmt->execute([$event_id]);
            $image_path = $stmt->fetchColumn();
            
            // Delete event
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$event_id]);
            
            // Delete image file if exists
            if ($image_path && file_exists('../' . $image_path)) {
                unlink('../' . $image_path);
            }
            
            $message = '<div class="alert alert-success">Event deleted successfully!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Get all events
$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Academic Planner</title>
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
                <li><a href="manage_students.php">Students</a></li>
                <li><a href="manage_faculty.php">Faculty</a></li>
                <li><a href="generate_timetable.php">Timetable</a></li>
                <li><a href="generate_exam_seating.php">Exam Seating</a></li>
                <li><a href="manage_events.php" class="active">Events</a></li>
                <!-- <li><a href="manage_attendance.php">Attendance</a></li> -->
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <h2>Manage Events</h2>
            
            <?php echo $message; ?>
            
            <!-- Add Event Form -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3>Add New Event</h3>
                <form method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label for="title">Event Title:</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_date">Event Date:</label>
                        <input type="date" id="event_date" name="event_date" class="form-control" required>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="image">Event Image:</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                    </div>
                    
                    <div style="grid-column: 1 / -1;">
                        <button type="submit" name="add_event" class="btn">Add Event</button>
                    </div>
                </form>
            </div>
            
            <!-- Events List -->
            <h3>All Events</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <?php foreach ($events as $event): ?>
                    <div class="card">
                        <?php if ($event['image_path']): ?>
                            <img src="../<?php echo htmlspecialchars($event['image_path']); ?>" 
                                 alt="Event Image" 
                                 style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 1rem;">
                        <?php endif; ?>
                        
                        <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                        <p style="color: #666; margin-bottom: 0.5rem;">
                            <strong>Date:</strong> <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                        </p>
                        
                        <?php if ($event['description']): ?>
                            <p style="font-size: 0.9rem; color: #555; margin-bottom: 1rem;">
                                <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                            </p>
                        <?php endif; ?>
                        
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this event?')">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                            <button type="submit" name="delete_event" class="btn btn-danger" style="width: 100%;">Delete Event</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($events)): ?>
                    <div style="grid-column: 1 / -1; text-align: center; color: #666; padding: 2rem;">
                        No events found. Add your first event using the form above.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>