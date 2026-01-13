<?php
// Define root path
define('ROOT_PATH', dirname(__DIR__));

// Start session and include functions
require_once ROOT_PATH . '/includes/sessions.php';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../signup.php');
    exit();
}

// Get user type
$user_type = isset($_POST['user_type']) ? sanitize_input($_POST['user_type']) : 'student';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

try {
    // Validate user type
    if (!in_array($user_type, ['student', 'parent', 'teacher'])) {
        throw new Exception('Invalid user type');
    }

    // Process based on user type
    switch ($user_type) {
        case 'student':
            $response = register_student_process();
            break;
        case 'parent':
            $response = register_parent_process();
            break;
        case 'teacher':
            $response = register_teacher_process();
            break;
    }

} catch (Exception $e) {
    $response['message'] = 'Registration failed: ' . $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();

/**
 * Process student registration
 */
function register_student_process() {
    global $conn;
    $errors = [];
    
    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'username', 'gender', 'class', 'password', 'confirm_password'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => 'Please fill all required fields',
            'errors' => $errors
        ];
    }
    
    // Get form data
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $username = sanitize_input($_POST['username']);
    $gender = sanitize_input($_POST['gender']);
    $class = sanitize_input($_POST['class']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate username
    if (strlen($username) < 3) {
        $errors['username'] = 'Username must be at least 3 characters';
    }
    
    if (student_username_exists($username)) {
        $errors['username'] = 'Username already exists';
    }
    
    // Validate password
    if (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => 'Please correct the errors',
            'errors' => $errors
        ];
    }
    
    // Register student
    $student_data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'username' => $username,
        'gender' => $gender,
        'class' => $class,
        'password' => $password
    ];
    
    $student_id = register_student($student_data);
    
    if ($student_id) {
        // Get student data and start session
        $student = get_student_by_username($username);
        start_user_session($student, 'student');
        
        return [
            'success' => true,
            'message' => 'Registration successful! Redirecting...',
            'redirect' => '../dashboard/student_dashboard.php'
        ];
    } else {
        $error_msg = mysqli_error($conn) ?: 'Database error occurred';
        return [
            'success' => false,
            'message' => 'Registration failed. Please try again.',
            'errors' => ['general' => $error_msg]
        ];
    }
}

/**
 * Process parent registration
 */
function register_parent_process() {
    global $conn;
    $errors = [];
    
    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'email', 'child_username', 'password', 'confirm_password'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => 'Please fill all required fields',
            'errors' => $errors
        ];
    }
    
    // Get form data
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $child_username = sanitize_input($_POST['child_username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate email
    if (!validate_email($email)) {
        $errors['email'] = 'Invalid email address';
    }
    
    if (parent_email_exists($email)) {
        $errors['email'] = 'Email already registered';
    }
    
    // Validate child username
    if (!child_username_exists($child_username)) {
        $errors['child_username'] = 'Child username does not exist';
    }
    
    // Validate password
    if (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => 'Please correct the errors',
            'errors' => $errors
        ];
    }
    
    // Register parent
    $parent_data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'child_username' => $child_username,
        'password' => $password
    ];
    
    $parent_id = register_parent($parent_data);
    
    if ($parent_id) {
        // Get parent data and start session
        $parent = get_parent_by_email($email);
        start_user_session($parent, 'parent');
        
        return [
            'success' => true,
            'message' => 'Registration successful! Redirecting...',
            'redirect' => '../dashboard/parent_dashboard.php'
        ];
    } else {
        $error_msg = mysqli_error($conn) ?: 'Database error occurred';
        return [
            'success' => false,
            'message' => 'Registration failed. Please try again.',
            'errors' => ['general' => $error_msg]
        ];
    }
}

/**
 * Process teacher registration
 */
function register_teacher_process() {
    global $conn;
    $errors = [];
    
    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'email', 'subject', 'password', 'confirm_password'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => 'Please fill all required fields',
            'errors' => $errors
        ];
    }
    
    // Get form data
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $subject = sanitize_input($_POST['subject']);
    $school = isset($_POST['school']) ? sanitize_input($_POST['school']) : '';
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate email
    if (!validate_email($email)) {
        $errors['email'] = 'Invalid email address';
    }
    
    if (teacher_email_exists($email)) {
        $errors['email'] = 'Email already registered';
    }
    
    // Validate password
    if (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => 'Please correct the errors',
            'errors' => $errors
        ];
    }
    
    // Register teacher
    $teacher_data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'subject' => $subject,
        'school' => $school,
        'password' => $password
    ];
    
    $teacher_id = register_teacher($teacher_data);
    
    if ($teacher_id) {
        // Get teacher data and start session
        $teacher = get_teacher_by_email($email);
        start_user_session($teacher, 'teacher');
        
        return [
            'success' => true,
            'message' => 'Registration successful! Redirecting...',
            'redirect' => '../dashboard/teacher_dashboard.php'
        ];
    } else {
        $error_msg = mysqli_error($conn) ?: 'Database error occurred';
        return [
            'success' => false,
            'message' => 'Registration failed. Please try again.',
            'errors' => ['general' => $error_msg]
        ];
    }
}
?>