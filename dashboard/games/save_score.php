<?php
// save_score.php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)$_SESSION['user_id'];
    $game_name = 'math-blaster';
    $score = (int)$_POST['score'];
    $level = isset($_POST['level']) ? (int)$_POST['level'] : 1;
    $problems_solved = isset($_POST['problems_solved']) ? (int)$_POST['problems_solved'] : 0;
    $accuracy = isset($_POST['accuracy']) ? (float)$_POST['accuracy'] : 0.0;
    $play_time = isset($_POST['play_time']) ? (int)$_POST['play_time'] : 0;
    
    // Check if score is higher than previous best
    $check_sql = "SELECT score FROM game_scores 
                  WHERE student_id = ? AND game_name = ? 
                  ORDER BY score DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, 'is', $student_id, $game_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $previous = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    $is_new_high = false;
    
    // Only save if it's a new high score or first time playing
    if (!$previous || $score > $previous['score']) {
        $is_new_high = true;
        
        $sql = "INSERT INTO game_scores 
                (student_id, game_name, score, level, problems_solved, accuracy, play_time) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'isiiidi', 
            $student_id, $game_name, $score, $level, 
            $problems_solved, $accuracy, $play_time);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'is_new_high' => $is_new_high]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => true, 'is_new_high' => $is_new_high]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>