<?php
    session_start();
    
    // Check if user is logged in as teacher
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
        header('Location: ../Frontend/login.php');
        exit();
    }

    // Get teacher information
    $teacher_id = $_SESSION['user_id'];
    $teacher_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    $teacher_subject = $_SESSION['subject'] ?? 'Teacher';
    $teacher_email = $_SESSION['email'];
    
    // Database connection
    require_once '../includes/config.php';
    
    // Handle form submission for creating assignment
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action']) && $_POST['action'] === 'create_assignment') {
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $description = mysqli_real_escape_string($conn, $_POST['description']);
            $subject = mysqli_real_escape_string($conn, $_POST['subject']);
            $class = mysqli_real_escape_string($conn, $_POST['class']);
            $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
            $due_time = mysqli_real_escape_string($conn, $_POST['due_time']);
            $total_points = (int)$_POST['total_points'];
            
            // If class is "other", use the other_class field
            if ($class === 'other') {
                $class = mysqli_real_escape_string($conn, $_POST['other_class']);
            }
            
            // Validate inputs
            $errors = [];
            if (empty($title)) $errors[] = "Title is required";
            if (empty($description)) $errors[] = "Description is required";
            if (empty($subject)) $errors[] = "Subject is required";
            if (empty($class)) $errors[] = "Class is required";
            if (empty($due_date)) $errors[] = "Due date is required";
            if (empty($due_time)) $errors[] = "Due time is required";
            if ($total_points <= 0) $errors[] = "Total points must be greater than 0";
            
            // Check if teacher is allowed to teach this class
            if (empty($errors)) {
                $check_query = "SELECT 1 FROM teacher_class_relationship 
                               WHERE teacher_id = ? AND class_name = ?";
                $stmt = mysqli_prepare($conn, $check_query);
                mysqli_stmt_bind_param($stmt, 'is', $teacher_id, $class);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) === 0) {
                    $errors[] = "You are not assigned to teach this class";
                }
                mysqli_stmt_close($stmt);
            }
            
            if (empty($errors)) {
                // Combine date and time
                $full_due_date = $due_date . ' ' . $due_time . ':00';
                
                // Insert assignment
                $insert_query = "INSERT INTO assignments 
                                (teacher_id, title, description, subject, class, due_date, total_points, created_at) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $stmt = mysqli_prepare($conn, $insert_query);
                mysqli_stmt_bind_param($stmt, 'isssssi', $teacher_id, $title, $description, $subject, $class, $full_due_date, $total_points);
                
                if (mysqli_stmt_execute($stmt)) {
                    $assignment_id = mysqli_insert_id($conn);
                    
                    // Get all students in the class
                    $students_query = "SELECT id FROM student_details WHERE Class = ?";
                    $stmt2 = mysqli_prepare($conn, $students_query);
                    mysqli_stmt_bind_param($stmt2, 's', $class);
                    mysqli_stmt_execute($stmt2);
                    $result = mysqli_stmt_get_result($stmt2);
                    
                    // Create pending submissions for each student
                    while ($student = mysqli_fetch_assoc($result)) {
                        $submission_sql = "INSERT INTO assignment_submissions (assignment_id, student_id, status) VALUES (?, ?, 'pending')";
                        $stmt3 = mysqli_prepare($conn, $submission_sql);
                        mysqli_stmt_bind_param($stmt3, 'ii', $assignment_id, $student['id']);
                        mysqli_stmt_execute($stmt3);
                        mysqli_stmt_close($stmt3);
                    }
                    mysqli_stmt_close($stmt2);
                    
                    $success_message = "Assignment created successfully!";
                    $message_type = "success";
                } else {
                    $errors[] = "Database error: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            }
            
            if (!empty($errors)) {
                $error_message = implode("<br>", $errors);
                $message_type = "error";
            }
            
            // Reload page to show new assignment
            header("Location: teacher_assignment.php?message=" . urlencode($success_message ?? $error_message ?? '') . "&type=" . urlencode($message_type ?? ''));
            exit();
        }
        
        // Handle assignment deletion
        if (isset($_POST['action']) && $_POST['action'] === 'delete_assignment') {
            $assignment_id = (int)$_POST['assignment_id'];
            
            // Verify teacher owns this assignment
            $check_query = "SELECT id FROM assignments WHERE id = ? AND teacher_id = ?";
            $stmt = mysqli_prepare($conn, $check_query);
            mysqli_stmt_bind_param($stmt, 'ii', $assignment_id, $teacher_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) > 0) {
                mysqli_stmt_close($stmt);
                
                // Delete assignment (cascade will delete submissions)
                $delete_query = "DELETE FROM assignments WHERE id = ?";
                $stmt = mysqli_prepare($conn, $delete_query);
                mysqli_stmt_bind_param($stmt, 'i', $assignment_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Assignment deleted successfully!";
                    $message_type = "success";
                } else {
                    $error_message = "Error deleting assignment: " . mysqli_error($conn);
                    $message_type = "error";
                }
                mysqli_stmt_close($stmt);
            } else {
                $error_message = "Assignment not found or you don't have permission to delete it";
                $message_type = "error";
            }
            
            header("Location: teacher_assignment.php?message=" . urlencode($success_message ?? $error_message ?? '') . "&type=" . urlencode($message_type ?? ''));
            exit();
        }
    }
    
    // Get message from URL if present
    $message = isset($_GET['message']) ? $_GET['message'] : '';
    $message_type = isset($_GET['type']) ? $_GET['type'] : '';
    
    // Get assignments created by this teacher
    $assignments_query = "SELECT 
        a.id,
        a.title,
        a.description,
        a.subject,
        a.class,
        a.due_date,
        a.total_points,
        a.created_at,
        COUNT(DISTINCT s.id) as total_students,
        COUNT(DISTINCT CASE WHEN s.status = 'submitted' OR s.submitted_at IS NOT NULL THEN s.id END) as submitted_count,
        COUNT(DISTINCT CASE WHEN s.status = 'graded' THEN s.id END) as graded_count,
        COUNT(DISTINCT CASE WHEN s.status = 'pending' AND a.due_date < NOW() THEN s.id END) as late_count
    FROM assignments a
    LEFT JOIN assignment_submissions s ON a.id = s.assignment_id
    WHERE a.teacher_id = ?
    GROUP BY a.id
    ORDER BY a.due_date ASC";
    
    $stmt = mysqli_prepare($conn, $assignments_query);
    mysqli_stmt_bind_param($stmt, 'i', $teacher_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $assignments = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $assignments[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
    
    // Get teacher's classes (only classes they actually teach)
    $classes_query = "SELECT DISTINCT tcr.class_name as class 
                     FROM teacher_class_relationship tcr 
                     WHERE tcr.teacher_id = ? 
                     ORDER BY tcr.class_name";
    $stmt = mysqli_prepare($conn, $classes_query);
    mysqli_stmt_bind_param($stmt, 'i', $teacher_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $teacher_classes = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $teacher_classes[] = $row['class'];
        }
    }
    mysqli_stmt_close($stmt);
    
    // If teacher has no classes assigned, show an empty array
    if (empty($teacher_classes)) {
        $teacher_classes = [];
    }
    
    // Get total stats
    $stats_query = "SELECT 
        COUNT(DISTINCT a.id) as total_assignments,
        SUM(a.total_points) as total_points_assigned,
        COUNT(DISTINCT s.student_id) as total_students_assigned,
        AVG(s.score) as avg_score
    FROM assignments a
    LEFT JOIN assignment_submissions s ON a.id = s.assignment_id AND s.status = 'graded'
    WHERE a.teacher_id = ?";
    
    $stmt = mysqli_prepare($conn, $stats_query);
    mysqli_stmt_bind_param($stmt, 'i', $teacher_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $teacher_stats = mysqli_fetch_assoc($result) ?? [];
    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Assignments • Nexa AI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-light: #6366F1;
            --primary-dark: #1d4ed8;
            --secondary: #10b981;
            --accent: #F59E0B;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --pink: #EC4899;
            --purple: #8B5CF6;
            --cyan: #06b6d4;
            --black: #111827;
            --dark-gray: #374151;
            --medium-gray: #6B7280;
            --light-gray: #F9FAFB;
            --white: #FFFFFF;
            --border: #E5E7EB;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFF;
            color: var(--black);
            line-height: 1.6;
        }

        /* Message alerts */
        .message-alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid;
        }
        
        .message-alert.success {
            background: #d1fae5;
            color: #047857;
            border-color: #a7f3d0;
        }
        
        .message-alert.error {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fecaca;
        }
        
        .message-alert.warning {
            background: #fef3c7;
            color: #d97706;
            border-color: #fde68a;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #4F46E5, #8B5CF6);
            color: var(--white);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 30px 0;
            overflow-y: auto;
            transition: var(--transition);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 0 25px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .logo {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            margin-top: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--accent), var(--pink));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
        }

        .user-info h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .user-info p {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .sidebar-nav {
            padding: 0 20px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 8px;
            transition: var(--transition);
            font-weight: 500;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255, 255, 255, 0.15);
            color: var(--white);
            backdrop-filter: blur(10px);
        }

        .nav-item i {
            width: 24px;
            text-align: center;
            font-size: 1.2rem;
        }

        .nav-label {
            flex: 1;
        }

        .badge {
            background: var(--accent);
            color: var(--black);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 30px;
            min-height: 100vh;
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow);
        }

        .page-title h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .page-title p {
            font-size: 1rem;
            color: var(--medium-gray);
            max-width: 600px;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .create-assignment-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--primary), var(--purple));
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .create-assignment-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        }

        .notification-btn, .logout-btn {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-gray);
            border: none;
            color: var(--dark-gray);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .notification-btn:hover, .logout-btn:hover {
            background: var(--primary);
            color: var(--white);
            transform: translateY(-2px);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 20px;
            height: 20px;
            background: var(--accent);
            color: var(--black);
            border-radius: 50%;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-top: 4px solid;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card.primary { border-color: var(--primary); }
        .stat-card.success { border-color: var(--secondary); }
        .stat-card.warning { border-color: var(--warning); }
        .stat-card.danger { border-color: var(--danger); }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
        }

        .stat-card.primary .stat-icon { background: linear-gradient(135deg, var(--primary), #6366f1); }
        .stat-card.success .stat-icon { background: linear-gradient(135deg, var(--secondary), #34d399); }
        .stat-card.warning .stat-icon { background: linear-gradient(135deg, var(--warning), #fbbf24); }
        .stat-card.danger .stat-icon { background: linear-gradient(135deg, var(--danger), #f87171); }

        .stat-info h3 {
            font-size: 2rem;
            font-weight: 700;
            font-family: 'Space Grotesk', sans-serif;
            margin-bottom: 5px;
        }

        .stat-info p {
            font-size: 0.9rem;
            color: var(--medium-gray);
        }

        /* Assignments Grid */
        .assignments-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .assignment-card {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid var(--border);
        }

        .assignment-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .assignment-header {
            padding: 25px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .assignment-info {
            flex: 1;
        }

        .assignment-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--black);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .assignment-subject {
            display: inline-block;
            padding: 4px 12px;
            background: var(--light-gray);
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--medium-gray);
        }

        .assignment-description {
            color: var(--medium-gray);
            margin: 15px 0;
            line-height: 1.6;
        }

        .assignment-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 0.9rem;
            color: var(--medium-gray);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .assignment-progress {
            display: flex;
            align-items: center;
            gap: 15px;
            min-width: 200px;
        }

        .progress-bar {
            flex: 1;
            height: 8px;
            background: var(--border);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--purple));
            border-radius: 4px;
            transition: width 1s ease-out;
        }

        .progress-text {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark-gray);
            min-width: 60px;
            text-align: right;
        }

        .assignment-body {
            padding: 25px;
        }

        .assignment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .detail-card {
            padding: 20px;
            background: var(--light-gray);
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .detail-label {
            font-size: 0.9rem;
            color: var(--medium-gray);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--black);
        }

        .detail-subtext {
            font-size: 0.9rem;
            color: var(--medium-gray);
            margin-top: 5px;
        }

        .assignment-actions {
            display: flex;
            gap: 15px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .btn-primary, .btn-secondary, .btn-success, .btn-danger, .btn-info {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--purple));
            color: var(--white);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        }

        .btn-secondary {
            background: var(--light-gray);
            color: var(--dark-gray);
            border: 2px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--white);
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--secondary), #34d399);
            color: var(--white);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #f87171);
            color: var(--white);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        }

        .btn-info {
            background: linear-gradient(135deg, var(--info), #60a5fa);
            color: var(--white);
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--white);
            border-radius: 16px;
            border: 2px dashed var(--border);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--medium-gray);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: var(--dark-gray);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--medium-gray);
            max-width: 400px;
            margin: 0 auto 20px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            border-radius: 16px;
            max-width: 600px;
            width: 100%;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            padding: 25px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--black);
        }

        .close-modal {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-gray);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--medium-gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .close-modal:hover {
            background: var(--danger);
            border-color: var(--danger);
            color: var(--white);
        }

        .modal-body {
            padding: 25px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-gray);
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .sidebar {
                width: 250px;
            }
            .main-content {
                margin-left: 250px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .menu-toggle {
                display: block;
                background: none;
                border: none;
                font-size: 1.5rem;
                color: var(--dark-gray);
                cursor: pointer;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .assignment-header {
                flex-direction: column;
            }
            .assignment-progress {
                width: 100%;
            }
            .assignment-details {
                grid-template-columns: 1fr;
            }
            .assignment-actions {
                flex-wrap: wrap;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .top-bar {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .assignment-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .assignment-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                NEXA AI
            </div>
            
            <div class="user-profile">
                <div class="avatar">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($teacher_name); ?></h3>
                    <p>Teacher • <?php echo htmlspecialchars($teacher_subject); ?></p>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="teacher_dashboard.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span class="nav-label">Dashboard</span>
            </a>
            <a href="teacher_assignment.php" class="nav-item active">
                <i class="fas fa-tasks"></i>
                <span class="nav-label">Assignments</span>
                <span class="badge"><?php echo $teacher_stats['total_assignments'] ?? 0; ?></span>
            </a>
            <a href="teacher_students.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span class="nav-label">Students</span>
            </a>
            <a href="teacher_grades.php" class="nav-item">
                <i class="fas fa-chart-line"></i>
                <span class="nav-label">Grades</span>
            </a>
            <a href="teacher_analytics.php" class="nav-item">
                <i class="fas fa-chart-bar"></i>
                <span class="nav-label">Analytics</span>
            </a>
            <a href="teacher_ai_tutor.php" class="nav-item">
                <i class="fas fa-robot"></i>
                <span class="nav-label">AI Teaching Assistant</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span class="nav-label">Schedule</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                <span class="nav-label">Settings</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="page-title">
                <h1>📋 Assignments Management</h1>
                <p>Create, manage, and grade assignments for your students.</p>
            </div>
            
            <div class="top-actions">
                <button class="create-assignment-btn" id="createAssignmentBtn">
                    <i class="fas fa-plus-circle"></i>
                    Create New Assignment
                </button>
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge"><?php 
                        $late_total = 0;
                        foreach ($assignments as $assignment) {
                            $late_total += $assignment['late_count'];
                        }
                        echo min($late_total, 9);
                    ?></span>
                </button>
                <a href="../auth/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>

        <!-- Message Display -->
        <?php if ($message): ?>
            <div class="message-alert <?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'error' ? 'exclamation-circle' : 'exclamation-triangle'); ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $teacher_stats['total_assignments'] ?? 0; ?></h3>
                    <p>Total Assignments</p>
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $teacher_stats['total_students_assigned'] ?? 0; ?></h3>
                    <p>Students Assigned</p>
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($teacher_stats['avg_score'] ?? 0, 1); ?>%</h3>
                    <p>Average Score</p>
                </div>
            </div>
            
            <div class="stat-card danger">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php 
                        $late_total = 0;
                        foreach ($assignments as $assignment) {
                            $late_total += $assignment['late_count'];
                        }
                        echo $late_total;
                    ?></h3>
                    <p>Late Submissions</p>
                </div>
            </div>
        </div>

        <!-- Assignments Grid -->
        <div class="assignments-grid">
            <?php if (!empty($assignments)): ?>
                <?php foreach ($assignments as $assignment): ?>
                    <?php 
                    // Format dates
                    $due_date = date('M d, Y', strtotime($assignment['due_date']));
                    $due_time = date('h:i A', strtotime($assignment['due_date']));
                    $created_date = date('M d, Y', strtotime($assignment['created_at']));
                    
                    // Calculate progress
                    $total_students = $assignment['total_students'] ?: 1;
                    $submitted_percent = round(($assignment['submitted_count'] / $total_students) * 100);
                    $graded_percent = round(($assignment['graded_count'] / $total_students) * 100);
                    
                    // Determine status color
                    $progress_color = $submitted_percent < 50 ? 'var(--danger)' : 
                                     ($submitted_percent < 80 ? 'var(--warning)' : 'var(--secondary)');
                    ?>
                    
                    <div class="assignment-card">
                        <div class="assignment-header">
                            <div class="assignment-info">
                                <h3 class="assignment-title">
                                    <?php echo htmlspecialchars($assignment['title']); ?>
                                    <span class="assignment-subject"><?php echo htmlspecialchars($assignment['subject']); ?></span>
                                </h3>
                                
                                <p class="assignment-description">
                                    <?php echo htmlspecialchars($assignment['description']); ?>
                                </p>
                                
                                <div class="assignment-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-users"></i>
                                        <span>Class: <?php echo htmlspecialchars($assignment['class']); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-calendar-day"></i>
                                        <span>Due: <?php echo $due_date; ?> at <?php echo $due_time; ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-star"></i>
                                        <span>Points: <?php echo $assignment['total_points']; ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-paper-plane"></i>
                                        <span><?php echo $assignment['submitted_count']; ?>/<?php echo $total_students; ?> submitted</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="assignment-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $submitted_percent; ?>%; background: <?php echo $progress_color; ?>;"></div>
                                </div>
                                <div class="progress-text">
                                    <?php echo $submitted_percent; ?>%
                                </div>
                            </div>
                        </div>
                        
                        <div class="assignment-body">
                            <div class="assignment-details">
                                <div class="detail-card">
                                    <div class="detail-label">
                                        <i class="fas fa-chart-line"></i>
                                        Submission Stats
                                    </div>
                                    <div class="detail-value">
                                        <?php echo $assignment['submitted_count']; ?>/<?php echo $total_students; ?> submitted
                                    </div>
                                    <div class="detail-subtext">
                                        <?php echo $assignment['graded_count']; ?> graded • <?php echo $assignment['late_count']; ?> late
                                    </div>
                                </div>
                                
                                <div class="detail-card">
                                    <div class="detail-label">
                                        <i class="fas fa-calendar-alt"></i>
                                        Timeline
                                    </div>
                                    <div class="detail-value">
                                        Due <?php echo $due_date; ?>
                                    </div>
                                    <div class="detail-subtext">
                                        Assigned <?php echo $created_date; ?>
                                    </div>
                                </div>
                                
                                <div class="detail-card">
                                    <div class="detail-label">
                                        <i class="fas fa-award"></i>
                                        Grading Status
                                    </div>
                                    <div class="detail-value">
                                        <?php echo $graded_percent; ?>% graded
                                    </div>
                                    <div class="detail-subtext">
                                        <?php echo ($total_students - $assignment['graded_count']); ?> remaining
                                    </div>
                                </div>
                            </div>
                            
                            <div class="assignment-actions">
                                <button class="btn-info view-submissions-btn" data-assignment-id="<?php echo $assignment['id']; ?>">
                                    <i class="fas fa-eye"></i>
                                    View Submissions
                                </button>
                                <button class="btn-success grade-assignments-btn" data-assignment-id="<?php echo $assignment['id']; ?>">
                                    <i class="fas fa-check-circle"></i>
                                    Grade Assignments
                                </button>
                                <button class="btn-primary edit-assignment-btn" data-assignment-id="<?php echo $assignment['id']; ?>">
                                    <i class="fas fa-edit"></i>
                                    Edit Assignment
                                </button>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                                    <input type="hidden" name="action" value="delete_assignment">
                                    <button type="submit" class="btn-danger delete-assignment-btn" onclick="return confirm('Are you sure you want to delete this assignment? This will also delete all submissions.')">
                                        <i class="fas fa-trash"></i>
                                        Delete
                                    </button>
                                </form>
                                <a href="assignment_analytics.php?id=<?php echo $assignment['id']; ?>" class="btn-secondary">
                                    <i class="fas fa-chart-bar"></i>
                                    Analytics
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-check"></i>
                    <h3>No Assignments Created Yet</h3>
                    <p>Start by creating your first assignment for your students.</p>
                    <button class="btn-primary" id="createFirstAssignmentBtn">
                        <i class="fas fa-plus-circle"></i>
                        Create Your First Assignment
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Create Assignment Modal -->
    <div class="modal" id="createAssignmentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create New Assignment</h3>
                <button class="close-modal" id="closeModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="createAssignmentForm" method="POST" action="">
                <input type="hidden" name="action" value="create_assignment">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="assignmentTitle">
                            <i class="fas fa-heading"></i>
                            Assignment Title
                        </label>
                        <input 
                            type="text" 
                            id="assignmentTitle" 
                            name="title" 
                            placeholder="e.g., Algebra Quiz, English Essay, Science Project" 
                            required>
                    </div>
                    
                    <div class="form-group">
                        <label for="assignmentDescription">
                            <i class="fas fa-align-left"></i>
                            Description
                        </label>
                        <textarea 
                            id="assignmentDescription" 
                            name="description" 
                            placeholder="Provide clear instructions for the assignment..." 
                            required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="assignmentSubject">
                                <i class="fas fa-book"></i>
                                Subject
                            </label>
                            <select id="assignmentSubject" name="subject" required>
                                <option value="">Select Subject</option>
                                <option value="Mathematics">Mathematics</option>
                                <option value="English">English</option>
                                <option value="Science">Science</option>
                                <option value="History">History</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Art">Art</option>
                                <option value="Physical Education">Physical Education</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="assignmentClass">
                                <i class="fas fa-users"></i>
                                Class
                            </label>
                            <select id="assignmentClass" name="class" required>
                                <option value="">Select Class</option>
                                <?php foreach ($teacher_classes as $class): ?>
                                    <option value="<?php echo htmlspecialchars($class); ?>">
                                        <?php echo htmlspecialchars($class); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="other">Other (specify below)</option>
                            </select>
                            <input type="text" id="otherClass" name="other_class" placeholder="Enter class name" style="margin-top: 10px; display: none;">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dueDate">
                                <i class="fas fa-calendar-day"></i>
                                Due Date
                            </label>
                            <input 
                                type="date" 
                                id="dueDate" 
                                name="due_date" 
                                required
                                min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="dueTime">
                                <i class="fas fa-clock"></i>
                                Due Time
                            </label>
                            <input 
                                type="time" 
                                id="dueTime" 
                                name="due_time" 
                                value="23:59"
                                required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="totalPoints">
                            <i class="fas fa-star"></i>
                            Total Points
                        </label>
                        <input 
                            type="number" 
                            id="totalPoints" 
                            name="total_points" 
                            placeholder="100" 
                            min="1" 
                            max="1000"
                            value="100"
                            required>
                    </div>
                    
                    <div style="background: #f0f9ff; padding: 15px; border-radius: 8px; margin-top: 20px;">
                        <div style="display: flex; align-items: center; gap: 10px; color: var(--primary); font-weight: 600; margin-bottom: 10px;">
                            <i class="fas fa-lightbulb"></i>
                            Tips for Great Assignments
                        </div>
                        <ul style="color: var(--medium-gray); padding-left: 20px; font-size: 0.9rem;">
                            <li>Be clear and specific in your instructions</li>
                            <li>Set realistic deadlines for completion</li>
                            <li>Include examples if helpful</li>
                            <li>Specify the format for submissions</li>
                            <li>Mention grading criteria if applicable</li>
                        </ul>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="cancelBtn">
                        Cancel
                    </button>
                    <button type="submit" class="btn-success">
                        <i class="fas fa-plus-circle"></i>
                        Create Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
        
        // Modal functionality
        const createAssignmentModal = document.getElementById('createAssignmentModal');
        const closeModal = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const createAssignmentBtn = document.getElementById('createAssignmentBtn');
        const createFirstAssignmentBtn = document.getElementById('createFirstAssignmentBtn');
        const assignmentClassSelect = document.getElementById('assignmentClass');
        const otherClassInput = document.getElementById('otherClass');
        
        // Open modal when create button is clicked
        if (createAssignmentBtn) {
            createAssignmentBtn.addEventListener('click', () => {
                createAssignmentModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }
        
        if (createFirstAssignmentBtn) {
            createFirstAssignmentBtn.addEventListener('click', () => {
                createAssignmentModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }
        
        // Close modal
        function closeCreateModal() {
            createAssignmentModal.classList.remove('active');
            document.body.style.overflow = 'auto';
            document.getElementById('createAssignmentForm').reset();
            otherClassInput.style.display = 'none';
        }
        
        closeModal.addEventListener('click', closeCreateModal);
        cancelBtn.addEventListener('click', closeCreateModal);
        
        // Close modal when clicking outside
        createAssignmentModal.addEventListener('click', (e) => {
            if (e.target === createAssignmentModal) {
                closeCreateModal();
            }
        });
        
        // Escape key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && createAssignmentModal.classList.contains('active')) {
                closeCreateModal();
            }
        });
        
        // Show/hide other class input
        assignmentClassSelect.addEventListener('change', function() {
            if (this.value === 'other') {
                otherClassInput.style.display = 'block';
                otherClassInput.required = true;
            } else {
                otherClassInput.style.display = 'none';
                otherClassInput.required = false;
            }
        });
        
        // Set minimum date to today
        document.getElementById('dueDate').min = new Date().toISOString().split('T')[0];
        
        // Assignment action buttons
        document.querySelectorAll('.view-submissions-btn').forEach(button => {
            button.addEventListener('click', function() {
                const assignmentId = this.getAttribute('data-assignment-id');
                alert(`Viewing submissions for assignment ID: ${assignmentId}\n\nThis would open a submissions management page in a real application.`);
            });
        });
        
        document.querySelectorAll('.grade-assignments-btn').forEach(button => {
            button.addEventListener('click', function() {
                const assignmentId = this.getAttribute('data-assignment-id');
                alert(`Grading assignments for ID: ${assignmentId}\n\nThis would open a grading interface in a real application.`);
            });
        });
        
        document.querySelectorAll('.edit-assignment-btn').forEach(button => {
            button.addEventListener('click', function() {
                const assignmentId = this.getAttribute('data-assignment-id');
                alert(`Editing assignment ID: ${assignmentId}\n\nThis would open an edit form in a real application.`);
            });
        });
        
        // Notification click
        document.querySelector('.notification-btn').addEventListener('click', () => {
            let lateTotal = 0;
            <?php foreach ($assignments as $assignment): ?>
                lateTotal += <?php echo $assignment['late_count']; ?>;
            <?php endforeach; ?>
            
            if (lateTotal > 0) {
                alert(`You have ${lateTotal} late submissions to review.`);
            } else {
                alert('All submissions are up to date!');
            }
        });
        
        // Auto-check for due dates
        function checkDueDates() {
            const now = new Date();
            const assignmentCards = document.querySelectorAll('.assignment-card');
            
            assignmentCards.forEach(card => {
                const dueDateText = card.querySelector('.meta-item:nth-child(2) span').textContent;
                const dueMatch = dueDateText.match(/Due: (\w+ \d+, \d+) at (\d+:\d+ [AP]M)/);
                
                if (dueMatch) {
                    const dueDateStr = `${dueMatch[1]} ${dueMatch[2]}`;
                    const dueDate = new Date(dueDateStr);
                    
                    if (dueDate < now) {
                        // Add overdue styling
                        card.style.borderColor = 'var(--danger)';
                        card.style.boxShadow = '0 0 0 2px rgba(239, 68, 68, 0.1)';
                    }
                }
            });
        }
        
        // Check due dates on load
        checkDueDates();
    </script>
</body>
</html>