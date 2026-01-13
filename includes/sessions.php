<?php
// Set session cookie parameters BEFORE starting session
$cookieParams = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => $cookieParams["lifetime"],
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Now start the session
session_start();

// Regenerate session ID to prevent fixation
if (!isset($ESSI_SON['initiated'])) {
    session_regenerate_id();
    $_SESSION['initiated'] = true;
}

// Include functions with correct path
$functionsPath = __DIR__ . '/functions.php';
if (file_exists($functionsPath)) {
    require_once $functionsPath;
} else {
    die("Error: Cannot find functions.php at: " . $functionsPath);
}

// Check for timeout (30 minutes)
if (isset($_SESSION['logged_in']) && isset($_SESSION['login_time'])) {
    $inactive = 1800; // 30 minutes
    $session_life = time() - $_SESSION['login_time'];
    
    if ($session_life > $inactive) {
        $_SESSION = array();
        session_destroy();
        header('Location: ../Frontend/login.php');
        exit();
    }
}

// Update last activity
$_SESSION['last_activity'] = time();
?>