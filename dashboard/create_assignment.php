
<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in as teacher
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../Frontend/login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $class = isset($_POST['class']) ? trim($_POST['class']) : '';
    $due_date = isset($_POST['due_date']) ? trim($_POST['due_date']) : '';
    $due_time = isset($_POST['due_time']) ? trim($_POST['due_time']) : '';
    $total_points = isset($_POST['total_points']) ? (int)$_POST['total_points'] : 100;
    
    // If class is "other", use the other_class field
    if ($class === 'other') {
        $class = isset($_POST['other_class']) ? trim($_POST['other_class']) : '';
    }
    
    // Validate inputs
    if (empty($title) || empty($description) || empty($subject) || empty($class) || empty($due_date) || empty($due_time)) {
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        exit();
    }
    
    // Check if teacher is allowed to teach this class
    $class_check_query = "SELECT 1 FROM teacher_class_relationship 
                         WHERE teacher_id = ? AND class_name = ?";
    $stmt_check = mysqli_prepare($conn, $class_check_query);
    mysqli_stmt_bind_param($stmt_check, 'is', $teacher_id, $class);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    
    if (mysqli_stmt_num_rows($stmt_check) === 0) {
        echo json_encode(['success' => false, 'error' => 'You are not assigned to teach this class']);
        mysqli_stmt_close($stmt_check);
        exit();
    }
    mysqli_stmt_close($stmt_check);
    
    // Combine date and time
    $full_due_date = $due_date . ' ' . $due_time . ':00';
    
    // Insert assignment
    $sql = "INSERT INTO assignments 
            (teacher_id, title, description, subject, class, due_date, total_points, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($conn, $sql);
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
        
        echo json_encode([
            'success' => true, 
            'message' => 'Assignment created successfully',
            'assignment_id' => $assignment_id
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($conn)]);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
