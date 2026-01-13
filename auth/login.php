<?php
// Define root path
define('ROOT_PATH', dirname(__DIR__));

// Start session and include functions
require_once ROOT_PATH . '/includes/sessions.php';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit();
}

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

try {
    // Get login type
    $login_type = isset($_POST['login_type']) ? sanitize_input($_POST['login_type']) : 'username';
    
    // Get credentials
    $identifier = isset($_POST['identifier']) ? sanitize_input($_POST['identifier']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate inputs
    if (empty($identifier) || empty($password)) {
        $response['message'] = 'Please enter both identifier and password';
        $response['errors']['identifier'] = empty($identifier) ? 'Required' : '';
        $response['errors']['password'] = empty($password) ? 'Required' : '';
    } else {
        // Try to authenticate user
        $user = null;
        $user_type = null;
        
        // Determine user type based on identifier
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            // Check if it's a parent or teacher email
            $parent = get_parent_by_email($identifier);
            if ($parent) {
                $user = $parent;
                $user_type = 'parent';
            } else {
                $teacher = get_teacher_by_email($identifier);
                if ($teacher) {
                    $user = $teacher;
                    $user_type = 'teacher';
                }
            }
        } else {
            // Check if it's a student username
            $student = get_student_by_username($identifier);
            if ($student) {
                $user = $student;
                $user_type = 'student';
            }
        }
        
        if ($user && $user_type) {
            // Verify password based on user type
            $authenticated = false;
            
            switch ($user_type) {
                case 'student':
                    $authenticated = verify_password($password, $user['Password']);
                    break;
                case 'parent':
                    $authenticated = verify_password($password, $user['password']);
                    break;
                case 'teacher':
                    $authenticated = verify_password($password, $user['Password']);
                    break;
            }
            
            if ($authenticated) {
                // Start user session
                start_user_session($user, $user_type);
                
                // Set remember me cookie (30 days)
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
                    // Store token in database (you'll need to add this functionality)
                }
                
                $response['success'] = true;
                $response['message'] = 'Login successful! Redirecting...';
                
                // Set redirect URL based on user type
                switch ($user_type) {
                    case 'student':
                        $response['redirect'] = '../dashboard/student_dashboard.php';
                        break;
                    case 'parent':
                        $response['redirect'] = '../dashboard/parent_dashboard.php';
                        break;
                    case 'teacher':
                        $response['redirect'] = '../dashboard/teacher_dashboard.php';
                        break;
                }
            } else {
                $response['message'] = 'Invalid password';
                $response['errors']['password'] = 'Incorrect password';
            }
        } else {
            $response['message'] = 'User not found';
            $response['errors']['identifier'] = 'No account found with this identifier';
        }
    }
    
} catch (Exception $e) {
    $response['message'] = 'Login failed: ' . $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>