
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
    
    // Get memory cards from database
    $cards_query = "SELECT front_content, back_content, category, card_type 
                    FROM memory_cards 
                    WHERE difficulty = 'easy'
                    ORDER BY RAND() LIMIT 8";
    $cards_result = mysqli_query($conn, $cards_query);
    $cards_data = [];
    
    if ($cards_result && mysqli_num_rows($cards_result) > 0) {
        while ($row = mysqli_fetch_assoc($cards_result)) {
            $cards_data[] = $row;
        }
    } else {
        // Default cards if no database data
        $cards_data = [
            ['front_content' => 'Apple', 'back_content' => '🍎', 'category' => 'Fruits', 'card_type' => 'text'],
            ['front_content' => 'Banana', 'back_content' => '🍌', 'category' => 'Fruits', 'card_type' => 'text'],
            ['front_content' => 'Car', 'back_content' => '🚗', 'category' => 'Transport', 'card_type' => 'text'],
            ['front_content' => 'Bike', 'back_content' => '🚲', 'category' => 'Transport', 'card_type' => 'text'],
            ['front_content' => '5 + 3', 'back_content' => '8', 'category' => 'Math', 'card_type' => 'math'],
            ['front_content' => '10 - 4', 'back_content' => '6', 'category' => 'Math', 'card_type' => 'math'],
            ['front_content' => 'Cat', 'back_content' => '🐱', 'category' => 'Animals', 'card_type' => 'text'],
            ['front_content' => 'Dog', 'back_content' => '🐶', 'category' => 'Animals', 'card_type' => 'text'],
        ];
    }
    
    // Get leaderboard for Memory Match
    $leaderboard_query = "SELECT s.id, s.UserName, s.firstName, s.lastName, 
                                  COALESCE(MAX(g.score), 0) as highScore
                           FROM student_details s
                           LEFT JOIN game_scores g ON s.id = g.student_id AND g.game_name = 'memory-match'
                           GROUP BY s.id, s.UserName, s.firstName, s.lastName
                           ORDER BY highScore DESC, s.firstName ASC
                           LIMIT 10";
    $leaderboard_result = mysqli_query($conn, $leaderboard_query);
    $leaderboard_data = [];
    
    if ($leaderboard_result) {
        while ($row = mysqli_fetch_assoc($leaderboard_result)) {
            $leaderboard_data[] = $row;
        }
    }
    
    // Get current student's high score
    $current_score_query = "SELECT MAX(score) as highScore FROM game_scores 
                            WHERE student_id = ? AND game_name = 'memory-match'";
    $stmt = mysqli_prepare($conn, $current_score_query);
    mysqli_stmt_bind_param($stmt, 'i', $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $current_score_data = mysqli_fetch_assoc($result);
    $current_high_score = $current_score_data['highScore'] ?? 0;
    mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Match • Nexa Games</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #8b5cf6;
            --primary-light: #a78bfa;
            --primary-dark: #7c3aed;
            --secondary: #10b981;
            --accent: #f59e0b;
            --danger: #ef4444;
            --success: #10b981;
            --warning: #f59e0b;
            --info: #3b82f6;
            --dark: #1f2937;
            --light: #f9fafb;
            --white: #ffffff;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --radius: 0.5rem;
            --radius-lg: 1rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
            color: white;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .game-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .game-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-lg);
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius);
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--accent), #fbbf24);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stats-bar {
            display: flex;
            gap: 15px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
        }

        /* Main Content */
        .game-content {
            display: grid;
            grid-template-columns: 300px 1fr 300px;
            gap: 20px;
            min-height: calc(100vh - 140px);
        }

        /* Side Panels */
        .side-panel {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-lg);
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            flex-direction: column;
        }

        .panel-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }

        /* Game Area */
        .game-area {
            background: rgba(255, 255, 255, 0.95);
            border-radius: var(--radius-lg);
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: var(--shadow-lg);
        }

        .game-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            text-align: center;
            margin-bottom: 10px;
        }

        .game-subtitle {
            color: var(--dark);
            text-align: center;
            margin-bottom: 40px;
            opacity: 0.8;
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            max-width: 800px;
            margin: 0 auto;
            width: 100%;
        }

        .card {
            aspect-ratio: 3/4;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            cursor: pointer;
            transform-style: preserve-3d;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            box-shadow: var(--shadow);
            border: 3px solid transparent;
        }

        .card:hover {
            border-color: var(--accent);
        }

        .card .front,
        .card .back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card .front {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            font-size: 3rem;
            font-weight: bold;
        }

        .card .back {
            background: white;
            color: var(--dark);
            transform: rotateY(180deg);
            flex-direction: column;
            padding: 20px;
            gap: 10px;
        }

        .card.flipped {
            transform: rotateY(180deg);
        }

        .card.matched {
            border-color: var(--success);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
            cursor: default;
        }

        .card.matched .front {
            background: linear-gradient(135deg, var(--success), #34d399);
        }

        .card .word {
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
        }

        .card .category {
            font-size: 0.9rem;
            color: var(--dark);
            background: var(--light);
            padding: 4px 12px;
            border-radius: 20px;
        }

        /* Controls */
        .game-controls {
            display: flex;
            gap: 15px;
            margin-top: 40px;
            width: 100%;
            max-width: 800px;
        }

        .control-btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .hint-btn {
            background: linear-gradient(135deg, var(--warning), #fbbf24);
            color: white;
        }

        .restart-btn {
            background: linear-gradient(135deg, var(--info), #60a5fa);
            color: white;
        }

        .control-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .control-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Timer */
        .timer-container {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 0 auto 40px;
        }

        .timer-svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .timer-circle {
            fill: none;
            stroke: var(--primary);
            stroke-width: 8;
            stroke-linecap: round;
            stroke-dasharray: 565;
            stroke-dashoffset: 0;
            transition: stroke-dashoffset 1s linear;
        }

        .timer-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-family: 'Space Grotesk', sans-serif;
            font-size: 3rem;
            font-weight: 700;
            color: var(--dark);
        }

        /* Feedback */
        .feedback {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 30px 60px;
            border-radius: var(--radius);
            font-size: 2rem;
            font-weight: 700;
            z-index: 1000;
            animation: feedbackAnimation 1.5s ease-out;
            pointer-events: none;
            text-align: center;
        }

        @keyframes feedbackAnimation {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); }
            20% { opacity: 1; transform: translate(-50%, -50%) scale(1.1); }
            40% { transform: translate(-50%, -50%) scale(1); }
            80% { opacity: 1; }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(1.2); }
        }

        .feedback.correct {
            background: linear-gradient(135deg, var(--success), #34d399);
            color: white;
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.3);
        }

        .feedback.wrong {
            background: linear-gradient(135deg, var(--danger), #f87171);
            color: white;
            box-shadow: 0 20px 40px rgba(239, 68, 68, 0.3);
        }

        /* Leaderboard */
        .leaderboard-list {
            list-style: none;
            max-height: 300px;
            overflow-y: auto;
        }

        .leaderboard-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            margin-bottom: 8px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--radius);
            transition: all 0.3s;
        }

        .leaderboard-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .leaderboard-item.current-user {
            background: rgba(139, 92, 246, 0.2);
            border: 1px solid rgba(139, 92, 246, 0.3);
        }

        .rank {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .rank-1 { background: linear-gradient(135deg, var(--accent), #d97706); }
        .rank-2 { background: linear-gradient(135deg, #6b7280, #4b5563); }
        .rank-3 { background: linear-gradient(135deg, #92400e, #78350f); }

        .player-name {
            flex: 1;
            font-size: 0.9rem;
        }

        .player-score {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            color: var(--accent);
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
            backdrop-filter: blur(5px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .game-modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius-lg);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 30px;
            text-align: center;
        }

        .modal-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .modal-body {
            padding: 30px;
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
            background: #f9fafb;
            border-radius: var(--radius);
            border: 1px solid #e5e7eb;
        }

        .result-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 15px;
        }

        .modal-btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .modal-btn.primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .modal-btn.secondary {
            background: #f3f4f6;
            color: var(--dark);
        }

        /* Game Modes */
        .game-modes {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            width: 100%;
            max-width: 800px;
        }

        .mode-btn {
            flex: 1;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius);
            color: white;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .mode-btn.active {
            background: white;
            color: var(--primary);
            border-color: white;
        }

        /* Progress */
        .progress-container {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--radius);
            margin-bottom: 20px;
        }

        .progress-text {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .progress-bar-container {
            height: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            margin-top: 15px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            width: 0%;
            transition: width 0.3s;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .game-content {
                grid-template-columns: 250px 1fr 250px;
            }
            
            .cards-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 992px) {
            .game-content {
                grid-template-columns: 1fr;
                grid-template-rows: auto;
            }
            
            .side-panel {
                display: none;
            }
            
            .cards-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (max-width: 768px) {
            .game-header {
                flex-direction: column;
                gap: 20px;
            }
            
            .stats-bar {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .cards-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
            }
            
            .card {
                font-size: 2rem;
            }
            
            .card .word {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 576px) {
            .cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .game-controls {
                flex-direction: column;
            }
            
            .timer-container {
                width: 150px;
                height: 150px;
            }
            
            .timer-text {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="game-container">
        <!-- Game Header -->
        <header class="game-header">
            <div class="header-left">
                <a href="../games.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Games
                </a>
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <span>Memory Match</span>
                </div>
            </div>
            
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div>
                        <div class="stat-value" id="score">0</div>
                        <div>Score</div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="stat-value" id="time">90</div>
                        <div>Time</div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div>
                        <div class="stat-value" id="matches">0</div>
                        <div>Matches</div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <div class="stat-value" id="level">1</div>
                        <div>Level</div>
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
                    Game Modes
                </h3>
                
                <div class="game-modes">
                    <button class="mode-btn active" id="modeEasy">
                        <i class="fas fa-smile"></i>
                        Easy (4x4)
                    </button>
                    <button class="mode-btn" id="modeMedium">
                        <i class="fas fa-meh"></i>
                        Medium (6x6)
                    </button>
                </div>
                
                <h3 class="panel-title" style="margin-top: 30px;">
                    <i class="fas fa-info-circle"></i>
                    How to Play
                </h3>
                
                <ul style="list-style: none; color: rgba(255, 255, 255, 0.9); line-height: 1.6; font-size: 0.9rem;">
                    <li style="margin-bottom: 10px; display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-check" style="color: var(--success); margin-top: 3px;"></i>
                        <span>Click on cards to flip them</span>
                    </li>
                    <li style="margin-bottom: 10px; display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-check" style="color: var(--success); margin-top: 3px;"></i>
                        <span>Find matching pairs of cards</span>
                    </li>
                    <li style="margin-bottom: 10px; display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-check" style="color: var(--success); margin-top: 3px;"></i>
                        <span>Complete all matches before time runs out</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-check" style="color: var(--success); margin-top: 3px;"></i>
                        <span>Earn points for each match</span>
                    </li>
                </ul>
                
                <div class="progress-container" style="margin-top: auto;">
                    <div class="progress-text" id="progressText">0/8</div>
                    <div style="color: rgba(255, 255, 255, 0.8);">Matches Found</div>
                    <div class="progress-bar-container">
                        <div id="progressBar" class="progress-bar"></div>
                    </div>
                </div>
            </div>

            <!-- Main Game Area -->
            <div class="game-area">
                <div style="text-align: center; margin-bottom: 30px;">
                    <h1 class="game-title">Memory Match Challenge</h1>
                    <p class="game-subtitle">Find all matching pairs before time runs out!</p>
                </div>
                
                <div class="timer-container">
                    <svg class="timer-svg">
                        <circle class="timer-circle" cx="100" cy="100" r="90" stroke-dashoffset="0"></circle>
                    </svg>
                    <div class="timer-text" id="timerText">90</div>
                </div>
                
                <div class="cards-grid" id="cardsGrid">
                    <!-- Cards will be dynamically generated -->
                </div>
                
                <div class="game-controls">
                    <button class="control-btn hint-btn" id="hintBtn">
                        <i class="fas fa-lightbulb"></i>
                        Show Hint (-5 pts)
                    </button>
                    <button class="control-btn restart-btn" id="restartBtn">
                        <i class="fas fa-redo"></i>
                        Restart Game
                    </button>
                </div>
            </div>

            <!-- Right Panel -->
            <div class="side-panel">
                <h3 class="panel-title">
                    <i class="fas fa-trophy"></i>
                    Leaderboard
                </h3>
                
                <div class="leaderboard">
                    <ul class="leaderboard-list" id="leaderboard">
                        <!-- Leaderboard will be loaded here -->
                    </ul>
                </div>
                
                <h3 class="panel-title" style="margin-top: 30px;">
                    <i class="fas fa-chart-line"></i>
                    Your Stats
                </h3>
                
                <div style="margin-bottom: 20px;">
                    <div class="stat-item" style="background: rgba(255, 255, 255, 0.05); margin-bottom: 10px;">
                        <div class="stat-icon" style="background: linear-gradient(135deg, var(--accent), #d97706);">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div>
                            <div class="stat-value"><?php echo $current_high_score; ?></div>
                            <div>High Score</div>
                        </div>
                    </div>
                    
                    <div class="stat-item" style="background: rgba(255, 255, 255, 0.05);">
                        <div class="stat-icon" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark));">
                            <i class="fas fa-brain"></i>
                        </div>
                        <div>
                            <div class="stat-value" id="totalMatches">0</div>
                            <div>Total Matches</div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: auto;">
                    <button class="control-btn" id="backBtn" style="background: rgba(255, 255, 255, 0.1); color: white; width: 100%;">
                        <i class="fas fa-home"></i>
                        Back to Games
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Game Over Modal -->
    <div class="game-modal" id="gameOverModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Game Over!</h2>
                <p id="modalSubtitle">Great memory training!</p>
            </div>
            
            <div class="modal-body">
                <div id="newRecordMessage" style="display: none; background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.05)); padding: 15px; border-radius: var(--radius); border: 1px solid rgba(245, 158, 11, 0.3); margin-bottom: 20px; text-align: center;">
                    <i class="fas fa-trophy" style="color: var(--accent); margin-right: 8px;"></i>
                    <strong>New High Score!</strong>
                </div>
                
                <div class="result-stats">
                    <div class="result-stat">
                        <div class="result-value" id="finalScore">0</div>
                        <div>Final Score</div>
                    </div>
                    
                    <div class="result-stat">
                        <div class="result-value" id="finalTime">0</div>
                        <div>Time Left</div>
                    </div>
                    
                    <div class="result-stat">
                        <div class="result-value" id="finalMatches">0</div>
                        <div>Matches Found</div>
                    </div>
                    
                    <div class="result-stat">
                        <div class="result-value" id="accuracy">0%</div>
                        <div>Accuracy</div>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 20px; color: var(--dark);">
                    <p id="performanceText">Keep practicing to improve your memory!</p>
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="modal-btn secondary" id="menuBtn">
                    Back to Games
                </button>
                <button class="modal-btn primary" id="playAgainBtn">
                    Play Again
                </button>
            </div>
        </div>
    </div>

    <script>
        // Game Variables
        let score = 0;
        let timeLeft = 90;
        let matchesFound = 0;
        let level = 1;
        let totalCards = 8;
        let totalMatches = 0;
        let gameActive = true;
        let gameTimer;
        let flippedCards = [];
        let matchedPairs = [];
        let gameMode = 'easy'; // 'easy' or 'medium'
        let canFlip = true;
        
        // Cards data from PHP
        let cardsData = <?php echo json_encode($cards_data); ?>;
        let cards = [];
        
        // DOM Elements
        const scoreEl = document.getElementById('score');
        const timeEl = document.getElementById('time');
        const timerText = document.getElementById('timerText');
        const matchesEl = document.getElementById('matches');
        const levelEl = document.getElementById('level');
        const cardsGrid = document.getElementById('cardsGrid');
        const hintBtn = document.getElementById('hintBtn');
        const restartBtn = document.getElementById('restartBtn');
        const backBtn = document.getElementById('backBtn');
        const gameOverModal = document.getElementById('gameOverModal');
        const finalScoreEl = document.getElementById('finalScore');
        const finalTimeEl = document.getElementById('finalTime');
        const finalMatchesEl = document.getElementById('finalMatches');
        const accuracyEl = document.getElementById('accuracy');
        const menuBtn = document.getElementById('menuBtn');
        const playAgainBtn = document.getElementById('playAgainBtn');
        const leaderboardEl = document.getElementById('leaderboard');
        const newRecordMessage = document.getElementById('newRecordMessage');
        const progressText = document.getElementById('progressText');
        const progressBar = document.getElementById('progressBar');
        const modalTitle = document.getElementById('modalTitle');
        const modalSubtitle = document.getElementById('modalSubtitle');
        const performanceText = document.getElementById('performanceText');
        const modeEasyBtn = document.getElementById('modeEasy');
        const modeMediumBtn = document.getElementById('modeMedium');
        const totalMatchesEl = document.getElementById('totalMatches');

        // Student data
        const currentStudent = {
            id: <?php echo $student_id; ?>,
            username: '<?php echo $student_username; ?>',
            name: '<?php echo $student_name; ?>',
            current_high_score: <?php echo $current_high_score; ?>
        };

        // Initialize Game
        function initGame() {
            createCards();
            shuffleCards();
            renderCards();
            startTimer();
            loadLeaderboard();
            updateProgress();
        }

        // Create card pairs from data
        function createCards() {
            cards = [];
            
            // Use first 8 cards for easy mode
            let selectedCards = cardsData.slice(0, totalCards / 2);
            
            // Create pairs for each card
            selectedCards.forEach(card => {
                // Create first card
                cards.push({
                    id: Date.now() + Math.random(),
                    front: card.front_content,
                    back: card.back_content,
                    category: card.category,
                    type: card.card_type,
                    matched: false
                });
                
                // Create matching pair
                cards.push({
                    id: Date.now() + Math.random(),
                    front: card.front_content,
                    back: card.back_content,
                    category: card.category,
                    type: card.card_type,
                    matched: false
                });
            });
        }

        // Shuffle cards using Fisher-Yates algorithm
        function shuffleCards() {
            for (let i = cards.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [cards[i], cards[j]] = [cards[j], cards[i]];
            }
        }

        // Render cards to grid
        function renderCards() {
            cardsGrid.innerHTML = '';
            
            // Update grid layout based on mode
            if (gameMode === 'easy') {
                cardsGrid.style.gridTemplateColumns = 'repeat(4, 1fr)';
                cardsGrid.style.maxWidth = '800px';
            } else {
                cardsGrid.style.gridTemplateColumns = 'repeat(6, 1fr)';
                cardsGrid.style.maxWidth = '1000px';
            }
            
            cards.forEach((card, index) => {
                const cardElement = document.createElement('div');
                cardElement.className = 'card';
                cardElement.dataset.index = index;
                
                if (card.matched) {
                    cardElement.classList.add('matched');
                    cardElement.classList.add('flipped');
                }
                
                cardElement.innerHTML = `
                    <div class="front">?</div>
                    <div class="back">
                        ${card.type === 'text' || card.type === 'math' 
                            ? `<div class="word">${card.back}</div>` 
                            : `<div style="font-size: 3rem;">${card.back}</div>`
                        }
                        ${card.category ? `<div class="category">${card.category}</div>` : ''}
                    </div>
                `;
                
                cardElement.addEventListener('click', () => flipCard(index));
                cardsGrid.appendChild(cardElement);
            });
        }

        // Flip card
        function flipCard(index) {
            if (!gameActive || !canFlip || cards[index].matched) return;
            
            const cardElement = document.querySelector(`.card[data-index="${index}"]`);
            
            // Don't flip if already flipped or matched
            if (cardElement.classList.contains('flipped') || flippedCards.includes(index)) {
                return;
            }
            
            // Flip the card
            cardElement.classList.add('flipped');
            flippedCards.push(index);
            
            // Check for match when two cards are flipped
            if (flippedCards.length === 2) {
                canFlip = false;
                setTimeout(checkMatch, 800);
            }
        }

        // Check if flipped cards match
        function checkMatch() {
            const [firstIndex, secondIndex] = flippedCards;
            const card1 = cards[firstIndex];
            const card2 = cards[secondIndex];
            
            if (card1.front === card2.front) {
                // Match found
                cards[firstIndex].matched = true;
                cards[secondIndex].matched = true;
                matchedPairs.push([firstIndex, secondIndex]);
                matchesFound++;
                totalMatches++;
                
                // Update UI for matched cards
                document.querySelectorAll(`.card[data-index="${firstIndex}"], .card[data-index="${secondIndex}"]`)
                    .forEach(card => card.classList.add('matched'));
                
                // Add points
                const points = calculatePoints();
                score += points;
                
                // Show feedback
                showFeedback(`Match! +${points}`, 'correct');
                
                // Update displays
                scoreEl.textContent = score;
                matchesEl.textContent = matchesFound;
                totalMatchesEl.textContent = totalMatches;
                updateProgress();
                
                // Check for level completion
                if (matchesFound === totalCards / 2) {
                    setTimeout(() => levelComplete(), 1000);
                }
            } else {
                // No match
                showFeedback('Try Again!', 'wrong');
                
                // Flip cards back after delay
                setTimeout(() => {
                    document.querySelectorAll(`.card[data-index="${firstIndex}"], .card[data-index="${secondIndex}"]`)
                        .forEach(card => card.classList.remove('flipped'));
                }, 1000);
            }
            
            // Reset flipped cards
            flippedCards = [];
            canFlip = true;
        }

        // Calculate points based on time left and level
        function calculatePoints() {
            let basePoints = 10;
            let timeBonus = Math.floor(timeLeft / 10);
            let levelBonus = level * 5;
            
            return basePoints + timeBonus + levelBonus;
        }

        // Update progress bar
        function updateProgress() {
            const progress = (matchesFound / (totalCards / 2)) * 100;
            progressText.textContent = `${matchesFound}/${totalCards / 2}`;
            progressBar.style.width = `${progress}%`;
        }

        // Level complete
        function levelComplete() {
            if (!gameActive) return;
            
            // Bonus points for completing level
            const levelBonus = level * 50;
            score += levelBonus;
            scoreEl.textContent = score;
            
            showFeedback(`Level Complete! +${levelBonus}`, 'correct');
            
            // Move to next level
            setTimeout(() => {
                level++;
                levelEl.textContent = level;
                
                // Reset for next level
                matchesFound = 0;
                flippedCards = [];
                matchedPairs = [];
                
                // Increase difficulty
                if (level % 3 === 0 && gameMode === 'easy') {
                    // Switch to medium mode
                    changeGameMode('medium');
                } else {
                    // Continue with current mode
                    createCards();
                    shuffleCards();
                    renderCards();
                    updateProgress();
                }
                
                // Add time bonus
                timeLeft += 30;
                timeEl.textContent = timeLeft;
                timerText.textContent = timeLeft;
                
                showFeedback('Time Bonus +30s!', 'correct');
            }, 1500);
        }

        // Start timer
        function startTimer() {
            clearInterval(gameTimer);
            gameTimer = setInterval(() => {
                if (!gameActive) return;
                
                timeLeft--;
                timeEl.textContent = timeLeft;
                timerText.textContent = timeLeft;
                
                // Update timer circle
                const timerCircle = document.querySelector('.timer-circle');
                const circumference = 565;
                const offset = circumference - (timeLeft / 90) * circumference;
                timerCircle.style.strokeDashoffset = offset;
                
                // Warning when time is low
                if (timeLeft <= 10) {
                    timerText.style.color = timerText.style.color === 'var(--danger)' ? 'var(--dark)' : 'var(--danger)';
                    timeEl.style.color = timeEl.style.color === 'var(--danger)' ? 'white' : 'var(--danger)';
                }
                
                if (timeLeft <= 0) {
                    endGame();
                }
            }, 1000);
        }

        // Show hint
        function showHint() {
            if (score < 5 || !gameActive) {
                showFeedback('Need 5 points for hint!', 'wrong');
                return;
            }
            
            score -= 5;
            scoreEl.textContent = score;
            
            // Find first unmatched pair
            const unmatchedCards = cards.reduce((acc, card, index) => {
                if (!card.matched && !flippedCards.includes(index)) {
                    acc.push(index);
                }
                return acc;
            }, []);
            
            if (unmatchedCards.length >= 2) {
                // Show two matching cards briefly
                const firstIndex = unmatchedCards[0];
                const secondIndex = unmatchedCards.find((idx, i) => 
                    i > 0 && cards[idx].front === cards[unmatchedCards[0]].front
                );
                
                if (secondIndex !== undefined) {
                    const card1 = document.querySelector(`.card[data-index="${firstIndex}"]`);
                    const card2 = document.querySelector(`.card[data-index="${secondIndex}"]`);
                    
                    // Briefly show the cards
                    card1.classList.add('flipped');
                    card2.classList.add('flipped');
                    
                    setTimeout(() => {
                        card1.classList.remove('flipped');
                        card2.classList.remove('flipped');
                    }, 1500);
                    
                    showFeedback('Hint Used! -5 points', 'warning');
                }
            }
        }

        // Show feedback animation
        function showFeedback(text, type) {
            const feedback = document.createElement('div');
            feedback.className = `feedback ${type}`;
            feedback.textContent = text;
            document.body.appendChild(feedback);
            
            setTimeout(() => {
                feedback.remove();
            }, 1500);
        }

        // Load leaderboard
        function loadLeaderboard() {
            const leaderboardData = <?php echo json_encode($leaderboard_data); ?>;
            
            leaderboardEl.innerHTML = '';
            leaderboardData.forEach((student, index) => {
                const isCurrentUser = student.UserName === currentStudent.username;
                const li = document.createElement('li');
                li.className = `leaderboard-item ${isCurrentUser ? 'current-user' : ''}`;
                li.innerHTML = `
                    <div class="rank ${index === 0 ? 'rank-1' : index === 1 ? 'rank-2' : index === 2 ? 'rank-3' : ''}">
                        ${index + 1}
                    </div>
                    <div class="player-name">
                        ${student.firstName} ${student.lastName}
                        ${isCurrentUser ? ' (You)' : ''}
                    </div>
                    <div class="player-score">${parseInt(student.highScore).toLocaleString()}</div>
                `;
                leaderboardEl.appendChild(li);
            });
        }

        // Save score
        // Replace the saveScore() function in memory-match.php with this improved version:
function saveScore() {
    const accuracy = totalCards > 0 ? Math.round((matchedPairs.length * 2 / totalCards) * 100) : 0;
    const playTime = 90 - timeLeft;
    
    console.log("Saving Memory Match score:", {
        student_id: currentStudent.id,
        game_name: 'memory-match',
        score: score,
        level: level,
        problems_solved: matchesFound,
        accuracy: accuracy,
        play_time: playTime
    });
    
    // Create FormData object
    const formData = new FormData();
    formData.append('student_id', currentStudent.id);
    formData.append('game_name', 'memory-match');
    formData.append('score', score);
    formData.append('level', level);
    formData.append('problems_solved', matchesFound);
    formData.append('accuracy', accuracy);
    formData.append('play_time', playTime);
    
    // Debug: Log FormData contents
    console.log("FormData contents:");
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    
    // Send request to save_score.php
    fetch('save_score.php', {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log("Response status:", response.status, response.statusText);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log("Save response received:", data);
        if (data.success) {
            console.log('Score saved successfully');
            if (data.is_new_high) {
                newRecordMessage.style.display = 'block';
                console.log('NEW HIGH SCORE!');
            }
            // Reload leaderboard after saving
            loadLeaderboard();
        } else {
            console.error('Error saving score:', data.error);
            // Try alternative method if fetch fails
            saveScoreAlternative();
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        // Try alternative method using XMLHttpRequest
        saveScoreAlternative();
    });
}

// Add this alternative save function:
function saveScoreAlternative() {
    const accuracy = totalCards > 0 ? Math.round((matchedPairs.length * 2 / totalCards) * 100) : 0;
    const playTime = 90 - timeLeft;
    
    console.log("Trying alternative save method...");
    
    const xhr = new XMLHttpRequest();
    const url = 'save_score.php';
    
    // Create URL encoded data
    const params = new URLSearchParams();
    params.append('student_id', currentStudent.id);
    params.append('game_name', 'memory-match');
    params.append('score', score);
    params.append('level', level);
    params.append('problems_solved', matchesFound);
    params.append('accuracy', accuracy);
    params.append('play_time', playTime);
    
    console.log("Sending via XHR:", params.toString());
    
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('Accept', 'application/json');
    
    xhr.onload = function() {
        console.log("XHR Response status:", xhr.status, xhr.statusText);
        console.log("XHR Response:", xhr.responseText);
        
        if (xhr.status === 200) {
            try {
                const data = JSON.parse(xhr.responseText);
                console.log("XHR Save response:", data);
                if (data.success) {
                    console.log('Score saved successfully via XHR');
                    if (data.is_new_high) {
                        newRecordMessage.style.display = 'block';
                    }
                    loadLeaderboard();
                } else {
                    console.error('XHR Error saving score:', data.error);
                }
            } catch (e) {
                console.error("Failed to parse XHR response:", e, "Raw:", xhr.responseText);
            }
        } else {
            console.error("XHR failed with status:", xhr.status);
        }
    };
    
    xhr.onerror = function() {
        console.error("XHR network error");
    };
    
    xhr.send(params.toString());
}
        // End game
        function endGame() {
            gameActive = false;
            clearInterval(gameTimer);
            
            const accuracy = totalCards > 0 ? Math.round((matchedPairs.length * 2 / totalCards) * 100) : 0;
            
            finalScoreEl.textContent = score.toLocaleString();
            finalTimeEl.textContent = timeLeft;
            finalMatchesEl.textContent = matchesFound;
            accuracyEl.textContent = accuracy + '%';
            
            // Set modal text based on performance
            if (matchesFound === totalCards / 2) {
                modalTitle.textContent = 'Level Complete!';
                modalSubtitle.textContent = 'Excellent memory skills!';
                performanceText.textContent = 'You found all matches! Great job!';
            } else if (accuracy >= 80) {
                modalTitle.textContent = 'Good Job!';
                modalSubtitle.textContent = 'Great memory training!';
                performanceText.textContent = 'You have excellent memory skills!';
            } else {
                modalTitle.textContent = 'Game Over!';
                modalSubtitle.textContent = 'Keep practicing!';
                performanceText.textContent = 'Practice makes perfect! Try again!';
            }
            
            saveScore();
            
            setTimeout(() => {
                gameOverModal.classList.add('active');
            }, 1000);
        }

        // Restart game
        function restartGame() {
            score = 0;
            timeLeft = 90;
            matchesFound = 0;
            level = 1;
            matchedPairs = [];
            flippedCards = [];
            gameActive = true;
            canFlip = true;
            
            scoreEl.textContent = score;
            timeEl.textContent = timeLeft;
            timerText.textContent = timeLeft;
            matchesEl.textContent = matchesFound;
            levelEl.textContent = level;
            timerText.style.color = '';
            timeEl.style.color = 'white';
            
            gameOverModal.classList.remove('active');
            newRecordMessage.style.display = 'none';
            
            createCards();
            shuffleCards();
            renderCards();
            startTimer();
            updateProgress();
        }

        // Change game mode
        function changeGameMode(mode) {
            gameMode = mode;
            
            modeEasyBtn.classList.remove('active');
            modeMediumBtn.classList.remove('active');
            
            if (mode === 'easy') {
                modeEasyBtn.classList.add('active');
                totalCards = 8;
            } else {
                modeMediumBtn.classList.add('active');
                totalCards = 12;
            }
            
            // Restart with new mode
            restartGame();
        }

        // Event Listeners
        hintBtn.addEventListener('click', showHint);
        restartBtn.addEventListener('click', restartGame);
        backBtn.addEventListener('click', () => {
            window.location.href = 'games.php';
        });
        playAgainBtn.addEventListener('click', restartGame);
        menuBtn.addEventListener('click', () => {
            window.location.href = 'games.php';
        });

        modeEasyBtn.addEventListener('click', () => changeGameMode('easy'));
        modeMediumBtn.addEventListener('click', () => changeGameMode('medium'));

        // Initialize game
        initGame();
    </script>
</body>
</html>
