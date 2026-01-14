
<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in as student
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../Frontend/login.php');
    exit();
}

$student_id = $_SESSION['user_id'];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = isset($_POST['assignment_id']) ? (int)$_POST['assignment_id'] : 0;
    $submission_text = isset($_POST['submission_text']) ? trim($_POST['submission_text']) : '';
    
    if ($assignment_id <= 0 || empty($submission_text)) {
        echo json_encode(['success' => false, 'error' => 'Invalid submission data']);
        exit();
    }
    
    // Handle file upload if any
    $attachment_url = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/assignments/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['attachment']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Validate file type and size
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $max_size = 10 * 1024 * 1024; // 10MB
        
        if (in_array($file_type, $allowed_types) && $_FILES['attachment']['size'] <= $max_size) {
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
                $attachment_url = 'uploads/assignments/' . $file_name;
            }
        }
    }
    
    // Check if assignment exists and is not past due
    $assignment_check = "SELECT due_date FROM assignments WHERE id = ?";
    $stmt = mysqli_prepare($conn, $assignment_check);
    mysqli_stmt_bind_param($stmt, 'i', $assignment_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) === 0) {
        echo json_encode(['success' => false, 'error' => 'Assignment not found']);
        exit();
    }
    
    mysqli_stmt_bind_result($stmt, $due_date);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    
    // Determine status
    $status = 'submitted';
    $current_time = date('Y-m-d H:i:s');
    if (strtotime($current_time) > strtotime($due_date)) {
        $status = 'late';
    }
    
    // Insert or update submission
    $sql = "INSERT INTO assignment_submissions 
            (assignment_id, student_id, submission_text, attachment_url, submitted_at, status) 
            VALUES (?, ?, ?, ?, NOW(), ?)
            ON DUPLICATE KEY UPDATE 
            submission_text = VALUES(submission_text),
            attachment_url = VALUES(attachment_url),
            submitted_at = NOW(),
            status = VALUES(status)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'issss', $assignment_id, $student_id, $submission_text, $attachment_url, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Assignment submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($conn)]);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
