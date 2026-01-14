<?php
session_start();

// Check if user is logged in as teacher
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../Frontend/login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Database connection
require_once '../includes/config.php';

// Get assignment details
$assignment_query = "SELECT * FROM assignments WHERE id = ? AND teacher_id = ?";
$stmt = mysqli_prepare($conn, $assignment_query);
mysqli_stmt_bind_param($stmt, 'ii', $assignment_id, $teacher_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$assignment = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$assignment) {
    die("Assignment not found or you don't have permission to edit it.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $class = mysqli_real_escape_string($conn, $_POST['class']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $due_time = mysqli_real_escape_string($conn, $_POST['due_time']);
    $total_points = (int)$_POST['total_points'];
    
    $full_due_date = $due_date . ' ' . $due_time . ':00';
    
    $update_query = "UPDATE assignments SET 
                    title = ?, description = ?, subject = ?, class = ?, 
                    due_date = ?, total_points = ?, updated_at = NOW()
                    WHERE id = ? AND teacher_id = ?";
    
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'sssssiii', $title, $description, $subject, $class, 
                          $full_due_date, $total_points, $assignment_id, $teacher_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Assignment updated successfully!";
        // Refresh assignment data
        $assignment['title'] = $title;
        $assignment['description'] = $description;
        $assignment['subject'] = $subject;
        $assignment['class'] = $class;
        $assignment['due_date'] = $full_due_date;
        $assignment['total_points'] = $total_points;
    } else {
        $error = "Error updating assignment: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// Get teacher's classes
$classes_query = "SELECT DISTINCT class_name FROM teacher_class_relationship 
                  WHERE teacher_id = ? ORDER BY class_name";
$stmt = mysqli_prepare($conn, $classes_query);
mysqli_stmt_bind_param($stmt, 'i', $teacher_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$teacher_classes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $teacher_classes[] = $row['class_name'];
}
mysqli_stmt_close($stmt);

mysqli_close($conn);

// Parse existing due date
$due_date = date('Y-m-d', strtotime($assignment['due_date']));
$due_time = date('H:i', strtotime($assignment['due_date']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Assignment • Nexa AI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4F46E5;
            --secondary: #10b981;
            --black: #111827;
            --white: #FFFFFF;
            --border: #E5E7EB;
            --light-gray: #F9FAFB;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFF;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary), #8B5CF6);
            color: var(--white);
            padding: 30px;
        }
        
        .content { padding: 30px; }
        
        .form-group { margin-bottom: 20px; }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-primary { background: var(--primary); color: var(--white); }
        .btn-secondary { background: var(--light-gray); color: var(--black); border: 1px solid var(--border); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✏️ Edit Assignment</h1>
            <a href="teacher_assignment.php" class="btn btn-secondary" style="margin-top: 15px;">
                <i class="fas fa-arrow-left"></i> Back to Assignments
            </a>
        </div>
        
        <div class="content">
            <?php if (isset($success)): ?>
                <div style="background: #d1fae5; color: #047857; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php elseif (isset($error)): ?>
                <div style="background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="title">Assignment Title:</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($assignment['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($assignment['description']); ?></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="subject">Subject:</label>
                        <select id="subject" name="subject" required>
                            <option value="Mathematics" <?php echo $assignment['subject'] == 'Mathematics' ? 'selected' : ''; ?>>Mathematics</option>
                            <option value="English" <?php echo $assignment['subject'] == 'English' ? 'selected' : ''; ?>>English</option>
                            <option value="Science" <?php echo $assignment['subject'] == 'Science' ? 'selected' : ''; ?>>Science</option>
                            <option value="History" <?php echo $assignment['subject'] == 'History' ? 'selected' : ''; ?>>History</option>
                            <option value="Computer Science" <?php echo $assignment['subject'] == 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="class">Class:</label>
                        <select id="class" name="class" required>
                            <option value="">Select Class</option>
                            <?php foreach ($teacher_classes as $class): ?>
                                <option value="<?php echo htmlspecialchars($class); ?>" 
                                    <?php echo $assignment['class'] == $class ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="due_date">Due Date:</label>
                        <input type="date" id="due_date" name="due_date" value="<?php echo $due_date; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="due_time">Due Time:</label>
                        <input type="time" id="due_time" name="due_time" value="<?php echo $due_time; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="total_points">Total Points:</label>
                    <input type="number" id="total_points" name="total_points" 
                           value="<?php echo $assignment['total_points']; ?>" min="1" max="1000" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Assignment
                </button>
            </form>
        </div>
    </div>
</body>
</html>