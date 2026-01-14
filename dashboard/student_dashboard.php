
<?php
    session_start();
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
        header('Location: ../Frontend/login.php');
        exit();
    }
    
    // Get student information
    $student_id = $_SESSION['user_id'];
    $student_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    $student_class = $_SESSION['class'] ?? 'Not specified';
    
    // Database connection
    require_once '../includes/config.php';
    
    // Get user's game stats from database
    $stats_query = "SELECT 
        COUNT(DISTINCT game_name) as total_games_played,
        COALESCE(SUM(score), 0) as total_score,
        COALESCE(SUM(play_time), 0) as total_play_time,
        COUNT(*) as total_games
    FROM game_scores 
    WHERE student_id = ?";
    
    $stmt = mysqli_prepare($conn, $stats_query);
    mysqli_stmt_bind_param($stmt, 'i', $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user_stats = mysqli_fetch_assoc($result) ?? [];
    mysqli_stmt_close($stmt);
    
    // Get recent games played
    $recent_games_query = "SELECT 
        gs.game_name,
        gs.score,
        gs.level,
        gs.accuracy,
        gs.created_at,
        gs.problems_solved,
        CASE 
            WHEN gs.game_name = 'math-blaster' THEN 'Math Blaster'
            WHEN gs.game_name = 'word-master' THEN 'Word Master'
            WHEN gs.game_name = 'memory-match' THEN 'Memory Match'
            ELSE gs.game_name
        END as game_title,
        CASE 
            WHEN gs.game_name = 'math-blaster' THEN 'fa-calculator'
            WHEN gs.game_name = 'word-master' THEN 'fa-book'
            WHEN gs.game_name = 'memory-match' THEN 'fa-brain'
            ELSE 'fa-gamepad'
        END as game_icon
    FROM game_scores gs
    WHERE gs.student_id = ?
    ORDER BY gs.created_at DESC
    LIMIT 3";
    
    $stmt = mysqli_prepare($conn, $recent_games_query);
    mysqli_stmt_bind_param($stmt, 'i', $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $recent_games = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $recent_games[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
    
    // Get leaderboard rank
    $rank_query = "SELECT 
        rank_position
    FROM (
        SELECT 
            s.id,
            s.firstName,
            s.lastName,
            COALESCE(SUM(g.score), 0) as total_score,
            ROW_NUMBER() OVER (ORDER BY COALESCE(SUM(g.score), 0) DESC) as rank_position
        FROM student_details s
        LEFT JOIN game_scores g ON s.id = g.student_id
        GROUP BY s.id, s.firstName, s.lastName
    ) as rankings
    WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $rank_query);
    mysqli_stmt_bind_param($stmt, 'i', $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rank_data = mysqli_fetch_assoc($result);
    $user_rank = $rank_data['rank_position'] ?? 0;
    mysqli_stmt_close($stmt);
    
    // Calculate stats
    $total_score = $user_stats['total_score'] ?? 0;
    $games_played = $user_stats['total_games_played'] ?? 0;
    $total_games = $user_stats['total_games'] ?? 0;
    $play_time_minutes = $user_stats['total_play_time'] ?? 0;
    $play_time_hours = floor($play_time_minutes / 60);
    $play_time_minutes = $play_time_minutes % 60;
    
    // Get achievements count (simplified - you can create an achievements table)
    $achievements_query = "SELECT 
        COUNT(DISTINCT game_name) as achievements
    FROM game_scores 
    WHERE student_id = ? AND score > 50";
    
    $stmt = mysqli_prepare($conn, $achievements_query);
    mysqli_stmt_bind_param($stmt, 'i', $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $achievements_data = mysqli_fetch_assoc($result);
    $achievements_count = $achievements_data['achievements'] ?? 0;
    mysqli_stmt_close($stmt);
    
    // Close connection
    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard • Nexa AI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-light: #6366F1;
            --primary-dark: #1d4ed8;
            --secondary: #10b981;
            --accent: #F59E0B;
            --pink: #EC4899;
            --purple: #8B5CF6;
            --cyan: #06b6d4;
            --black: #111827;
            --dark-gray: #374151;
            --medium-gray: #6B7280;
            --light-gray: #F9FAFB;
            --white: #FFFFFF;
            --border: #E5E7EB;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFF;
            color: var(--black);
            line-height: 1.6;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #4F46E5, #8B5CF6);
            color: var(--white);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 30px 0;
            overflow-y: auto;
            transition: var(--transition);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 0 25px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .logo {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            margin-top: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--accent), var(--pink));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
        }

        .user-info h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .user-info p {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .sidebar-nav {
            padding: 0 20px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 8px;
            transition: var(--transition);
            font-weight: 500;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255, 255, 255, 0.15);
            color: var(--white);
            backdrop-filter: blur(10px);
        }

        .nav-item i {
            width: 24px;
            text-align: center;
            font-size: 1.2rem;
        }

        .nav-label {
            flex: 1;
        }

        .badge {
            background: var(--accent);
            color: var(--black);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 30px;
            min-height: 100vh;
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow);
        }

        .search-bar {
            flex: 1;
            max-width: 400px;
            position: relative;
        }

        .search-bar i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--medium-gray);
        }

        .search-bar input {
            width: 100%;
            padding: 14px 20px 14px 48px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            background: var(--light-gray);
            transition: var(--transition);
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--white);
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-btn, .logout-btn {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-gray);
            border: none;
            color: var(--dark-gray);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .notification-btn:hover, .logout-btn:hover {
            background: var(--primary);
            color: var(--white);
            transform: translateY(-2px);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 20px;
            height: 20px;
            background: var(--accent);
            color: var(--black);
            border-radius: 50%;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary), var(--purple));
            color: var(--white);
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .banner-shapes {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            top: -100px;
            right: -50px;
        }

        .welcome-banner h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .welcome-banner p {
            opacity: 0.9;
            max-width: 600px;
            font-size: 1.1rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--white);
            padding: 30px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-top: 4px solid;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card.blue {
            border-color: var(--primary);
        }

        .stat-card.green {
            border-color: var(--secondary);
        }

        .stat-card.orange {
            border-color: var(--accent);
        }

        .stat-card.pink {
            border-color: var(--pink);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: var(--white);
        }

        .stat-card.blue .stat-icon {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
        }

        .stat-card.green .stat-icon {
            background: linear-gradient(135deg, var(--secondary), #34D399);
        }

        .stat-card.orange .stat-icon {
            background: linear-gradient(135deg, var(--accent), #FBBF24);
        }

        .stat-card.pink .stat-icon {
            background: linear-gradient(135deg, var(--pink), var(--purple));
        }

        .stat-number {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--medium-gray);
            font-size: 0.95rem;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-top: 10px;
        }

        .trend-up {
            color: var(--secondary);
        }

        .trend-down {
            color: #EF4444;
        }

        /* Recent Games Section */
        .games-section {
            margin-bottom: 40px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-header h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .view-all {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .view-all:hover {
            gap: 12px;
        }

        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .game-card {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid var(--border);
        }

        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .game-header {
            padding: 25px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .game-icon {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--white);
        }

        .game-icon.math { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); }
        .game-icon.word { background: linear-gradient(135deg, var(--accent), #d97706); }
        .game-icon.memory { background: linear-gradient(135deg, var(--purple), #7c3aed); }
        .game-icon.other { background: linear-gradient(135deg, var(--cyan), #0891b2); }

        .game-info h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--black);
            margin-bottom: 5px;
        }

        .game-info p {
            font-size: 0.9rem;
            color: var(--medium-gray);
        }

        .game-body {
            padding: 25px;
        }

        .game-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .game-stat {
            text-align: center;
            padding: 15px;
            background: var(--light-gray);
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .game-stat-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--black);
            margin-bottom: 5px;
        }

        .game-stat-label {
            font-size: 0.85rem;
            color: var(--medium-gray);
        }

        .game-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            font-size: 0.9rem;
            color: var(--medium-gray);
        }

        .game-date {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .game-actions {
            display: flex;
            gap: 10px;
        }

        .btn-primary, .btn-secondary {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--purple));
            color: var(--white);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        }

        .btn-secondary {
            background: var(--light-gray);
            color: var(--dark-gray);
            border: 2px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--white);
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Quick Actions */
        .quick-actions {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: var(--shadow);
        }

        .quick-actions h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: var(--black);
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: var(--light-gray);
            border: 2px solid var(--border);
            border-radius: 12px;
            color: var(--dark-gray);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .action-btn:hover {
            background: var(--white);
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .action-btn i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        /* AI Assistant */
        .ai-assistant {
            background: linear-gradient(135deg, var(--black), #1F2937);
            color: var(--white);
            padding: 30px;
            border-radius: 20px;
            margin-top: 40px;
            position: relative;
            overflow: hidden;
        }

        .ai-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent), var(--pink));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .ai-assistant h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .ai-assistant p {
            opacity: 0.9;
            margin-bottom: 20px;
        }

        .ai-input {
            display: flex;
            gap: 10px;
        }

        .ai-input input {
            flex: 1;
            padding: 14px 20px;
            border-radius: 12px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            font-family: 'Inter', sans-serif;
        }

        .ai-input input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .ai-input input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.15);
        }

        .ai-input button {
            padding: 14px 25px;
            background: linear-gradient(135deg, var(--accent), var(--pink));
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .ai-input button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--white);
            border-radius: 16px;
            border: 2px dashed var(--border);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--medium-gray);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: var(--dark-gray);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--medium-gray);
            max-width: 400px;
            margin: 0 auto 20px;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .sidebar {
                width: 250px;
            }
            .main-content {
                margin-left: 250px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .menu-toggle {
                display: block;
                background: none;
                border: none;
                font-size: 1.5rem;
                color: var(--dark-gray);
                cursor: pointer;
            }
        }

        @media (max-width: 768px) {
            .stats-grid, .games-grid {
                grid-template-columns: 1fr;
            }
            .top-bar {
                flex-direction: column;
                gap: 20px;
            }
            .search-bar {
                max-width: 100%;
            }
            .welcome-banner h1 {
                font-size: 2rem;
            }
            .game-stats {
                grid-template-columns: 1fr;
            }
            .action-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-robot"></i>
                </div>
                NEXA AI
            </div>
            
            <div class="user-profile">
                <div class="avatar">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($student_name); ?></h3>
                    <p>Student • Class <?php echo htmlspecialchars($student_class); ?></p>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="#" class="nav-item active">
                <i class="fas fa-home"></i>
                <span class="nav-label">Dashboard</span>
            </a>
            <a href="games.php" class="nav-item">
                <i class="fas fa-gamepad"></i>
                <span class="nav-label">Learning Games</span>
                <span class="badge"><?php echo $games_played; ?></span>
            </a>
            <a href="student_ai_tutor.php" class="nav-item">
                <i class="fas fa-robot"></i>
                <span class="nav-label">AI Tutor</span>
            </a>
            <a href="student_assignment.php" class="nav-item">
                <i class="fas fa-tasks"></i>
                <span class="nav-label">Assignments</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-chart-line"></i>
                <span class="nav-label">Progress</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-trophy"></i>
                <span class="nav-label">Achievements</span>
                <span class="badge"><?php echo $achievements_count; ?></span>
            </a>
            
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                <span class="nav-label">Settings</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search games, lessons, or help...">
            </div>
            
            <div class="top-actions">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge"><?php echo $games_played > 0 ? min($games_played, 9) : '0'; ?></span>
                </button>
                <a href="../auth/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="banner-shapes"></div>
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>! 🎮</h1>
            <p>Ready for some brain training? You've played <?php echo $games_played; ?> games with a total score of <?php echo number_format($total_score); ?> points!</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>#<?php echo $user_rank > 0 ? $user_rank : 'NR'; ?></span>
                    </div>
                </div>
                <div class="stat-number"><?php echo number_format($total_score); ?></div>
                <div class="stat-label">Total Score</div>
                <div class="stat-trend">
                    <span>Rank #<?php echo $user_rank > 0 ? $user_rank : 'Not Ranked'; ?></span>
                </div>
            </div>
            
            <div class="stat-card green">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span><?php echo $total_games; ?> games</span>
                    </div>
                </div>
                <div class="stat-number"><?php echo $games_played; ?></div>
                <div class="stat-label">Games Played</div>
                <div class="stat-trend">
                    <span><?php echo $total_games; ?> sessions total</span>
                </div>
            </div>
            
            <div class="stat-card orange">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>+<?php echo $play_time_minutes; ?>m</span>
                    </div>
                </div>
                <div class="stat-number"><?php echo $play_time_hours; ?>h</div>
                <div class="stat-label">Learning Time</div>
                <div class="stat-trend">
                    <span><?php echo $play_time_minutes; ?> minutes</span>
                </div>
            </div>
            
            <div class="stat-card pink">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>+<?php echo $achievements_count; ?></span>
                    </div>
                </div>
                <div class="stat-number"><?php echo $achievements_count; ?></div>
                <div class="stat-label">Achievements</div>
                <div class="stat-trend">
                    <span>Keep playing to earn more!</span>
                </div>
            </div>
        </div>

        <!-- Recent Games Section -->
        <div class="games-section">
            <div class="section-header">
                <h2>Recent Games</h2>
                <a href="games.php" class="view-all">
                    View All Games
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <?php if (!empty($recent_games)): ?>
                <div class="games-grid">
                    <?php foreach ($recent_games as $game): ?>
                        <?php 
                        // Determine icon class based on game type
                        $icon_class = 'other';
                        if (strpos(strtolower($game['game_title']), 'math') !== false) $icon_class = 'math';
                        if (strpos(strtolower($game['game_title']), 'word') !== false) $icon_class = 'word';
                        if (strpos(strtolower($game['game_title']), 'memory') !== false) $icon_class = 'memory';
                        
                        // Format date
                        $game_date = date('M d, Y', strtotime($game['created_at']));
                        $game_time = date('h:i A', strtotime($game['created_at']));
                        ?>
                        
                        <div class="game-card">
                            <div class="game-header">
                                <div class="game-icon <?php echo $icon_class; ?>">
                                    <i class="fas <?php echo $game['game_icon']; ?>"></i>
                                </div>
                                <div class="game-info">
                                    <h3><?php echo htmlspecialchars($game['game_title']); ?></h3>
                                    <p>Game Session • Level <?php echo $game['level']; ?></p>
                                </div>
                            </div>
                            
                            <div class="game-body">
                                <div class="game-stats">
                                    <div class="game-stat">
                                        <div class="game-stat-value"><?php echo number_format($game['score']); ?></div>
                                        <div class="game-stat-label">Score</div>
                                    </div>
                                    
                                    <div class="game-stat">
                                        <div class="game-stat-value"><?php echo $game['problems_solved']; ?></div>
                                        <div class="game-stat-label">Solved</div>
                                    </div>
                                    
                                    <div class="game-stat">
                                        <div class="game-stat-value"><?php echo $game['accuracy']; ?>%</div>
                                        <div class="game-stat-label">Accuracy</div>
                                    </div>
                                    
                                    <div class="game-stat">
                                        <div class="game-stat-value"><?php echo $game['level']; ?></div>
                                        <div class="game-stat-label">Level</div>
                                    </div>
                                </div>
                                
                                <div class="game-meta">
                                    <div class="game-date">
                                        <i class="far fa-calendar"></i>
                                        <span><?php echo $game_date; ?> at <?php echo $game_time; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-gamepad"></i>
                    <h3>No Games Played Yet</h3>
                    <p>Start your learning journey by playing some educational games!</p>
                    <a href="games.php" class="btn-primary" style="text-decoration: none; margin-top: 20px;">
                        <i class="fas fa-play"></i>
                        Play Games Now
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <a href="games.php" class="action-btn">
                    <i class="fas fa-play-circle"></i>
                    <span>Play New Game</span>
                </a>
                <a href="games.php?category=math" class="action-btn">
                    <i class="fas fa-calculator"></i>
                    <span>Math Games</span>
                </a>
                <a href="games.php?category=english" class="action-btn">
                    <i class="fas fa-book"></i>
                    <span>Word Games</span>
                </a>
                <a href="games.php?category=memory" class="action-btn">
                    <i class="fas fa-brain"></i>
                    <span>Memory Games</span>
                </a>
            </div>
        </div>

        <!-- AI Assistant -->
        <div class="ai-assistant">
            <div class="ai-icon">
                <i class="fas fa-robot"></i>
            </div>
            <h3>Need help? Ask Nexa AI</h3>
            <p>Your personal AI tutor is here to help with homework, explain concepts, or answer questions about your games.</p>
            
            <div class="ai-input">
                <input type="text" placeholder="Ask me anything about math, words, or memory games...">
                <button>
                    <i class="fas fa-paper-plane"></i>
                    Ask
                </button>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
        
        // Update progress bars on page load
        document.addEventListener('DOMContentLoaded', () => {
            const progressBars = document.querySelectorAll('.progress-fill');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 300);
            });
            
            // AI Assistant interaction
            const aiInput = document.querySelector('.ai-input input');
            const aiButton = document.querySelector('.ai-input button');
            
            aiButton.addEventListener('click', () => {
                if (aiInput.value.trim()) {
                    alert(`Nexa AI: "I received your question: "${aiInput.value}". I'm thinking..."`);
                    aiInput.value = '';
                }
            });
            
            aiInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    aiButton.click();
                }
            });
            
            // Notification click
            document.querySelector('.notification-btn').addEventListener('click', () => {
                alert('You have new game notifications!\nCheck your recent games for updates.');
            });
            
            // Game card hover effects
            const gameCards = document.querySelectorAll('.game-card');
            gameCards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0)';
                });
            });
            
            // Auto-refresh stats every 30 seconds (if new games played)
            setInterval(() => {
                // This would typically make an AJAX call to refresh stats
                // For now, just update the time display
                const now = new Date();
                const timeElements = document.querySelectorAll('.game-date');
                timeElements.forEach(el => {
                    if (el.textContent.includes('ago')) {
                        // Update relative time if needed
                    }
                });
            }, 30000);
        });
    </script>
</body>
</html>
