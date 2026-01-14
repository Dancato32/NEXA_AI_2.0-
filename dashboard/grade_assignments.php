<?php
session_start();

// Check if user is logged in as teacher
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../Frontend/login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$student_id = isset($_GET['student']) ? (int)$_GET['student'] : 0;

// Database connection
require_once '../includes/config.php';

// Verify teacher owns this assignment
$assignment_query = "SELECT a.*, t.firstName as teacher_first, t.lastName as teacher_last 
                    FROM assignments a
                    JOIN teachers_details t ON a.teacher_id = t.id
                    WHERE a.id = ? AND a.teacher_id = ?";
$stmt = mysqli_prepare($conn, $assignment_query);
mysqli_stmt_bind_param($stmt, 'ii', $assignment_id, $teacher_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$assignment = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$assignment) {
    die("Assignment not found or you don't have permission to grade it.");
}

// Handle form submission for grading
$success = $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = (int)$_POST['submission_id'];
    $score = (float)$_POST['score'];
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    
    // Validate score
    if ($score < 0 || $score > $assignment['total_points']) {
        $error = "Score must be between 0 and " . $assignment['total_points'];
    } else {
        // Update submission
        $update_query = "UPDATE assignment_submissions 
                        SET score = ?, feedback = ?, graded_at = NOW(), status = 'graded' 
                        WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'dsi', $score, $feedback, $submission_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Grade submitted successfully!";
            // If grading a specific student, redirect back to submissions
            if ($student_id > 0) {
                header("Location: view_submissions.php?id=" . $assignment_id . "&graded=success");
                exit();
            }
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// Get submission to grade
if ($student_id > 0) {
    $submission_query = "SELECT s.*, sd.firstName, sd.lastName, sd.UserName, sd.Class, sd.Gender
                        FROM assignment_submissions s
                        JOIN student_details sd ON s.student_id = sd.id
                        WHERE s.assignment_id = ? AND s.student_id = ?";
    $stmt = mysqli_prepare($conn, $submission_query);
    mysqli_stmt_bind_param($stmt, 'ii', $assignment_id, $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $submission = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$submission) {
        $error = "Student submission not found.";
    }
} else {
    // Get all ungraded submissions
    $ungraded_query = "SELECT s.*, sd.firstName, sd.lastName, sd.UserName, sd.Class, 
                              CASE 
                                WHEN s.status = 'submitted' THEN 'Submitted'
                                WHEN s.submitted_at IS NOT NULL THEN 'Submitted'
                                WHEN a.due_date < NOW() THEN 'Late'
                                ELSE 'Pending'
                              END as display_status
                      FROM assignment_submissions s
                      JOIN student_details sd ON s.student_id = sd.id
                      JOIN assignments a ON s.assignment_id = a.id
                      WHERE s.assignment_id = ? 
                      AND (s.status != 'graded' OR s.status IS NULL)
                      AND s.submitted_at IS NOT NULL
                      ORDER BY s.submitted_at DESC";
    $stmt = mysqli_prepare($conn, $ungraded_query);
    mysqli_stmt_bind_param($stmt, 'i', $assignment_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $ungraded_submissions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ungraded_submissions[] = $row;
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);

// Parse assignment due date
$due_date = date('F j, Y', strtotime($assignment['due_date']));
$due_time = date('g:i A', strtotime($assignment['due_date']));
$teacher_name = $assignment['teacher_first'] . ' ' . $assignment['teacher_last'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Assignments • Nexa AI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-light: #6366F1;
            --primary-dark: #4338CA;
            --secondary: #10B981;
            --secondary-light: #34D399;
            --accent: #F59E0B;
            --accent-light: #FBBF24;
            --danger: #EF4444;
            --danger-light: #F87171;
            --success: #10B981;
            --warning: #F59E0B;
            --info: #3B82F6;
            --dark: #111827;
            --dark-gray: #374151;
            --medium-gray: #6B7280;
            --light-gray: #F9FAFB;
            --white: #FFFFFF;
            --border: #E5E7EB;
            --border-light: #F3F4F6;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: var(--dark);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Header Section */
        .header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.2);
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
        }

        .header h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 800px;
            line-height: 1.6;
        }

        .assignment-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-top: 15px;
            backdrop-filter: blur(10px);
        }

        /* Content Layout */
        .content {
            padding: 40px;
            min-height: 500px;
        }

        /* Alerts */
        .alert {
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideIn 0.5s ease-out;
            border-left: 5px solid;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .alert.success {
            background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
            color: #065F46;
            border-left-color: var(--success);
        }

        .alert.error {
            background: linear-gradient(135deg, #FEE2E2, #FECACA);
            color: #991B1B;
            border-left-color: var(--danger);
        }

        .alert.warning {
            background: linear-gradient(135deg, #FEF3C7, #FDE68A);
            color: #92400E;
            border-left-color: var(--warning);
        }

        /* Grading Form - Beautiful Layout */
        .grading-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
            margin-top: 30px;
        }

        @media (max-width: 1200px) {
            .grading-container {
                grid-template-columns: 1fr;
            }
        }

        /* Submission Preview */
        .submission-preview {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            height: fit-content;
        }

        .preview-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-light);
        }

        .student-avatar {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--white);
        }

        .student-info h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .student-meta {
            display: flex;
            gap: 15px;
            font-size: 0.9rem;
            color: var(--medium-gray);
        }

        .student-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .submission-content {
            margin-top: 30px;
        }

        .content-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-light);
        }

        .section-title i {
            color: var(--primary);
        }

        .text-display {
            background: var(--light-gray);
            border-radius: 12px;
            padding: 20px;
            max-height: 300px;
            overflow-y: auto;
            font-size: 1rem;
            line-height: 1.6;
            border: 1px solid var(--border);
        }

        .text-display:empty::before {
            content: "No text submitted";
            color: var(--medium-gray);
            font-style: italic;
        }

        /* Grading Form */
        .grading-form {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            height: fit-content;
            position: sticky;
            top: 30px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .points-display {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            text-align: center;
            margin-bottom: 5px;
        }

        .points-label {
            text-align: center;
            color: var(--medium-gray);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label i {
            color: var(--primary);
        }

        .score-input-container {
            position: relative;
        }

        .score-input {
            width: 100%;
            padding: 20px 20px 20px 60px;
            font-size: 1.5rem;
            font-weight: 600;
            border: 3px solid var(--border);
            border-radius: 16px;
            background: var(--light-gray);
            text-align: center;
            transition: var(--transition);
        }

        .score-input:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .score-prefix {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
        }

        .score-slider {
            width: 100%;
            height: 8px;
            -webkit-appearance: none;
            appearance: none;
            background: linear-gradient(to right, var(--danger), var(--warning), var(--success));
            border-radius: 4px;
            margin-top: 15px;
            outline: none;
        }

        .score-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--white);
            border: 3px solid var(--primary);
            cursor: pointer;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .score-slider::-webkit-slider-thumb:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-lg);
        }

        .score-marks {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 0.85rem;
            color: var(--medium-gray);
        }

        .feedback-input {
            width: 100%;
            padding: 20px;
            border: 3px solid var(--border);
            border-radius: 16px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            resize: vertical;
            min-height: 150px;
            background: var(--light-gray);
            transition: var(--transition);
        }

        .feedback-input:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .feedback-templates {
            margin-top: 15px;
        }

        .template-title {
            font-size: 0.9rem;
            color: var(--medium-gray);
            margin-bottom: 10px;
        }

        .template-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .template-btn {
            padding: 8px 16px;
            background: var(--light-gray);
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .template-btn:hover {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 18px 32px;
            border-radius: 16px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex: 1;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--light-gray);
            color: var(--dark);
            border: 2px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--white);
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-3px);
        }

        /* Ungraded Submissions List */
        .ungraded-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .submission-card {
            background: var(--white);
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--shadow);
            border: 2px solid transparent;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .submission-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .submission-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .card-avatar {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
        }

        .card-info h4 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .card-meta {
            font-size: 0.9rem;
            color: var(--medium-gray);
        }

        .submission-preview-text {
            background: var(--light-gray);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            max-height: 100px;
            overflow: hidden;
            position: relative;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .submission-preview-text:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            background: linear-gradient(to bottom, transparent, var(--light-gray));
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-submitted { background: #DBEAFE; color: #1E40AF; }
        .status-late { background: #FEE2E2; color: #991B1B; }

        .grade-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .grade-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--medium-gray);
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            color: var(--dark-gray);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--medium-gray);
            max-width: 400px;
            margin: 0 auto 30px;
            line-height: 1.6;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                border-radius: 20px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 2rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .grading-container {
                gap: 30px;
            }
            
            .submission-card {
                padding: 20px;
            }
            
            .btn {
                padding: 15px 25px;
                font-size: 1rem;
            }
            
            .ungraded-list {
                grid-template-columns: 1fr;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: var(--white);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <a href="view_submissions.php?id=<?php echo $assignment_id; ?>" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Submissions
                </a>
                
                <h1>
                    <i class="fas fa-graduation-cap"></i>
                    Grade Assignments
                </h1>
                
                <p>
                    Review and grade student submissions for:
                    <strong>"<?php echo htmlspecialchars($assignment['title']); ?>"</strong>
                </p>
                
                <div class="assignment-badge">
                    <i class="fas fa-book"></i>
                    <span><?php echo htmlspecialchars($assignment['subject']); ?></span>
                    <i class="fas fa-users"></i>
                    <span><?php echo htmlspecialchars($assignment['class']); ?></span>
                    <i class="fas fa-calendar-day"></i>
                    <span>Due: <?php echo $due_date; ?> at <?php echo $due_time; ?></span>
                    <i class="fas fa-star"></i>
                    <span>Max Points: <?php echo $assignment['total_points']; ?></span>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <?php if ($success): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Success!</strong> <?php echo htmlspecialchars($success); ?>
                    </div>
                </div>
            <?php elseif ($error): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($student_id > 0 && isset($submission)): ?>
                <!-- Grade Specific Student -->
                <div class="grading-container">
                    <!-- Left: Submission Preview -->
                    <div class="submission-preview">
                        <div class="preview-header">
                            <div class="student-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="student-info">
                                <h3><?php echo htmlspecialchars($submission['firstName'] . ' ' . $submission['lastName']); ?></h3>
                                <div class="student-meta">
                                    <span>
                                        <i class="fas fa-user-graduate"></i>
                                        <?php echo htmlspecialchars($submission['UserName']); ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-users"></i>
                                        <?php echo htmlspecialchars($submission['Class']); ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-clock"></i>
                                        <?php echo date('M d, Y g:i A', strtotime($submission['submitted_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="submission-content">
                            <div class="content-section">
                                <div class="section-title">
                                    <i class="fas fa-edit"></i>
                                    Submission Text
                                </div>
                                <div class="text-display">
                                    <?php echo nl2br(htmlspecialchars($submission['submission_text'] ?? 'No text submitted')); ?>
                                </div>
                            </div>

                            <?php if ($submission['attachment_url']): ?>
                            <div class="content-section">
                                <div class="section-title">
                                    <i class="fas fa-paperclip"></i>
                                    Attachments
                                </div>
                                <a href="../<?php echo htmlspecialchars($submission['attachment_url']); ?>" 
                                   target="_blank" 
                                   class="grade-btn" 
                                   style="text-decoration: none; display: inline-flex;">
                                    <i class="fas fa-download"></i> Download Attachment
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right: Grading Form -->
                    <div class="grading-form">
                        <form method="POST" id="gradeForm">
                            <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                            
                            <div class="form-header">
                                <h3>Assign Grade</h3>
                                <div class="points-display"><?php echo $assignment['total_points']; ?></div>
                                <div class="points-label">Total Points Available</div>
                            </div>

                            <div class="form-group">
                                <label for="score">
                                    <i class="fas fa-star"></i>
                                    Score
                                </label>
                                <div class="score-input-container">
                                    <span class="score-prefix">/</span>
                                    <input type="number" 
                                           id="score" 
                                           name="score" 
                                           class="score-input"
                                           min="0" 
                                           max="<?php echo $assignment['total_points']; ?>" 
                                           step="0.5" 
                                           value="<?php echo isset($submission['score']) ? $submission['score'] : ''; ?>" 
                                           required>
                                </div>
                                
                                <input type="range" 
                                       id="scoreSlider" 
                                       class="score-slider"
                                       min="0" 
                                       max="<?php echo $assignment['total_points']; ?>" 
                                       step="0.5" 
                                       value="<?php echo isset($submission['score']) ? $submission['score'] : 0; ?>">
                                
                                <div class="score-marks">
                                    <span>0</span>
                                    <span><?php echo round($assignment['total_points'] * 0.25); ?></span>
                                    <span><?php echo round($assignment['total_points'] * 0.5); ?></span>
                                    <span><?php echo round($assignment['total_points'] * 0.75); ?></span>
                                    <span><?php echo $assignment['total_points']; ?></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="feedback">
                                    <i class="fas fa-comment-dots"></i>
                                    Feedback
                                </label>
                                <textarea 
                                    id="feedback" 
                                    name="feedback" 
                                    class="feedback-input" 
                                    placeholder="Provide constructive feedback for the student..."><?php echo htmlspecialchars($submission['feedback'] ?? ''); ?></textarea>
                                
                                <div class="feedback-templates">
                                    <div class="template-title">Quick Feedback Templates:</div>
                                    <div class="template-buttons">
                                        <button type="button" class="template-btn" onclick="addTemplate('Excellent work! Well organized and thorough analysis.')">Excellent</button>
                                        <button type="button" class="template-btn" onclick="addTemplate('Good job, but needs more detail in the analysis.')">Needs Detail</button>
                                        <button type="button" class="template-btn" onclick="addTemplate('Please review the requirements and resubmit.')">Revise & Resubmit</button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-check-circle"></i> Submit Grade
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            <?php elseif (isset($ungraded_submissions) && !empty($ungraded_submissions)): ?>
                <!-- List of Ungraded Submissions -->
                <h2 style="font-family: 'Poppins', sans-serif; font-size: 1.8rem; margin-bottom: 10px;">
                    <i class="fas fa-list-check"></i> Ungraded Submissions
                </h2>
                <p style="color: var(--medium-gray); margin-bottom: 30px;">
                    Select a student submission to grade. <?php echo count($ungraded_submissions); ?> submissions need grading.
                </p>

                <div class="ungraded-list">
                    <?php foreach ($ungraded_submissions as $sub): ?>
                        <div class="submission-card">
                            <div class="card-header">
                                <div class="card-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="card-info">
                                    <h4><?php echo htmlspecialchars($sub['firstName'] . ' ' . $sub['lastName']); ?></h4>
                                    <div class="card-meta">
                                        <span><?php echo htmlspecialchars($sub['Class']); ?></span> • 
                                        <span><?php echo htmlspecialchars($sub['UserName']); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="submission-preview-text">
                                <?php 
                                $preview = $sub['submission_text'] ?? 'No text submitted';
                                echo htmlspecialchars(mb_strimwidth($preview, 0, 150, '...')); 
                                ?>
                            </div>

                            <div class="card-footer">
                                <span class="status-badge status-<?php echo strtolower($sub['display_status']); ?>">
                                    <?php echo htmlspecialchars($sub['display_status']); ?>
                                    <?php if ($sub['display_status'] === 'Late'): ?>
                                        <i class="fas fa-clock"></i>
                                    <?php endif; ?>
                                </span>
                                
                                <a href="grade_assignments.php?id=<?php echo $assignment_id; ?>&student=<?php echo $sub['student_id']; ?>" 
                                   class="grade-btn">
                                    <i class="fas fa-edit"></i> Grade Now
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <!-- All Graded State -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>All Submissions Graded! 🎉</h3>
                    <p>Excellent work! All submissions for this assignment have been graded.</p>
                    <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
                        <a href="view_submissions.php?id=<?php echo $assignment_id; ?>" class="btn btn-primary">
                            <i class="fas fa-eye"></i> View All Submissions
                        </a>
                        <a href="assignment_analytics.php?id=<?php echo $assignment_id; ?>" class="btn btn-secondary">
                            <i class="fas fa-chart-bar"></i> View Analytics
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Sync slider and input
        const scoreInput = document.getElementById('score');
        const scoreSlider = document.getElementById('scoreSlider');
        
        if (scoreInput && scoreSlider) {
            scoreInput.addEventListener('input', function() {
                scoreSlider.value = this.value;
                updateScoreColor(this.value);
            });
            
            scoreSlider.addEventListener('input', function() {
                scoreInput.value = this.value;
                updateScoreColor(this.value);
            });
            
            // Initial color update
            updateScoreColor(scoreInput.value || 0);
        }
        
        function updateScoreColor(score) {
            const maxScore = <?php echo $assignment['total_points']; ?>;
            const percentage = (score / maxScore) * 100;
            let color;
            
            if (percentage < 50) color = '#EF4444'; // Red
            else if (percentage < 70) color = '#F59E0B'; // Orange
            else if (percentage < 85) color = '#3B82F6'; // Blue
            else color = '#10B981'; // Green
            
            scoreInput.style.borderColor = color;
            scoreInput.style.color = color;
        }
        
        // Feedback templates
        function addTemplate(text) {
            const textarea = document.getElementById('feedback');
            const currentText = textarea.value;
            
            if (currentText && !currentText.endsWith('\n')) {
                textarea.value += '\n\n';
            }
            
            textarea.value += text;
            textarea.focus();
            
            // Animate the template button
            event.target.style.transform = 'scale(0.95)';
            setTimeout(() => {
                event.target.style.transform = '';
            }, 200);
        }
        
        // Form submission loading state
        const form = document.getElementById('gradeForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                submitBtn.innerHTML = '<div class="loading"></div> Grading...';
                submitBtn.disabled = true;
            });
        }
        
        // Animate cards on load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.submission-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.style.animation = 'fadeIn 0.6s ease-out forwards';
            });
            
            // Auto-expand textarea based on content
            const textarea = document.getElementById('feedback');
            if (textarea) {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
                // Trigger once on load
                textarea.dispatchEvent(new Event('input'));
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                // Ctrl+Enter to submit
                if (form) form.submit();
            } else if (e.key === 'Escape') {
                // Escape to go back
                window.history.back();
            }
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>