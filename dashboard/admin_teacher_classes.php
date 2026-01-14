
<?php
session_start();

// Check if user is logged in as admin (you'll need to add admin functionality)
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    // For now, let teachers view this too
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
        header('Location: ../Frontend/login.php');
        exit();
    }
}

// Database connection
require_once '../includes/config.php';

// Get all teachers
$teachers_query = "SELECT id, firstName, lastName, emailAddress, subject FROM teachers_details ORDER BY lastName";
$teachers_result = mysqli_query($conn, $teachers_query);
$teachers = [];
while ($row = mysqli_fetch_assoc($teachers_result)) {
    $teachers[] = $row;
}

// Get all unique classes from students
$classes_query = "SELECT DISTINCT Class FROM student_details ORDER BY Class";
$classes_result = mysqli_query($conn, $classes_query);
$all_classes = [];
while ($row = mysqli_fetch_assoc($classes_result)) {
    $all_classes[] = $row['Class'];
}

// Get current teacher-class assignments
$assignments_query = "SELECT tcr.*, t.firstName, t.lastName 
                     FROM teacher_class_relationship tcr
                     JOIN teachers_details t ON tcr.teacher_id = t.id
                     ORDER BY t.lastName, tcr.class_name";
$assignments_result = mysqli_query($conn, $assignments_query);
$current_assignments = [];
while ($row = mysqli_fetch_assoc($assignments_result)) {
    $current_assignments[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'assign') {
        $teacher_id = (int)$_POST['teacher_id'];
        $class_name = mysqli_real_escape_string($conn, $_POST['class_name']);
        
        // Check if assignment already exists
        $check_query = "SELECT id FROM teacher_class_relationship 
                       WHERE teacher_id = ? AND class_name = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, 'is', $teacher_id, $class_name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) === 0) {
            mysqli_stmt_close($stmt);
            
            // Insert new assignment
            $insert_query = "INSERT INTO teacher_class_relationship (teacher_id, class_name) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, 'is', $teacher_id, $class_name);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Teacher assigned to class successfully!";
                $message_type = "success";
            } else {
                $message = "Error: " . mysqli_error($conn);
                $message_type = "error";
            }
        } else {
            $message = "This teacher is already assigned to this class.";
            $message_type = "warning";
        }
        mysqli_stmt_close($stmt);
        
        // Refresh page to show updated assignments
        header("Location: admin_teacher_classes.php?message=" . urlencode($message) . "&type=" . urlencode($message_type));
        exit();
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'remove') {
        $assignment_id = (int)$_POST['assignment_id'];
        
        $delete_query = "DELETE FROM teacher_class_relationship WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, 'i', $assignment_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "Assignment removed successfully!";
            $message_type = "success";
        } else {
            $message = "Error: " . mysqli_error($conn);
            $message_type = "error";
        }
        mysqli_stmt_close($stmt);
        
        header("Location: admin_teacher_classes.php?message=" . urlencode($message) . "&type=" . urlencode($message_type));
        exit();
    }
}

// Get message from URL if present
$message = isset($_GET['message']) ? $_GET['message'] : '';
$message_type = isset($_GET['type']) ? $_GET['type'] : '';

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teacher Classes • Nexa AI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --secondary: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #111827;
            --light: #f9fafb;
            --white: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: var(--dark);
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            color: var(--white);
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        /* Message alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert.success {
            background: #d1fae5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        
        .alert.error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .alert.warning {
            background: #fef3c7;
            color: #d97706;
            border: 1px solid #fde68a;
        }
        
        /* Forms */
        .form-section {
            background: var(--light);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid #e5e7eb;
        }
        
        .form-section h2 {
            color: var(--dark);
            margin-bottom: 20px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #4b5563;
        }
        
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            background: var(--white);
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: #4338ca;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: var(--danger);
            color: var(--white);
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        /* Assignments table */
        .assignments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .assignments-table th {
            background: #f3f4f6;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #4b5563;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .assignments-table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .assignments-table tr:hover {
            background: #f9fafb;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #d1d5db;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .content {
                padding: 20px;
            }
            
            .assignments-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chalkboard-teacher"></i> Manage Teacher-Class Assignments</h1>
            <p>Assign teachers to classes they teach</p>
        </div>
        
        <div class="content">
            <?php if ($message): ?>
                <div class="alert <?php echo $message_type; ?>">
                    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'error' ? 'exclamation-circle' : 'exclamation-triangle'); ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Assign Teacher to Class Form -->
            <div class="form-section">
                <h2><i class="fas fa-plus-circle"></i> Assign Teacher to Class</h2>
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="teacher_id">Select Teacher</label>
                            <select id="teacher_id" name="teacher_id" required>
                                <option value="">Choose a teacher...</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>">
                                        <?php echo htmlspecialchars($teacher['firstName'] . ' ' . $teacher['lastName'] . ' (' . $teacher['subject'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="class_name">Select Class</label>
                            <select id="class_name" name="class_name" required>
                                <option value="">Choose a class...</option>
                                <?php foreach ($all_classes as $class): ?>
                                    <option value="<?php echo htmlspecialchars($class); ?>">
                                        <?php echo htmlspecialchars($class); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="action" value="assign" class="btn btn-primary">
                                <i class="fas fa-link"></i> Assign
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Current Assignments -->
            <div class="form-section">
                <h2><i class="fas fa-list"></i> Current Teacher-Class Assignments</h2>
                
                <?php if (empty($current_assignments)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        <h3>No Assignments Yet</h3>
                        <p>Start by assigning teachers to classes using the form above.</p>
                    </div>
                <?php else: ?>
                    <table class="assignments-table">
                        <thead>
                            <tr>
                                <th>Teacher</th>
                                <th>Class</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($current_assignments as $assignment): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($assignment['firstName'] . ' ' . $assignment['lastName']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($assignment['class_name']); ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                                            <button type="submit" name="action" value="remove" class="btn btn-danger" 
                                                    onclick="return confirm('Are you sure you want to remove this assignment?')">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <!-- Information Section -->
            <div class="form-section">
                <h2><i class="fas fa-info-circle"></i> How It Works</h2>
                <div style="color: #6b7280; line-height: 1.6;">
                    <p><strong>1. Assign Teachers to Classes:</strong> Use the form above to link teachers with the classes they teach.</p>
                    <p><strong>2. Teachers Create Assignments:</strong> Teachers can only create assignments for classes they're assigned to.</p>
                    <p><strong>3. Students See Assignments:</strong> Students only see assignments from teachers who teach their class.</p>
                    <p><strong>4. Manage Relationships:</strong> Use the table above to view or remove existing assignments.</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-refresh page every 30 seconds to show updates
        setTimeout(() => {
            window.location.reload();
        }, 30000);
        
        // Confirm before removing assignment
        document.querySelectorAll('form[action*="remove"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('Are you sure you want to remove this assignment? The teacher will no longer be able to create assignments for this class.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
