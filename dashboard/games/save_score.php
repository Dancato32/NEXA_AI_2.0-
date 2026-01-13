
<?php
// save_score.php
session_start();
require_once '../../includes/config.php';

// Add this at the very beginning of save_score.php, after session_start()
if (isset($_GET['test'])) {
    echo json_encode([
        'success' => true,
        'message' => 'save_score.php is accessible',
        'session_user_id' => $_SESSION['user_id'] ?? 'not set',
        'session_user_type' => $_SESSION['user_type'] ?? 'not set'
    ]);
    exit();
}

// Debug: Log all incoming data
error_log("=== SAVE SCORE REQUEST ===");
error_log("POST data: " . print_r($_POST, true));
error_log("SESSION data: " . print_r($_SESSION, true));

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    error_log("Unauthorized access attempt");
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get all POST data - FIXED: Check $_POST directly, not formData names
    $student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0);
    $game_name = isset($_POST['game_name']) ? trim($_POST['game_name']) : 'unknown';
    $score = isset($_POST['score']) ? (int)$_POST['score'] : 0;
    $level = isset($_POST['level']) ? (int)$_POST['level'] : 1;
    $problems_solved = isset($_POST['problems_solved']) ? (int)$_POST['problems_solved'] : 0;
    $accuracy = isset($_POST['accuracy']) ? (float)$_POST['accuracy'] : 0.0;
    $play_time = isset($_POST['play_time']) ? (int)$_POST['play_time'] : 0;
    
    error_log("Processing score for student $student_id, game: $game_name, score: $score");
    
    if ($student_id <= 0) {
        error_log("Invalid student ID: $student_id");
        echo json_encode(['success' => false, 'error' => 'Invalid student ID']);
        exit();
    }
    
    // Check if score is higher than previous best
    $check_sql = "SELECT score FROM game_scores 
                  WHERE student_id = ? AND game_name = ? 
                  ORDER BY score DESC LIMIT 1";
    
    error_log("Checking previous score with SQL: $check_sql");
    
    $stmt = mysqli_prepare($conn, $check_sql);
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($conn));
        echo json_encode(['success' => false, 'error' => 'Database prepare failed']);
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, 'is', $student_id, $game_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $previous = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    $previous_score = $previous ? (int)$previous['score'] : 0;
    $is_new_high = ($score > $previous_score);
    
    error_log("Previous score: $previous_score, New score: $score, Is new high: " . ($is_new_high ? 'YES' : 'NO'));
    
    // Always insert the score (track all attempts)
    $sql = "INSERT INTO game_scores 
            (student_id, game_name, score, level, problems_solved, accuracy, play_time, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    
    error_log("Inserting score with SQL: $sql");
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("Prepare failed for insert: " . mysqli_error($conn));
        echo json_encode(['success' => false, 'error' => 'Database prepare failed']);
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, 'isiiidi', 
        $student_id, $game_name, $score, $level, 
        $problems_solved, $accuracy, $play_time);
    
    if (mysqli_stmt_execute($stmt)) {
        $insert_id = mysqli_insert_id($conn);
        error_log("Score saved successfully with ID: $insert_id");
        
        // Also update user's stats in a separate table if you have one
        $update_stats_sql = "INSERT INTO user_game_stats 
                            (student_id, game_name, last_score, last_played, games_played) 
                            VALUES (?, ?, ?, NOW(), 1)
                            ON DUPLICATE KEY UPDATE 
                            last_score = VALUES(last_score),
                            last_played = VALUES(last_played),
                            games_played = games_played + 1";
        
        $stmt2 = mysqli_prepare($conn, $update_stats_sql);
        if ($stmt2) {
            mysqli_stmt_bind_param($stmt2, 'isi', $student_id, $game_name, $score);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);
            error_log("User stats updated");
        }
        
        mysqli_stmt_close($stmt);
        
        echo json_encode([
            'success' => true, 
            'is_new_high' => $is_new_high,
            'previous_score' => $previous_score,
            'new_score' => $score
        ]);
    } else {
        $error = mysqli_error($conn);
        error_log("Execute failed: " . $error);
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $error]);
    }
} else {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

// Close connection
mysqli_close($conn);
?>
