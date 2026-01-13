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
    
    // Get words from database
    $words_query = "SELECT word, definition, category, difficulty, synonyms, example 
                    FROM word_game_data 
                    ORDER BY RAND() LIMIT 50";
    $words_result = mysqli_query($conn, $words_query);
    $word_list = [];
    
    if ($words_result && mysqli_num_rows($words_result) > 0) {
        while ($row = mysqli_fetch_assoc($words_result)) {
            $word_list[] = $row;
        }
    } else {
        // If no words in database, use default words
        $word_list = [
            ['word' => 'apple', 'definition' => 'A round fruit with red or green skin', 'category' => 'Fruits', 'difficulty' => 'easy', 'synonyms' => 'fruit, pomaceous fruit', 'example' => 'I eat an apple every day for health.'],
            ['word' => 'book', 'definition' => 'A set of written or printed pages bound together', 'category' => 'Education', 'difficulty' => 'easy', 'synonyms' => 'volume, publication, tome', 'example' => 'She is reading an interesting book.'],
            ['word' => 'cat', 'definition' => 'A small domesticated carnivorous mammal', 'category' => 'Animals', 'difficulty' => 'easy', 'synonyms' => 'feline, kitty, pet', 'example' => 'The cat is sleeping on the chair.'],
            ['word' => 'dog', 'definition' => 'A domesticated carnivorous mammal', 'category' => 'Animals', 'difficulty' => 'easy', 'synonyms' => 'canine, puppy, hound', 'example' => 'The dog barked at the mailman.'],
            ['word' => 'elephant', 'definition' => 'A very large herbivorous mammal with a trunk', 'category' => 'Animals', 'difficulty' => 'medium', 'synonyms' => 'pachyderm, jumbo, tusker', 'example' => 'The elephant is the largest land animal.'],
        ];
    }
    
    // Get leaderboard for Word Master
    $leaderboard_query = "SELECT s.id, s.UserName, s.firstName, s.lastName, 
                                  COALESCE(MAX(g.score), 0) as highScore
                           FROM student_details s
                           LEFT JOIN game_scores g ON s.id = g.student_id AND g.game_name = 'word-master'
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
                            WHERE student_id = ? AND game_name = 'word-master'";
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
    <title>Word Master • Nexa Games</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #7c3aed;
            --primary-light: #8b5cf6;
            --primary-dark: #6d28d9;
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
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
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

        .word-display {
            text-align: center;
            margin-bottom: 40px;
            width: 100%;
        }

        .word-text {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 4rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .word-category {
            display: inline-block;
            padding: 8px 20px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .definition-box {
            background: white;
            border-radius: var(--radius);
            padding: 30px;
            margin-bottom: 30px;
            border: 2px solid #e5e7eb;
            width: 100%;
            max-width: 600px;
        }

        .definition-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 15px;
        }

        .definition-text {
            font-size: 1.2rem;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .example-text {
            padding: 15px;
            background: #f3f4f6;
            border-radius: var(--radius);
            border-left: 4px solid var(--primary);
            font-style: italic;
            color: #6b7280;
        }

        .input-area {
            width: 100%;
            max-width: 600px;
            margin-top: 30px;
        }

        .input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .word-input {
            flex: 1;
            padding: 18px 25px;
            font-size: 1.2rem;
            border: 2px solid #d1d5db;
            border-radius: var(--radius);
            outline: none;
            transition: all 0.3s;
        }

        .word-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }

        .submit-btn {
            padding: 18px 35px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Options */
        .options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
            width: 100%;
            max-width: 600px;
        }

        .option-btn {
            padding: 20px;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: var(--radius);
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--dark);
            cursor: pointer;
            transition: all 0.3s;
            text-align: left;
        }

        .option-btn:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .option-btn.correct {
            background: linear-gradient(135deg, var(--success), #34d399);
            color: white;
            border-color: transparent;
        }

        .option-btn.wrong {
            background: linear-gradient(135deg, var(--danger), #f87171);
            color: white;
            border-color: transparent;
        }

        /* Game Controls */
        .game-controls {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            width: 100%;
            max-width: 600px;
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

        .skip-btn {
            background: linear-gradient(135deg, var(--info), #60a5fa);
            color: white;
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

        /* Game Modes */
        .game-modes {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            width: 100%;
            max-width: 600px;
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
            background: rgba(124, 58, 237, 0.2);
            border: 1px solid rgba(124, 58, 237, 0.3);
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

        /* Responsive */
        @media (max-width: 1200px) {
            .game-content {
                grid-template-columns: 250px 1fr 250px;
            }
            
            .word-text {
                font-size: 3rem;
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
            
            .options-grid {
                grid-template-columns: 1fr;
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
            }
            
            .word-text {
                font-size: 2.5rem;
            }
            
            .input-group {
                flex-direction: column;
            }
            
            .submit-btn {
                width: 100%;
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
                        <i class="fas fa-book"></i>
                    </div>
                    <span>Word Master</span>
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
                        <div class="stat-value" id="time">60</div>
                        <div>Time</div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div>
                        <div class="stat-value" id="lives">3</div>
                        <div>Lives</div>
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
                    <button class="mode-btn active" id="modeDefinition">
                        <i class="fas fa-book"></i>
                        Definition
                    </button>
                    <button class="mode-btn" id="modeSpelling">
                        <i class="fas fa-spell-check"></i>
                        Spelling
                    </button>
                </div>
                
                <h3 class="panel-title" style="margin-top: 30px;">
                    <i class="fas fa-info-circle"></i>
                    How to Play
                </h3>
                
                <ul style="list-style: none; color: rgba(255, 255, 255, 0.9); line-height: 1.6; font-size: 0.9rem;">
                    <li style="margin-bottom: 10px; display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-check" style="color: var(--success); margin-top: 3px;"></i>
                        <span>Read the definition and guess the word</span>
                    </li>
                    <li style="margin-bottom: 10px; display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-check" style="color: var(--success); margin-top: 3px;"></i>
                        <span>Type your answer or choose from options</span>
                    </li>
                    <li style="margin-bottom: 10px; display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-check" style="color: var(--success); margin-top: 3px;"></i>
                        <span>Get points for correct answers</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-check" style="color: var(--success); margin-top: 3px;"></i>
                        <span>Complete words to level up</span>
                    </li>
                </ul>
                
                <div style="margin-top: auto; padding-top: 20px;">
                    <button class="control-btn hint-btn" id="hintBtn">
                        <i class="fas fa-lightbulb"></i>
                        Get Hint (-10 pts)
                    </button>
                </div>
            </div>

            <!-- Main Game Area -->
            <div class="game-area">
                <div class="word-display">
                    <div class="word-text" id="wordText">WORD</div>
                    <span class="word-category" id="wordCategory">Vocabulary</span>
                </div>
                
                <div class="definition-box">
                    <div class="definition-title">
                        <i class="fas fa-info-circle"></i>
                        Definition
                    </div>
                    <p class="definition-text" id="definitionText">Read the definition and try to guess the word. Type your answer below or select from the options.</p>
                    <div class="example-text" id="exampleText">Example sentence will appear here.</div>
                </div>
                
                <div class="options-grid" id="optionsGrid">
                    <!-- Options will be dynamically generated -->
                </div>
                
                <div class="input-area">
                    <div class="input-group">
                        <input type="text" class="word-input" id="wordInput" placeholder="Type the word here..." autocomplete="off">
                        <button class="submit-btn" id="submitBtn">
                            <i class="fas fa-paper-plane"></i>
                            Submit Answer
                        </button>
                    </div>
                    
                    <div class="game-controls">
                        <button class="control-btn skip-btn" id="skipBtn">
                            <i class="fas fa-forward"></i>
                            Skip Word
                        </button>
                    </div>
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
                    Progress
                </h3>
                
                <div style="text-align: center; padding: 20px; background: rgba(255, 255, 255, 0.05); border-radius: var(--radius);">
                    <div style="font-size: 2rem; font-weight: 700; margin-bottom: 5px;" id="progressText">0/10</div>
                    <div style="color: rgba(255, 255, 255, 0.8);">Words Solved</div>
                    <div style="height: 10px; background: rgba(255, 255, 255, 0.1); border-radius: 5px; margin-top: 15px; overflow: hidden;">
                        <div id="progressBar" style="height: 100%; background: linear-gradient(90deg, var(--primary), var(--accent)); width: 0%; transition: width 0.3s;"></div>
                    </div>
                </div>
                
                <div style="margin-top: 20px;">
                    <button class="control-btn" id="restartBtn" style="background: rgba(255, 255, 255, 0.1); color: white; width: 100%;">
                        <i class="fas fa-redo"></i>
                        Restart Game
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Game Over Modal -->
    <div class="game-modal" id="gameOverModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Game Over!</h2>
                <p>Great job learning new words!</p>
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
                        <div class="result-value" id="finalLevel">1</div>
                        <div>Level Reached</div>
                    </div>
                    
                    <div class="result-stat">
                        <div class="result-value" id="wordsSolved">0</div>
                        <div>Words Solved</div>
                    </div>
                    
                    <div class="result-stat">
                        <div class="result-value" id="accuracy">0%</div>
                        <div>Accuracy</div>
                    </div>
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

        // Test database connection on page load
function testConnection() {
    console.log("Testing connection to save_score.php...");
    
    fetch('save_score.php?test=1')
    .then(response => {
        console.log("Connection test response status:", response.status);
        return response.text();
    })
    .then(text => {
        console.log("Connection test response:", text.substring(0, 100));
    })
    .catch(error => {
        console.error("Connection test failed:", error);
    });
}

// Call this at the end of your initialization
testConnection();

        // Game Variables
        let score = 0;
        let timeLeft = 60;
        let lives = 3;
        let level = 1;
        let wordsSolved = 0;
        let totalAttempts = 0;
        let correctAttempts = 0;
        let gameActive = true;
        let gameTimer;
        let currentWord = null;
        let gameMode = 'definition'; // 'definition' or 'spelling'
        let words = <?php echo json_encode($word_list); ?>;
        let usedWords = [];
        
        // DOM Elements
        const scoreEl = document.getElementById('score');
        const timeEl = document.getElementById('time');
        const livesEl = document.getElementById('lives');
        const levelEl = document.getElementById('level');
        const wordTextEl = document.getElementById('wordText');
        const wordCategoryEl = document.getElementById('wordCategory');
        const definitionTextEl = document.getElementById('definitionText');
        const exampleTextEl = document.getElementById('exampleText');
        const wordInputEl = document.getElementById('wordInput');
        const submitBtn = document.getElementById('submitBtn');
        const optionsGrid = document.getElementById('optionsGrid');
        const hintBtn = document.getElementById('hintBtn');
        const skipBtn = document.getElementById('skipBtn');
        const restartBtn = document.getElementById('restartBtn');
        const gameOverModal = document.getElementById('gameOverModal');
        const finalScoreEl = document.getElementById('finalScore');
        const finalLevelEl = document.getElementById('finalLevel');
        const wordsSolvedEl = document.getElementById('wordsSolved');
        const accuracyEl = document.getElementById('accuracy');
        const menuBtn = document.getElementById('menuBtn');
        const playAgainBtn = document.getElementById('playAgainBtn');
        const leaderboardEl = document.getElementById('leaderboard');
        const newRecordMessage = document.getElementById('newRecordMessage');
        const progressText = document.getElementById('progressText');
        const progressBar = document.getElementById('progressBar');
        const modeDefinitionBtn = document.getElementById('modeDefinition');
        const modeSpellingBtn = document.getElementById('modeSpelling');

        // Student data
        const currentStudent = {
            id: <?php echo $student_id; ?>,
            username: '<?php echo $student_username; ?>',
            name: '<?php echo $student_name; ?>',
            current_high_score: <?php echo $current_high_score; ?>
        };

        // Initialize Game
        function initGame() {
            loadNewWord();
            startTimer();
            loadLeaderboard();
            updateProgress();
        }

        // Load a new word
        function loadNewWord() {
            if (words.length === 0) {
                endGame();
                return;
            }
            
            // Filter out used words
            const availableWords = words.filter(word => !usedWords.includes(word.word));
            
            if (availableWords.length === 0) {
                // Reset used words if all have been used
                usedWords = [];
                return loadNewWord();
            }
            
            // Get random word
            const randomIndex = Math.floor(Math.random() * availableWords.length);
            currentWord = availableWords[randomIndex];
            usedWords.push(currentWord.word);
            
            // Update display based on game mode
            updateDisplayForMode();
            
            // Clear input
            wordInputEl.value = '';
            wordInputEl.focus();
            
            // Clear options
            optionsGrid.innerHTML = '';
            
            // Generate multiple choice options (50% chance)
            if (Math.random() < 0.5) {
                generateMultipleChoice();
            }
        }

        // Update display based on game mode
        function updateDisplayForMode() {
            switch(gameMode) {
                case 'definition':
                    wordTextEl.textContent = '? ? ? ?';
                    definitionTextEl.textContent = currentWord.definition;
                    exampleTextEl.textContent = currentWord.example || 'No example available.';
                    wordCategoryEl.textContent = currentWord.category || 'Vocabulary';
                    break;
                    
                case 'spelling':
                    // Show scrambled word
                    const scrambled = scrambleWord(currentWord.word);
                    wordTextEl.textContent = scrambled;
                    definitionTextEl.textContent = currentWord.definition;
                    exampleTextEl.textContent = currentWord.example || 'No example available.';
                    wordCategoryEl.textContent = 'Spelling Challenge';
                    break;
            }
        }

        // Scramble a word
        function scrambleWord(word) {
            const letters = word.split('');
            for (let i = letters.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [letters[i], letters[j]] = [letters[j], letters[i]];
            }
            return letters.join('').toUpperCase();
        }

        // Generate multiple choice options
        function generateMultipleChoice() {
            const options = [currentWord.word];
            
            // Add 3 wrong options
            const wrongWords = words.filter(w => w.word !== currentWord.word);
            for (let i = 0; i < 3 && wrongWords.length > 0; i++) {
                const randomIndex = Math.floor(Math.random() * wrongWords.length);
                options.push(wrongWords[randomIndex].word);
                wrongWords.splice(randomIndex, 1);
            }
            
            // Shuffle options
            options.sort(() => Math.random() - 0.5);
            
            // Create buttons
            options.forEach(option => {
                const button = document.createElement('button');
                button.className = 'option-btn';
                button.textContent = option;
                button.addEventListener('click', () => {
                    if (!gameActive) return;
                    checkAnswer(option);
                });
                optionsGrid.appendChild(button);
            });
        }

        // Start timer
        function startTimer() {
            clearInterval(gameTimer);
            gameTimer = setInterval(() => {
                if (!gameActive) return;
                
                timeLeft--;
                timeEl.textContent = timeLeft;
                
                if (timeLeft <= 0) {
                    endGame();
                }
                
                // Warning when time is low
                if (timeLeft <= 10) {
                    timeEl.style.color = timeEl.style.color === 'var(--danger)' ? 'white' : 'var(--danger)';
                }
            }, 1000);
        }

        // Update progress display
        function updateProgress() {
            const progress = Math.min(wordsSolved, 10);
            progressText.textContent = `${progress}/10`;
            progressBar.style.width = `${(progress / 10) * 100}%`;
        }

        // Check answer
        function checkAnswer(userAnswer) {
            if (!gameActive) return;
            
            totalAttempts++;
            const normalizedAnswer = userAnswer.trim().toLowerCase();
            const correctAnswer = currentWord.word.toLowerCase();
            
            // Disable all option buttons
            document.querySelectorAll('.option-btn').forEach(btn => {
                btn.disabled = true;
                if (btn.textContent.toLowerCase() === correctAnswer) {
                    btn.classList.add('correct');
                } else if (btn.textContent.toLowerCase() === normalizedAnswer) {
                    btn.classList.add('wrong');
                }
            });
            
            if (normalizedAnswer === correctAnswer) {
                // Correct answer
                correctAttempts++;
                wordsSolved++;
                
                // Calculate points
                let points = 10;
                if (gameMode === 'spelling') points = 15;
                if (level >= 5) points += 5;
                
                score += points;
                
                // Show feedback
                showFeedback('Correct! +' + points, 'correct');
                
                // Check for level up
                if (wordsSolved % 5 === 0) {
                    level++;
                    levelEl.textContent = level;
                    showFeedback('Level Up!', 'correct');
                    timeLeft += 30; // Time bonus
                }
                
                updateProgress();
            } else {
                // Wrong answer
                lives--;
                livesEl.textContent = lives;
                
                showFeedback('Wrong! -1 Life', 'wrong');
                
                // Show correct answer
                const originalText = wordTextEl.textContent;
                wordTextEl.textContent = currentWord.word.toUpperCase();
                wordTextEl.style.color = 'var(--success)';
                
                setTimeout(() => {
                    wordTextEl.textContent = originalText;
                    wordTextEl.style.color = '';
                }, 2000);
                
                if (lives <= 0) {
                    endGame();
                    return;
                }
            }
            
            // Update score
            scoreEl.textContent = score;
            
            // Load new word after delay
            setTimeout(() => {
                if (gameActive) {
                    loadNewWord();
                }
            }, 2000);
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

        // Use hint
        function useHint() {
            if (score < 10) {
                showFeedback('Need 10 points for hint!', 'wrong');
                return;
            }
            
            score -= 10;
            scoreEl.textContent = score;
            
            if (gameMode === 'spelling') {
                // For spelling mode, reveal the correct word
                const correctWord = currentWord.word.toUpperCase();
                let revealed = '';
                
                for (let i = 0; i < correctWord.length; i++) {
                    if (i === 0 || i === correctWord.length - 1) {
                        revealed += `<span style="color: var(--accent); font-weight: bold;">${correctWord[i]}</span>`;
                    } else {
                        revealed += '?';
                    }
                }
                
                wordTextEl.innerHTML = revealed;
            } else {
                // For definition mode, show first and last letter
                const firstLetter = currentWord.word[0].toUpperCase();
                const lastLetter = currentWord.word[currentWord.word.length - 1].toUpperCase();
                hintBtn.innerHTML = `<i class="fas fa-lightbulb"></i> Hint: ${firstLetter}...${lastLetter}`;
            }
            
            showFeedback('Hint Used! -10 points', 'warning');
        }

        // Skip word
        function skipWord() {
            if (!gameActive) return;
            
            showFeedback('Word Skipped!', 'warning');
            loadNewWord();
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
        // Save score to database
function saveScore() {
    const accuracy = totalAttempts > 0 ? Math.round((correctAttempts / totalAttempts) * 100) : 0;
    const playTime = 60 - timeLeft;
    
    console.log("Saving score:", {
        score: score,
        level: level,
        wordsSolved: wordsSolved,
        accuracy: accuracy,
        playTime: playTime
    });
    
    const formData = new FormData();
    formData.append('student_id', currentStudent.id);
    formData.append('game_name', 'word-master');
    formData.append('score', score);
    formData.append('level', level);
    formData.append('problems_solved', wordsSolved);
    formData.append('accuracy', accuracy);
    formData.append('play_time', playTime);
    
    // Debug: Log FormData contents
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    
    fetch('save_score.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log("Response status:", response.status);
        return response.json();
    })
    .then(data => {
        console.log("Save response:", data);
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
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        // Try alternative method if fetch fails
        saveScoreAlternative();
    });
}

// Alternative method using XMLHttpRequest
function saveScoreAlternative() {
    const accuracy = totalAttempts > 0 ? Math.round((correctAttempts / totalAttempts) * 100) : 0;
    const playTime = 60 - timeLeft;
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'save_score.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const data = JSON.parse(xhr.responseText);
                console.log("XHR Save response:", data);
                if (data.success && data.is_new_high) {
                    newRecordMessage.style.display = 'block';
                }
                loadLeaderboard();
            } catch (e) {
                console.error("Failed to parse response:", e);
            }
        }
    };
    
    const params = new URLSearchParams({
        student_id: currentStudent.id,
        game_name: 'word-master',
        score: score,
        level: level,
        problems_solved: wordsSolved,
        accuracy: accuracy,
        play_time: playTime
    });
    
    console.log("Sending via XHR:", params.toString());
    xhr.send(params.toString());
}

        // End game
        function endGame() {
            gameActive = false;
            clearInterval(gameTimer);
            
            const accuracy = totalAttempts > 0 ? Math.round((correctAttempts / totalAttempts) * 100) : 0;
            
            finalScoreEl.textContent = score.toLocaleString();
            finalLevelEl.textContent = level;
            wordsSolvedEl.textContent = wordsSolved;
            accuracyEl.textContent = accuracy + '%';
            
            saveScore();
            
            setTimeout(() => {
                gameOverModal.classList.add('active');
            }, 1000);
        }

        // Restart game
        function restartGame() {
            score = 0;
            timeLeft = 60;
            lives = 3;
            level = 1;
            wordsSolved = 0;
            totalAttempts = 0;
            correctAttempts = 0;
            usedWords = [];
            gameActive = true;
            
            scoreEl.textContent = score;
            timeEl.textContent = timeLeft;
            livesEl.textContent = lives;
            levelEl.textContent = level;
            timeEl.style.color = 'white';
            
            gameOverModal.classList.remove('active');
            updateProgress();
            loadNewWord();
            startTimer();
        }

        // Change game mode
        function changeGameMode(mode) {
            gameMode = mode;
            
            modeDefinitionBtn.classList.remove('active');
            modeSpellingBtn.classList.remove('active');
            
            if (mode === 'definition') {
                modeDefinitionBtn.classList.add('active');
            } else {
                modeSpellingBtn.classList.add('active');
            }
            
            if (currentWord) {
                updateDisplayForMode();
            }
        }

        // Event Listeners
        submitBtn.addEventListener('click', () => {
            if (wordInputEl.value.trim()) {
                checkAnswer(wordInputEl.value);
            }
        });

        wordInputEl.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && wordInputEl.value.trim()) {
                checkAnswer(wordInputEl.value);
            }
        });

        hintBtn.addEventListener('click', useHint);
        skipBtn.addEventListener('click', skipWord);
        restartBtn.addEventListener('click', restartGame);
        playAgainBtn.addEventListener('click', restartGame);
        menuBtn.addEventListener('click', () => {
            window.location.href = 'games.php';
        });

        modeDefinitionBtn.addEventListener('click', () => changeGameMode('definition'));
        modeSpellingBtn.addEventListener('click', () => changeGameMode('spelling'));

        // Initialize game
        initGame();
    </script>
</body>
</html>