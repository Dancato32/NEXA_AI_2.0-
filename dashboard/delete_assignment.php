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

// Verify teacher owns this assignment
$assignment_query = "SELECT * FROM assignments WHERE id = ? AND teacher_id = ?";
$stmt = mysqli_prepare($conn, $assignment_query);
mysqli_stmt_bind_param($stmt, 'ii', $assignment_id, $teacher_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$assignment = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$assignment) {
    die("Assignment not found or you don't have permission to delete it.");
}

// Handle deletion
$deleted = false;
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    // Delete assignment (cascade will delete submissions)
    $delete_query = "DELETE FROM assignments WHERE id = ? AND teacher_id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, 'ii', $assignment_id, $teacher_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $deleted = true;
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);

if ($deleted) {
    header('Location: teacher_assignment.php?message=' . urlencode('Assignment deleted successfully!') . '&type=success');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Assignment • Nexa AI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --danger: #ef4444;
            --black: #111827;
            --white: #FFFFFF;
            --border: #E5E7EB;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFF;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .container {
            max-width: 600px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, var(--danger), #f87171);
            color: var(--white);
            padding: 30px;
            text-align: center;
        }
        
        .content {
            padding: 40px;
            text-align: center;
        }
        
        .warning-icon {
            font-size: 4rem;
            color: var(--danger);
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            margin: 10px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-danger { background: var(--danger); color: var(--white); }
        .btn-secondary { background: var(--border); color: var(--black); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗑️ Delete Assignment</h1>
        </div>
        
        <div class="content">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <h2>Are you sure?</h2>
            
            <p style="margin: 20px 0; line-height: 1.6;">
                You are about to delete the assignment:<br>
                <strong>"<?php echo htmlspecialchars($assignment['title']); ?>"</strong>
            </p>
            
            <div style="background: #fef3c7; color: #92400e; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Warning:</strong> This action cannot be undone. All student submissions 
                and grades for this assignment will also be permanently deleted.
            </div>
            
            <div style="margin-top: 30px;">
                <a href="teacher_assignment.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                
                <a href="delete_assignment.php?id=<?php echo $assignment_id; ?>&confirm=yes" 
                   class="btn btn-danger">
                    <i class="fas fa-trash"></i> Yes, Delete Assignment
                </a>
            </div>
        </div>
    </div>
</body>
</html>