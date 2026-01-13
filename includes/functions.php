<?php
// Include configuration
require_once 'config.php';

/**
 * Sanitize user input
 */
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

/**
 * Hash password
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

/**
 * Redirect user to their dashboard based on type
 */
function redirect_by_user_type() {
    if (is_logged_in()) {
        $user_type = $_SESSION['user_type'];
        switch ($user_type) {
            case 'student':
                header('Location: ../dashboard/student-dashboard.php');
                break;
            case 'parent':
                header('Location: ../dashboard/parent-dashboard.php');
                break;
            case 'teacher':
                header('Location: ../dashboard/teacher-dashboard.php');
                break;
            default:
                header('Location: ../index.html');
        }
        exit();
    }
}

/**
 * Check if student username exists
 */
function student_username_exists($username) {
    global $conn;
    
    $sql = "SELECT id FROM student_details WHERE UserName = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    
    return $exists;
}

/**
 * Check if parent email exists
 */
function parent_email_exists($email) {
    global $conn;
    
    $sql = "SELECT id FROM parents_details WHERE emailAddress = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    
    return $exists;
}

/**
 * Check if teacher email exists
 */
function teacher_email_exists($email) {
    global $conn;
    
    $sql = "SELECT id FROM teachers_details WHERE emailAddress = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    
    return $exists;
}

/**
 * Check if child username exists (for parent registration)
 */
function child_username_exists($child_username) {
    global $conn;
    
    $sql = "SELECT id FROM student_details WHERE UserName = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $child_username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    
    return $exists;
}

/**
 * Get student by username
 */
function get_student_by_username($username) {
    global $conn;
    
    $sql = "SELECT * FROM student_details WHERE UserName = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $student = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $student;
}

/**
 * Get parent by email
 */
function get_parent_by_email($email) {
    global $conn;
    
    $sql = "SELECT * FROM parents_details WHERE emailAddress = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $parent = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $parent;
}

/**
 * Get teacher by email
 */
function get_teacher_by_email($email) {
    global $conn;
    
    $sql = "SELECT * FROM teachers_details WHERE emailAddress = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $teacher = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $teacher;
}

/**
 * Validate email format
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Register a new student
 */
function register_student($data) {
    global $conn;
    
    $first_name = sanitize_input($data['first_name']);
    $last_name = sanitize_input($data['last_name']);
    $username = sanitize_input($data['username']);
    $gender = sanitize_input($data['gender']);
    $class = sanitize_input($data['class']);
    $password = hash_password($data['password']);
    
    $sql = "INSERT INTO student_details (firstName, lastName, UserName, Gender, Class, Password) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssssss', $first_name, $last_name, $username, $gender, $class, $password);
    
    if (mysqli_stmt_execute($stmt)) {
        $student_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        return $student_id;
    } else {
        $error = mysqli_error($conn);
        error_log("Student registration error: " . $error);
        mysqli_stmt_close($stmt);
        return false;
    }
}

/**
 * Register a new parent
 */
function register_parent($data) {
    global $conn;
    
    $first_name = sanitize_input($data['first_name']);
    $last_name = sanitize_input($data['last_name']);
    $email = sanitize_input($data['email']);
    $child_username = sanitize_input($data['child_username']);
    $password = hash_password($data['password']);
    
    $sql = "INSERT INTO parents_details (firstName, lastName, emailAddress, child_Username, password) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sssss', $first_name, $last_name, $email, $child_username, $password);
    
    if (mysqli_stmt_execute($stmt)) {
        $parent_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        return $parent_id;
    } else {
        $error = mysqli_error($conn);
        error_log("Parent registration error: " . $error);
        mysqli_stmt_close($stmt);
        return false;
    }
}

/**
 * Register a new teacher
 */
function register_teacher($data) {
    global $conn;
    
    $first_name = sanitize_input($data['first_name']);
    $last_name = sanitize_input($data['last_name']);
    $email = sanitize_input($data['email']);
    $subject = sanitize_input($data['subject']);
    $school = isset($data['school']) ? sanitize_input($data['school']) : '';
    $password = hash_password($data['password']);
    
    $sql = "INSERT INTO teachers_details (firstName, lastName, emailAddress, Subject, schoolName, Password) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssssss', $first_name, $last_name, $email, $subject, $school, $password);
    
    if (mysqli_stmt_execute($stmt)) {
        $teacher_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        return $teacher_id;
    } else {
        $error = mysqli_error($conn);
        error_log("Teacher registration error: " . $error);
        mysqli_stmt_close($stmt);
        return false;
    }
}

/**
 * Authenticate student
 */
function authenticate_student($username, $password) {
    $student = get_student_by_username($username);
    
    if ($student && verify_password($password, $student['Password'])) {
        return $student;
    }
    
    return false;
}

/**
 * Authenticate parent
 */
function authenticate_parent($email, $password) {
    $parent = get_parent_by_email($email);
    
    if ($parent && verify_password($password, $parent['password'])) {
        return $parent;
    }
    
    return false;
}

/**
 * Authenticate teacher
 */
function authenticate_teacher($email, $password) {
    $teacher = get_teacher_by_email($email);
    
    if ($teacher && verify_password($password, $teacher['Password'])) {
        return $teacher;
    }
    
    return false;
}

/**
 * Start user session
 */
function start_user_session($user, $user_type) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_type'] = $user_type;
    
    if ($user_type === 'student') {
        $_SESSION['first_name'] = $user['firstName'];
        $_SESSION['last_name'] = $user['lastName'];
        $_SESSION['username'] = $user['UserName'];
        $_SESSION['class'] = $user['Class'];
        $_SESSION['gender'] = $user['Gender'];
    } elseif ($user_type === 'parent') {
        $_SESSION['first_name'] = $user['firstName'];
        $_SESSION['last_name'] = $user['lastName'];
        $_SESSION['email'] = $user['emailAddress'];
        $_SESSION['child_username'] = $user['child_Username'];
    } elseif ($user_type === 'teacher') {
        $_SESSION['first_name'] = $user['firstName'];
        $_SESSION['last_name'] = $user['lastName'];
        $_SESSION['email'] = $user['emailAddress'];
        $_SESSION['subject'] = $user['Subject'];
        $_SESSION['school'] = $user['schoolName'];
    }
    
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
}

/**
 * Logout user
 */
function logout_user() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header('Location: ../frontend/login.php');
    exit();
}

/**
 * JSON response helper
 */
function json_response($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}
?>