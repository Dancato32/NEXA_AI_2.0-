<?php
    session_start();
    
    // Check if user is logged in as student
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
        header('Location: ../Frontend/login.php');
        exit();
    }

    // Get student information
    $student_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    $student_class = $_SESSION['class'] ?? 'Not specified';
    $student_username = $_SESSION['username'];
    $student_id = $_SESSION['user_id'];
    
    // Database connection
    require_once '../../includes/config.php';
    
    // Get all students for leaderboard
    $leaderboard_query = "SELECT s.id, s.UserName, s.firstName, s.lastName, 
                                  COALESCE(MAX(g.score), 0) as highScore
                           FROM student_details s
                           LEFT JOIN game_scores g ON s.id = g.student_id AND g.game_name = 'math-blaster'
                           GROUP BY s.id, s.UserName, s.firstName, s.lastName
                           ORDER BY highScore DESC, s.firstName ASC";
    $leaderboard_result = mysqli_query($conn, $leaderboard_query);
    $all_students = [];
    
    if ($leaderboard_result) {
        while ($row = mysqli_fetch_assoc($leaderboard_result)) {
            $all_students[] = $row;
        }
    }
    
    // Get current student's high score
    $current_score_query = "SELECT MAX(score) as highScore FROM game_scores 
                            WHERE student_id = ? AND game_name = 'math-blaster'";
    $stmt = mysqli_prepare($conn, $current_score_query);
    mysqli_stmt_bind_param($stmt, 'i', $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $current_score_data = mysqli_fetch_assoc($result);
    $current_high_score = $current_score_data['highScore'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Math Blaster • Nexa Games</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --primary-dark: #1d4ed8;
            --secondary: #10b981;
            --accent: #f59e0b;
            --danger: #ef4444;
            --purple: #8b5cf6;
            --pink: #ec4899;
            --cyan: #06b6d4;
            --dark: #111827;
            --gray-900: #1f2937;
            --gray-800: #374151;
            --gray-700: #4b5563;
            --gray-600: #6b7280;
            --gray-500: #9ca3af;
            --gray-400: #d1d5db;
            --gray-300: #e5e7eb;
            --gray-200: #f3f4f6;
            --gray-100: #f9fafb;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 6px 12px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 25px -3px rgb(0 0 0 / 0.1);
            --radius-sm: 0.375rem;
            --radius: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: var(--white);
            line-height: 1.5;
            height: 100vh;
            overflow: hidden;
        }

        /* Game Container */
        .game-container {
            display: grid;
            grid-template-rows: 72px 1fr;
            height: 100vh;
            max-width: 1600px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        /* Game Header */
        .game-header {
            background: rgba(15, 23, 42, 0.95);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 50;
            backdrop-filter: blur(10px);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--white);
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--cyan));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius);
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-4px);
        }

        /* Game Stats Bar */
        .stats-bar {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius);
        }

        .stat-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
        }

        .stat-value {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .stat-label {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Game Content */
        .game-content {
            display: grid;
            grid-template-columns: 300px 1fr 300px;
            gap: 0;
            height: 100%;
            position: relative;
        }

        /* Game Canvas */
        .game-canvas-container {
            position: relative;
            background: linear-gradient(135deg, #0c4a6e 0%, #1e40af 100%);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Stars Background */
        .stars {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
        }

        .star {
            position: absolute;
            background: white;
            border-radius: 50%;
            animation: twinkle 2s infinite alternate;
        }

        @keyframes twinkle {
            0% { opacity: 0.3; }
            100% { opacity: 1; }
        }

        /* Game Area */
        .game-area {
            width: 100%;
            height: 100%;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Spaceship */
        .spaceship {
            position: absolute;
            bottom: 20%;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--primary), var(--cyan));
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
            transition: var(--transition);
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }

        .spaceship::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 30px;
            background: linear-gradient(135deg, var(--accent), #fbbf24);
            clip-path: polygon(20% 0%, 80% 0%, 100% 100%, 0% 100%);
        }

        /* Math Problems */
        .math-problem {
            position: absolute;
            top: 10%;
            background: rgba(255, 255, 255, 0.95);
            padding: 20px 30px;
            border-radius: var(--radius-lg);
            border: 2px solid var(--primary);
            box-shadow: var(--shadow-lg);
            text-align: center;
            z-index: 30;
            min-width: 300px;
        }

        .problem-text {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 15px;
        }

        .answer-input {
            width: 200px;
            padding: 12px 20px;
            font-size: 1.5rem;
            text-align: center;
            border: 2px solid var(--gray-300);
            border-radius: var(--radius);
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 600;
            transition: var(--transition);
            outline: none;
        }

        .answer-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Falling Asteroids */
        .asteroid {
            position: absolute;
            top: -100px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #6b7280, #374151);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            box-shadow: inset -5px -5px 10px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            transition: var(--transition);
            z-index: 10;
            user-select: none;
        }

        .asteroid:hover {
            transform: scale(1.1);
        }

        .asteroid.correct {
            background: linear-gradient(135deg, var(--secondary), #059669);
        }

        .asteroid.wrong {
            background: linear-gradient(135deg, var(--danger), #dc2626);
        }

        /* Power-ups */
        .power-up {
            position: absolute;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            box-shadow: var(--shadow);
            animation: float 3s infinite ease-in-out;
            cursor: pointer;
            z-index: 15;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .power-up.time {
            background: linear-gradient(135deg, var(--accent), #fbbf24);
        }

        .power-up.score {
            background: linear-gradient(135deg, var(--purple), #7c3aed);
        }

        .power-up.shield {
            background: linear-gradient(135deg, var(--cyan), #0891b2);
        }

        /* Side Panels */
        .side-panel {
            background: rgba(15, 23, 42, 0.95);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding: 24px;
            display: flex;
            flex-direction: column;
            backdrop-filter: blur(10px);
        }

        .side-panel.right {
            border-right: none;
            border-left: 1px solid rgba(255, 255, 255, 0.1);
        }

        .panel-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-title i {
            color: var(--primary);
        }

        /* Instructions */
        .instructions-list {
            list-style: none;
            margin-bottom: 30px;
        }

        .instruction-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .instruction-item i {
            color: var(--secondary);
            margin-top: 2px;
        }

        /* Power-ups List */
        .power-ups-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .power-up-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }

        .power-up-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(4px);
        }

        .power-up-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .power-up-info h4 {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .power-up-info p {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Leaderboard */
        .leaderboard {
            margin-top: 20px;
        }

        .leaderboard-list {
            list-style: none;
            max-height: 300px;
            overflow-y: auto;
        }

        .leaderboard-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            margin-bottom: 8px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--radius);
            transition: var(--transition);
        }

        .leaderboard-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .leaderboard-item.current-user {
            background: rgba(37, 99, 235, 0.2);
            border: 1px solid rgba(37, 99, 235, 0.3);
        }

        .rank {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .rank-1 { background: linear-gradient(135deg, var(--accent), #d97706); }
        .rank-2 { background: linear-gradient(135deg, var(--gray-600), var(--gray-700)); }
        .rank-3 { background: linear-gradient(135deg, #92400e, #78350f); }

        .player-name {
            flex: 1;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .player-you {
            font-size: 0.7rem;
            background: var(--primary);
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
        }

        .player-score {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            color: var(--accent);
        }

        /* Challenge Button */
        .challenge-btn {
            margin-top: 15px;
            padding: 10px 16px;
            background: linear-gradient(135deg, var(--secondary), #059669);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
        }

        .challenge-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .challenge-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        /* Challenge Modal */
        .challenge-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .challenge-modal.active {
            display: flex;
        }

        .challenge-content {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: var(--radius-lg);
            max-width: 500px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .challenge-header {
            padding: 32px 32px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .challenge-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--secondary), #10b981);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .challenge-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
        }

        .challenge-body {
            padding: 32px;
        }

        .challenge-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--radius);
            margin-bottom: 24px;
        }

        .challenger-info {
            text-align: center;
            flex: 1;
        }

        .challenger-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .challenger-score {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent);
        }

        .vs {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            padding: 0 20px;
        }

        .challenge-footer {
            padding: 24px 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            gap: 16px;
        }

        .challenge-btn-modal {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .challenge-btn-modal.cancel {
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .challenge-btn-modal.cancel:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .challenge-btn-modal.accept {
            background: linear-gradient(135deg, var(--secondary), #059669);
            color: var(--white);
            border: none;
        }

        .challenge-btn-modal.accept:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Controls */
        .controls {
            display: flex;
            gap: 12px;
            margin-top: auto;
        }

        .control-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .pause-btn {
            background: rgba(245, 158, 11, 0.2);
            color: var(--accent);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .pause-btn:hover {
            background: rgba(245, 158, 11, 0.3);
        }

        .restart-btn {
            background: rgba(37, 99, 235, 0.2);
            color: var(--primary-light);
            border: 1px solid rgba(37, 99, 235, 0.3);
        }

        .restart-btn:hover {
            background: rgba(37, 99, 235, 0.3);
        }

        /* Game Status */
        .game-status {
            margin-top: 20px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .level-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .level-text {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .level-number {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            color: var(--primary-light);
            font-size: 1.2rem;
        }

        .progress-bar {
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--cyan));
            transition: width 0.3s ease;
        }

        /* Effects */
        .explosion {
            position: absolute;
            width: 100px;
            height: 100px;
            pointer-events: none;
            z-index: 100;
        }

        .particle {
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--accent);
            animation: explode 0.5s forwards;
        }

        @keyframes explode {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            100% {
                transform: translate(var(--tx), var(--ty)) scale(0);
                opacity: 0;
            }
        }

        /* Game Over Modal */
        .game-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .game-modal.active {
            display: flex;
        }

        .modal-content {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: var(--radius-lg);
            max-width: 500px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            padding: 32px 32px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--primary), var(--cyan));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .modal-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
        }

        .modal-body {
            padding: 32px;
        }

        .result-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .result-stat {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .result-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--white);
        }

        .result-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .new-record {
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.1));
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: var(--radius);
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }

        .new-record i {
            color: var(--accent);
            margin-right: 8px;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .modal-footer {
            padding: 24px 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            gap: 16px;
        }

        .modal-btn {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .modal-btn.secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-btn.secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .modal-btn.primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border: none;
        }

        .modal-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .game-content {
                grid-template-columns: 250px 1fr 250px;
            }
        }

        @media (max-width: 992px) {
            .game-content {
                grid-template-columns: 1fr;
                grid-template-rows: auto 1fr auto;
            }
            
            .side-panel {
                display: none;
            }
            
            .controls {
                position: fixed;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                width: 90%;
                max-width: 400px;
                z-index: 100;
            }
        }

        @media (max-width: 768px) {
            .game-header {
                padding: 0 16px;
            }
            
            .stats-bar {
                gap: 12px;
            }
            
            .stat-item {
                padding: 6px 12px;
            }
            
            .math-problem {
                min-width: 250px;
                padding: 15px 20px;
            }
            
            .problem-text {
                font-size: 1.5rem;
            }
            
            .answer-input {
                width: 150px;
                font-size: 1.2rem;
            }
        }

        @media (max-width: 480px) {
            .logo span {
                display: none;
            }
            
            .game-area {
                padding: 10px;
            }
            
            .spaceship {
                width: 80px;
                height: 80px;
                font-size: 1.5rem;
            }
            
            .asteroid {
                width: 50px;
                height: 50px;
                font-size: 0.9rem;
            }
            
            .modal-body {
                padding: 20px;
            }
            
            .result-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="game-container">
        <!-- Game Header -->
        <header class="game-header">
            <div class="header-left">
                <a href="games.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Games
                </a>
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <span>Math Blaster</span>
                </div>
            </div>
            
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div>
                        <div class="stat-value" id="score">0</div>
                        <div class="stat-label">Score</div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="stat-value" id="time">60</div>
                        <div class="stat-label">Time</div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div>
                        <div class="stat-value" id="lives">3</div>
                        <div class="stat-label">Lives</div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <div class="stat-value" id="level">1</div>
                        <div class="stat-label">Level</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Game Content -->
        <div class="game-content">
            <!-- Left Panel -->
            <div class="side-panel">
                <h3 class="panel-title">
                    <i class="fas fa-gamepad"></i>
                    How to Play
                </h3>
                
                <ul class="instructions-list">
                    <li class="instruction-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Solve math problems on falling asteroids</span>
                    </li>
                    <li class="instruction-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Type the answer and press Enter</span>
                    </li>
                    <li class="instruction-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Click correct asteroids to destroy them</span>
                    </li>
                    <li class="instruction-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Avoid wrong answers to keep your lives</span>
                    </li>
                    <li class="instruction-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Collect power-ups for bonuses</span>
                    </li>
                </ul>
                
                <h3 class="panel-title">
                    <i class="fas fa-bolt"></i>
                    Power-ups
                </h3>
                
                <div class="power-ups-list">
                    <div class="power-up-item">
                        <div class="power-up-icon" style="background: linear-gradient(135deg, var(--accent), #fbbf24);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="power-up-info">
                            <h4>Time Boost</h4>
                            <p>+10 seconds</p>
                        </div>
                    </div>
                    
                    <div class="power-up-item">
                        <div class="power-up-icon" style="background: linear-gradient(135deg, var(--purple), #7c3aed);">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="power-up-info">
                            <h4>Score Multiplier</h4>
                            <p>2x points for 15s</p>
                        </div>
                    </div>
                    
                    <div class="power-up-item">
                        <div class="power-up-icon" style="background: linear-gradient(135deg, var(--cyan), #0891b2);">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="power-up-info">
                            <h4>Shield</h4>
                            <p>Extra protection</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Game Canvas -->
            <div class="game-canvas-container">
                <!-- Stars Background -->
                <div class="stars" id="stars"></div>
                
                <!-- Game Area -->
                <div class="game-area" id="gameArea">
                    <!-- Math Problem -->
                    <div class="math-problem" id="mathProblem">
                        <div class="problem-text" id="problemText">5 + 3 = ?</div>
                        <input type="text" class="answer-input" id="answerInput" placeholder="Enter answer" maxlength="3" autocomplete="off">
                    </div>
                    
                    <!-- Spaceship -->
                    <div class="spaceship" id="spaceship">
                        <i class="fas fa-rocket"></i>
                    </div>
                </div>
            </div>

            <!-- Right Panel -->
            <div class="side-panel right">
                <h3 class="panel-title">
                    <i class="fas fa-trophy"></i>
                    Class Leaderboard
                </h3>
                
                <div class="leaderboard">
                    <ul class="leaderboard-list" id="leaderboard">
                        <!-- Leaderboard will be loaded here -->
                    </ul>
                    
                    <button class="challenge-btn" id="challengeBtn">
                        <i class="fas fa-fist-raised"></i>
                        Challenge Top Player
                    </button>
                </div>
                
                <div class="game-status">
                    <div class="level-info">
                        <span class="level-text">Progress</span>
                        <span class="level-number" id="levelProgress">0/10</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill" style="width: 0%"></div>
                    </div>
                </div>
                
                <div class="controls">
                    <button class="control-btn pause-btn" id="pauseBtn">
                        <i class="fas fa-pause"></i>
                        Pause
                    </button>
                    <button class="control-btn restart-btn" id="restartBtn">
                        <i class="fas fa-redo"></i>
                        Restart
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Game Over Modal -->
    <div class="game-modal" id="gameOverModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Mission Complete!</h2>
                <p class="modal-subtitle">Great job, Space Cadet!</p>
            </div>
            
            <div class="modal-body">
                <div id="newRecordMessage" class="new-record" style="display: none;">
                    <i class="fas fa-trophy"></i>
                    <strong>New High Score!</strong> You beat your personal best!
                </div>
                
                <div class="result-stats">
                    <div class="result-stat">
                        <div class="result-value" id="finalScore">0</div>
                        <div class="result-label">Final Score</div>
                    </div>
                    
                    <div class="result-stat">
                        <div class="result-value" id="finalLevel">1</div>
                        <div class="result-label">Level Reached</div>
                    </div>
                    
                    <div class="result-stat">
                        <div class="result-value" id="problemsSolved">0</div>
                        <div class="result-label">Problems Solved</div>
                    </div>
                    
                    <div class="result-stat">
                        <div class="result-value" id="accuracy">0%</div>
                        <div class="result-label">Accuracy</div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="modal-btn secondary" id="menuBtn">
                    <i class="fas fa-home"></i>
                    Back to Menu
                </button>
                <button class="modal-btn primary" id="playAgainBtn">
                    <i class="fas fa-redo"></i>
                    Play Again
                </button>
            </div>
        </div>
    </div>

    <!-- Challenge Modal -->
    <div class="challenge-modal" id="challengeModal">
        <div class="challenge-content">
            <div class="challenge-header">
                <h2 class="challenge-title">Challenge Accepted!</h2>
                <p class="challenge-subtitle">Can you beat the top score?</p>
            </div>
            
            <div class="challenge-body">
                <div class="challenge-info">
                    <div class="challenger-info">
                        <div class="challenger-name" id="challengerName">Daniel Cato</div>
                        <div class="challenger-score" id="challengerScore">2,450</div>
                    </div>
                    
                    <div class="vs">VS</div>
                    
                    <div class="challenger-info">
                        <div class="challenger-name"><?php echo htmlspecialchars($_SESSION['first_name']); ?></div>
                        <div class="challenger-score" id="currentScore">0</div>
                    </div>
                </div>
                
                <p style="text-align: center; color: rgba(255, 255, 255, 0.8); font-size: 0.9rem;">
                    Beat <span id="targetScore">2,450</span> points to win the challenge!
                </p>
            </div>
            
            <div class="challenge-footer">
                <button class="challenge-btn-modal cancel" id="cancelChallengeBtn">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
                <button class="challenge-btn-modal accept" id="acceptChallengeBtn">
                    <i class="fas fa-play"></i>
                    Start Challenge
                </button>
            </div>
        </div>
    </div>

    <script>
        // Game Variables
        let score = 0;
        let timeLeft = 60;
        let lives = 3;
        let level = 1;
        let problemsSolved = 0;
        let totalProblems = 0;
        let correctProblems = 0;
        let gameActive = false;
        let gameTimer;
        let asteroidInterval;
        let powerUpInterval;
        let currentProblem = null;
        let multiplier = 1;
        let shieldActive = false;
        let progress = 0;
        const maxProgress = 10;
        let isChallengeMode = false;
        let challengeTargetScore = 0;
        let challengeTargetName = '';

        // DOM Elements
        const scoreEl = document.getElementById('score');
        const timeEl = document.getElementById('time');
        const livesEl = document.getElementById('lives');
        const levelEl = document.getElementById('level');
        const levelProgressEl = document.getElementById('levelProgress');
        const progressFillEl = document.getElementById('progressFill');
        const problemTextEl = document.getElementById('problemText');
        const answerInputEl = document.getElementById('answerInput');
        const spaceshipEl = document.getElementById('spaceship');
        const gameAreaEl = document.getElementById('gameArea');
        const starsEl = document.getElementById('stars');
        const pauseBtn = document.getElementById('pauseBtn');
        const restartBtn = document.getElementById('restartBtn');
        const gameOverModal = document.getElementById('gameOverModal');
        const finalScoreEl = document.getElementById('finalScore');
        const finalLevelEl = document.getElementById('finalLevel');
        const problemsSolvedEl = document.getElementById('problemsSolved');
        const accuracyEl = document.getElementById('accuracy');
        const menuBtn = document.getElementById('menuBtn');
        const playAgainBtn = document.getElementById('playAgainBtn');
        const challengeBtn = document.getElementById('challengeBtn');
        const challengeModal = document.getElementById('challengeModal');
        const challengerNameEl = document.getElementById('challengerName');
        const challengerScoreEl = document.getElementById('challengerScore');
        const currentScoreEl = document.getElementById('currentScore');
        const targetScoreEl = document.getElementById('targetScore');
        const cancelChallengeBtn = document.getElementById('cancelChallengeBtn');
        const acceptChallengeBtn = document.getElementById('acceptChallengeBtn');
        const newRecordMessage = document.getElementById('newRecordMessage');
        const leaderboardEl = document.getElementById('leaderboard');

        // Student data from PHP
        const allStudents = <?php echo json_encode($all_students); ?>;
        const currentStudent = {
            id: <?php echo $student_id; ?>,
            username: '<?php echo $student_username; ?>',
            name: '<?php echo $student_name; ?>',
            first_name: '<?php echo $_SESSION["first_name"]; ?>',
            current_high_score: <?php echo $current_high_score; ?>
        };

        // Math Problems by Level
        const mathProblems = {
            1: [
                { problem: "5 + 3", answer: "8" },
                { problem: "7 - 2", answer: "5" },
                { problem: "4 × 2", answer: "8" },
                { problem: "9 ÷ 3", answer: "3" },
                { problem: "6 + 4", answer: "10" },
                { problem: "8 - 5", answer: "3" },
                { problem: "3 × 3", answer: "9" },
                { problem: "12 ÷ 4", answer: "3" }
            ],
            2: [
                { problem: "15 + 7", answer: "22" },
                { problem: "23 - 9", answer: "14" },
                { problem: "6 × 7", answer: "42" },
                { problem: "48 ÷ 6", answer: "8" },
                { problem: "19 + 8", answer: "27" },
                { problem: "35 - 17", answer: "18" },
                { problem: "9 × 8", answer: "72" },
                { problem: "63 ÷ 9", answer: "7" }
            ],
            3: [
                { problem: "125 + 89", answer: "214" },
                { problem: "204 - 76", answer: "128" },
                { problem: "12 × 15", answer: "180" },
                { problem: "144 ÷ 12", answer: "12" },
                { problem: "56 + 78", answer: "134" },
                { problem: "200 - 123", answer: "77" },
                { problem: "13 × 14", answer: "182" },
                { problem: "196 ÷ 14", answer: "14" }
            ]
        };

        // Initialize Game
        function initGame() {
            createStars();
            generateProblem();
            startGame();
            answerInputEl.focus();
            loadLeaderboard();
        }

        // Create Stars Background
        function createStars() {
            starsEl.innerHTML = '';
            for (let i = 0; i < 100; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                star.style.width = Math.random() * 3 + 'px';
                star.style.height = star.style.width;
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';
                star.style.animationDelay = Math.random() * 2 + 's';
                star.style.opacity = Math.random() * 0.5 + 0.5;
                starsEl.appendChild(star);
            }
        }

        // Load and Display Leaderboard from Database
        function loadLeaderboard() {
            // Use the data from PHP
            const scoresArray = allStudents.map(student => ({
                id: student.id,
                username: student.UserName,
                name: `${student.firstName} ${student.lastName}`,
                highScore: parseInt(student.highScore) || 0
            }));
            
            // Display leaderboard
            leaderboardEl.innerHTML = '';
            scoresArray.forEach((student, index) => {
                const isCurrentUser = student.username === currentStudent.username;
                const rankClass = index === 0 ? 'rank-1' : index === 1 ? 'rank-2' : index === 2 ? 'rank-3' : '';
                
                const li = document.createElement('li');
                li.className = `leaderboard-item ${isCurrentUser ? 'current-user' : ''}`;
                li.innerHTML = `
                    <div class="rank ${rankClass}">${index + 1}</div>
                    <div class="player-name">
                        ${student.name}
                        ${isCurrentUser ? '<span class="player-you">You</span>' : ''}
                    </div>
                    <div class="player-score">${student.highScore.toLocaleString()}</div>
                `;
                
                // Make top player (not current user) clickable for challenge
                if (index === 0 && !isCurrentUser) {
                    li.style.cursor = 'pointer';
                    li.title = 'Challenge this player';
                    li.addEventListener('click', () => openChallengeModal(student));
                }
                
                leaderboardEl.appendChild(li);
            });
            
            // Update challenge button state
            const topScore = scoresArray[0]?.highScore || 0;
            const isTopPlayer = scoresArray[0]?.username === currentStudent.username;
            
            if (isTopPlayer) {
                challengeBtn.innerHTML = '<i class="fas fa-crown"></i> You are the Champion!';
                challengeBtn.disabled = true;
            } else {
                const topPlayerName = scoresArray[0]?.name || 'Top Player';
                challengeBtn.innerHTML = `<i class="fas fa-fist-raised"></i> Challenge #1 (${topPlayerName})`;
                challengeBtn.disabled = false;
                challengeBtn.addEventListener('click', () => openChallengeModal(scoresArray[0]));
            }
        }

        // Open Challenge Modal
        function openChallengeModal(targetPlayer) {
            challengeTargetScore = targetPlayer.highScore;
            challengeTargetName = targetPlayer.name;
            
            challengerNameEl.textContent = targetPlayer.name;
            challengerScoreEl.textContent = targetPlayer.highScore.toLocaleString();
            targetScoreEl.textContent = targetPlayer.highScore.toLocaleString();
            currentScoreEl.textContent = currentStudent.current_high_score.toLocaleString();
            
            challengeModal.classList.add('active');
        }

        // Start Challenge Mode
        function startChallengeMode() {
            isChallengeMode = true;
            challengeModal.classList.remove('active');
            showMessage(`Challenge: Beat ${challengeTargetName}'s score of ${challengeTargetScore}!`, 'success');
            
            // Reset game for challenge
            restartGame();
        }

        // Generate Math Problem
        function generateProblem() {
            const problems = mathProblems[level] || mathProblems[1];
            currentProblem = problems[Math.floor(Math.random() * problems.length)];
            problemTextEl.textContent = currentProblem.problem + " = ?";
            answerInputEl.value = '';
            answerInputEl.focus();
        }

        // Start Game
        function startGame() {
            gameActive = true;
            updateStats();
            
            // Start timers
            gameTimer = setInterval(updateTimer, 1000);
            asteroidInterval = setInterval(createAsteroid, 2000 - (level * 300));
            powerUpInterval = setInterval(createPowerUp, 10000);
        }

        // Pause Game
        function pauseGame() {
            gameActive = !gameActive;
            
            if (!gameActive) {
                clearInterval(gameTimer);
                clearInterval(asteroidInterval);
                clearInterval(powerUpInterval);
                pauseBtn.innerHTML = '<i class="fas fa-play"></i> Resume';
            } else {
                startGame();
                pauseBtn.innerHTML = '<i class="fas fa-pause"></i> Pause';
            }
        }

        // Update Timer
        function updateTimer() {
            if (!gameActive) return;
            
            timeLeft--;
            timeEl.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                endGame();
            }
            
            // Blink warning when time is low
            if (timeLeft <= 10) {
                timeEl.style.color = timeEl.style.color === 'var(--danger)' ? 'var(--white)' : 'var(--danger)';
                timeEl.style.animation = timeLeft <= 5 ? 'pulse 0.5s infinite' : 'none';
            }
        }

        // Update Stats Display
        function updateStats() {
            scoreEl.textContent = score.toLocaleString();
            timeEl.textContent = timeLeft;
            livesEl.textContent = lives;
            levelEl.textContent = level;
            levelProgressEl.textContent = `${progress}/${maxProgress}`;
            progressFillEl.style.width = `${(progress / maxProgress) * 100}%`;
        }

        // Create Asteroid
        function createAsteroid() {
            if (!gameActive) return;
            
            const asteroid = document.createElement('div');
            asteroid.className = 'asteroid';
            
            // Random position
            const leftPos = Math.random() * 80 + 10;
            asteroid.style.left = leftPos + '%';
            
            // Random size
            const size = 40 + Math.random() * 40;
            asteroid.style.width = size + 'px';
            asteroid.style.height = size + 'px';
            
            // Random speed
            const speed = 2 + Math.random() * 3 + level * 0.5;
            asteroid.dataset.speed = speed;
            
            // Random problem
            const problems = mathProblems[level] || mathProblems[1];
            const problem = problems[Math.floor(Math.random() * problems.length)];
            
            // 70% chance of correct answer, 30% wrong
            let displayAnswer;
            let isCorrect;
            
            if (Math.random() < 0.7) {
                displayAnswer = problem.answer;
                isCorrect = true;
            } else {
                // Generate wrong answer
                displayAnswer = parseInt(problem.answer) + (Math.random() < 0.5 ? 1 : -1) * (Math.floor(Math.random() * 5) + 1);
                displayAnswer = Math.max(1, displayAnswer); // Ensure positive
                isCorrect = false;
            }
            
            asteroid.textContent = displayAnswer;
            asteroid.dataset.answer = displayAnswer;
            asteroid.dataset.correct = isCorrect;
            
            // Add click handler
            asteroid.addEventListener('click', () => handleAsteroidClick(asteroid, isCorrect));
            
            gameAreaEl.appendChild(asteroid);
            
            // Animate falling
            let topPos = -100;
            const fallInterval = setInterval(() => {
                if (!gameActive) {
                    clearInterval(fallInterval);
                    asteroid.remove();
                    return;
                }
                
                topPos += parseFloat(asteroid.dataset.speed);
                asteroid.style.top = topPos + 'px';
                
                // Remove if falls past bottom
                if (topPos > window.innerHeight + 100) {
                    clearInterval(fallInterval);
                    asteroid.remove();
                    
                    // Lose life if correct asteroid wasn't clicked
                    if (asteroid.dataset.correct === 'true') {
                        loseLife();
                    }
                }
            }, 20);
        }

        // Create Power-up
        function createPowerUp() {
            if (!gameActive) return;
            
            const powerUps = [
                { type: 'time', icon: 'fa-clock', class: 'time' },
                { type: 'score', icon: 'fa-star', class: 'score' },
                { type: 'shield', icon: 'fa-shield-alt', class: 'shield' }
            ];
            
            const powerUp = powerUps[Math.floor(Math.random() * powerUps.length)];
            const powerUpEl = document.createElement('div');
            
            powerUpEl.className = `power-up ${powerUp.class}`;
            powerUpEl.innerHTML = `<i class="fas ${powerUp.icon}"></i>`;
            powerUpEl.dataset.type = powerUp.type;
            
            // Random position
            powerUpEl.style.left = Math.random() * 80 + 10 + '%';
            powerUpEl.style.top = '-50px';
            
            powerUpEl.addEventListener('click', () => collectPowerUp(powerUpEl));
            
            gameAreaEl.appendChild(powerUpEl);
            
            // Animate falling
            let topPos = -50;
            const fallInterval = setInterval(() => {
                if (!gameActive) {
                    clearInterval(fallInterval);
                    powerUpEl.remove();
                    return;
                }
                
                topPos += 1.5;
                powerUpEl.style.top = topPos + 'px';
                
                // Remove if falls past bottom
                if (topPos > window.innerHeight + 50) {
                    clearInterval(fallInterval);
                    powerUpEl.remove();
                }
            }, 20);
        }

        // Handle Asteroid Click
        function handleAsteroidClick(asteroid, isCorrect) {
            if (!gameActive) return;
            
            totalProblems++;
            
            if (isCorrect) {
                // Correct answer
                asteroid.classList.add('correct');
                score += 10 * level * multiplier;
                correctProblems++;
                problemsSolved++;
                
                // Check answer if input has value
                if (answerInputEl.value === asteroid.dataset.answer) {
                    score += 20 * level * multiplier; // Bonus for typing
                    showMessage('Perfect! +20', 'success');
                }
                
                updateProgress();
            } else {
                // Wrong answer
                asteroid.classList.add('wrong');
                loseLife();
                showMessage('Wrong answer! -1 Life', 'error');
            }
            
            // Create explosion effect
            createExplosion(asteroid);
            
            // Remove asteroid
            setTimeout(() => {
                asteroid.remove();
            }, 300);
            
            // Generate new problem
            setTimeout(generateProblem, 500);
            
            updateStats();
            
            // Check challenge progress
            if (isChallengeMode && score > challengeTargetScore) {
                showMessage(`You beat ${challengeTargetName}'s score! Keep going!`, 'success');
            }
        }

        // Collect Power-up
        function collectPowerUp(powerUp) {
            const type = powerUp.dataset.type;
            
            switch(type) {
                case 'time':
                    timeLeft += 10;
                    showMessage('+10 seconds!', 'time');
                    break;
                case 'score':
                    multiplier = 2;
                    showMessage('2x Score!', 'score');
                    setTimeout(() => {
                        multiplier = 1;
                        showMessage('Multiplier expired', 'info');
                    }, 15000);
                    break;
                case 'shield':
                    shieldActive = true;
                    spaceshipEl.style.border = '3px solid var(--cyan)';
                    showMessage('Shield activated!', 'shield');
                    setTimeout(() => {
                        shieldActive = false;
                        spaceshipEl.style.border = 'none';
                        showMessage('Shield expired', 'info');
                    }, 10000);
                    break;
            }
            
            // Create collection effect
            createExplosion(powerUp, true);
            powerUp.remove();
            updateStats();
        }

        // Create Explosion Effect
        function createExplosion(element, isPowerUp = false) {
            const rect = element.getBoundingClientRect();
            const explosion = document.createElement('div');
            explosion.className = 'explosion';
            explosion.style.left = rect.left + rect.width / 2 + 'px';
            explosion.style.top = rect.top + rect.height / 2 + 'px';
            
            // Create particles
            for (let i = 0; i < 15; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.background = isPowerUp ? getPowerUpColor(element) : 
                    element.dataset.correct === 'true' ? 'var(--secondary)' : 'var(--danger)';
                
                // Random direction and distance
                const angle = Math.random() * Math.PI * 2;
                const distance = 50 + Math.random() * 50;
                const tx = Math.cos(angle) * distance;
                const ty = Math.sin(angle) * distance;
                
                particle.style.setProperty('--tx', tx + 'px');
                particle.style.setProperty('--ty', ty + 'px');
                
                explosion.appendChild(particle);
            }
            
            gameAreaEl.appendChild(explosion);
            
            // Remove after animation
            setTimeout(() => {
                explosion.remove();
            }, 500);
        }

        // Get power-up color
        function getPowerUpColor(element) {
            if (element.classList.contains('time')) return 'var(--accent)';
            if (element.classList.contains('score')) return 'var(--purple)';
            if (element.classList.contains('shield')) return 'var(--cyan)';
            return 'var(--primary)';
        }

        // Show Message
        function showMessage(text, type) {
            const message = document.createElement('div');
            message.textContent = text;
            message.style.position = 'absolute';
            message.style.top = '20%';
            message.style.left = '50%';
            message.style.transform = 'translateX(-50%)';
            message.style.padding = '10px 20px';
            message.style.background = type === 'success' ? 'rgba(16, 185, 129, 0.9)' :
                                      type === 'error' ? 'rgba(239, 68, 68, 0.9)' :
                                      type === 'time' ? 'rgba(245, 158, 11, 0.9)' :
                                      type === 'score' ? 'rgba(139, 92, 246, 0.9)' :
                                      type === 'shield' ? 'rgba(6, 182, 212, 0.9)' :
                                      'rgba(37, 99, 235, 0.9)';
            message.style.color = 'white';
            message.style.borderRadius = 'var(--radius)';
            message.style.fontWeight = '600';
            message.style.zIndex = '1000';
            message.style.animation = 'fadeInOut 2s forwards';
            
            // Add animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fadeInOut {
                    0% { opacity: 0; transform: translateX(-50%) translateY(-20px); }
                    20% { opacity: 1; transform: translateX(-50%) translateY(0); }
                    80% { opacity: 1; transform: translateX(-50%) translateY(0); }
                    100% { opacity: 0; transform: translateX(-50%) translateY(-20px); }
                }
            `;
            document.head.appendChild(style);
            
            gameAreaEl.appendChild(message);
            
            setTimeout(() => {
                message.remove();
                style.remove();
            }, 2000);
        }

        // Lose Life
        function loseLife() {
            if (shieldActive) {
                showMessage('Shield protected you!', 'shield');
                return;
            }
            
            lives--;
            
            // Visual feedback
            spaceshipEl.style.animation = 'shake 0.5s';
            setTimeout(() => {
                spaceshipEl.style.animation = '';
            }, 500);
            
            if (lives <= 0) {
                endGame();
            }
            
            updateStats();
        }

        // Update Progress
        function updateProgress() {
            progress++;
            
            if (progress >= maxProgress) {
                progress = 0;
                levelUp();
            }
            
            updateStats();
        }

        // Level Up
        function levelUp() {
            level++;
            showMessage(`Level ${level}!`, 'success');
            
            // Increase difficulty
            clearInterval(asteroidInterval);
            asteroidInterval = setInterval(createAsteroid, 2000 - (level * 300));
            
            updateStats();
        }

        // Handle Answer Input
        answerInputEl.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const userAnswer = answerInputEl.value.trim();
                if (!userAnswer) return;
                
                totalProblems++;
                
                if (userAnswer === currentProblem.answer) {
                    score += 15 * level * multiplier;
                    correctProblems++;
                    problemsSolved++;
                    showMessage('Correct! +15', 'success');
                    updateProgress();
                } else {
                    loseLife();
                    showMessage('Wrong! Try again', 'error');
                }
                
                updateStats();
                generateProblem();
            }
        });

        // End Game
        function endGame() {
            gameActive = false;
            clearInterval(gameTimer);
            clearInterval(asteroidInterval);
            clearInterval(powerUpInterval);
            
            // Calculate accuracy
            const accuracy = totalProblems > 0 ? Math.round((correctProblems / totalProblems) * 100) : 0;
            
            // Update modal
            finalScoreEl.textContent = score.toLocaleString();
            finalLevelEl.textContent = level;
            problemsSolvedEl.textContent = problemsSolved;
            accuracyEl.textContent = accuracy + '%';
            
            // Check for new high score
            const previousHighScore = currentStudent.current_high_score || 0;
            let isNewRecord = false;
            let isChallengeWon = false;
            
            if (score > previousHighScore) {
                isNewRecord = true;
                newRecordMessage.style.display = 'block';
            }
            
            if (isChallengeMode && score > challengeTargetScore) {
                isChallengeWon = true;
                showMessage(`Challenge Won! You beat ${challengeTargetName}!`, 'success');
            }
            
            // Save score to database
            saveHighScore(score, level, problemsSolved, accuracy, isNewRecord);
            
            // Show modal
            setTimeout(() => {
                gameOverModal.classList.add('active');
            }, 1000);
        }

        // Save High Score to Database
        function saveHighScore(finalScore, finalLevel, problemsSolved, accuracy, isNewRecord = false) {
            const formData = new FormData();
            formData.append('student_id', currentStudent.id);
            formData.append('game_name', 'math-blaster');
            formData.append('score', finalScore);
            formData.append('level', finalLevel);
            formData.append('problems_solved', problemsSolved);
            formData.append('accuracy', accuracy);
            formData.append('play_time', 60 - timeLeft); // Time played in seconds
            
            fetch('save_score.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Score saved successfully');
                    // Reload leaderboard to show updated scores
                    loadLeaderboard();
                    
                    if (data.is_new_high && isNewRecord) {
                        newRecordMessage.style.display = 'block';
                    }
                }
            })
            .catch(error => {
                console.error('Error saving score:', error);
                // Still show new record message if applicable
                if (isNewRecord) {
                    newRecordMessage.style.display = 'block';
                }
            });
        }

        // Restart Game
        function restartGame() {
            // Reset variables
            score = 0;
            timeLeft = 60;
            lives = 3;
            level = 1;
            progress = 0;
            problemsSolved = 0;
            totalProblems = 0;
            correctProblems = 0;
            multiplier = 1;
            shieldActive = false;
            isChallengeMode = false;
            
            // Clear intervals
            clearInterval(gameTimer);
            clearInterval(asteroidInterval);
            clearInterval(powerUpInterval);
            
            // Remove all asteroids and power-ups
            document.querySelectorAll('.asteroid, .power-up').forEach(el => el.remove());
            
            // Reset display
            spaceshipEl.style.border = 'none';
            newRecordMessage.style.display = 'none';
            updateStats();
            generateProblem();
            
            // Close modals if open
            gameOverModal.classList.remove('active');
            challengeModal.classList.remove('active');
            
            // Start game
            startGame();
            answerInputEl.focus();
        }

        // Event Listeners
        pauseBtn.addEventListener('click', pauseGame);
        restartBtn.addEventListener('click', restartGame);
        playAgainBtn.addEventListener('click', restartGame);
        menuBtn.addEventListener('click', () => {
            window.location.href = 'games.php';
        });
        
        cancelChallengeBtn.addEventListener('click', () => {
            challengeModal.classList.remove('active');
        });
        
        acceptChallengeBtn.addEventListener('click', startChallengeMode);

        // Initialize game when page loads
        window.addEventListener('load', initGame);

        // Prevent spacebar from scrolling page
        window.addEventListener('keydown', (e) => {
            if (e.key === ' ' && e.target === document.body) {
                e.preventDefault();
            }
        });

        // Focus input when clicking anywhere
        document.addEventListener('click', () => {
            if (gameActive) {
                answerInputEl.focus();
            }
        });

        // Initial leaderboard load
        loadLeaderboard();
    </script>
</head>
<body>
</body>
</html>