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
    $student_username = $_SESSION['username'] ?? 'Student';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Games • Nexa</title>
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
            background: linear-gradient(135deg, #f0f4ff 0%, #f8fafc 100%);
            color: var(--dark);
            line-height: 1.5;
            height: 100vh;
            overflow: hidden;
        }

        /* Main Container */
        .app {
            display: grid;
            grid-template-columns: 280px 1fr;
            grid-template-rows: 72px 1fr;
            height: 100vh;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            grid-column: 1 / -1;
            background: var(--white);
            border-bottom: 1px solid var(--gray-300);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 50;
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
            color: var(--dark);
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: var(--gray-100);
            border-radius: var(--radius);
            border: 1px solid var(--gray-300);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--accent), #f97316);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 0.9rem;
        }

        .user-details h4 {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--dark);
        }

        .user-details p {
            font-size: 0.8rem;
            color: var(--gray-600);
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--white);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            color: var(--gray-700);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .back-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Sidebar */
        .sidebar {
            background: var(--white);
            border-right: 1px solid var(--gray-300);
            padding: 24px 0;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow-y: auto;
        }

        .sidebar-section {
            padding: 0 24px;
            margin-bottom: 24px;
        }

        .sidebar-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray-600);
            margin-bottom: 12px;
        }

        .nav-items {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--gray-700);
            text-decoration: none;
            border-radius: var(--radius);
            transition: var(--transition);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .nav-item:hover {
            background: var(--gray-100);
            color: var(--dark);
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(37, 99, 235, 0.05));
            color: var(--primary);
            border-left: 3px solid var(--primary);
        }

        .nav-item i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            background: var(--gray-100);
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }

        /* Page Header */
        .page-header {
            background: var(--white);
            border-bottom: 1px solid var(--gray-300);
            padding: 24px 32px;
        }

        .page-title h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .page-title p {
            font-size: 1rem;
            color: var(--gray-600);
            max-width: 600px;
        }

        /* Games Container */
        .games-container {
            flex: 1;
            padding: 32px;
            overflow-y: auto;
        }

        /* Games Grid */
        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        /* Game Card */
        .game-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-300);
            overflow: hidden;
            transition: var(--transition);
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .game-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
        }

        .game-header {
            padding: 20px;
            border-bottom: 1px solid var(--gray-300);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .game-icon {
            width: 60px;
            height: 60px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.5rem;
        }

        .game-icon.math { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); }
        .game-icon.science { background: linear-gradient(135deg, var(--secondary), #059669); }
        .game-icon.english { background: linear-gradient(135deg, var(--accent), #d97706); }
        .game-icon.puzzle { background: linear-gradient(135deg, var(--purple), #7c3aed); }
        .game-icon.memory { background: linear-gradient(135deg, var(--pink), #db2777); }
        .game-icon.quiz { background: linear-gradient(135deg, var(--cyan), #0891b2); }

        .game-info h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .game-info p {
            font-size: 0.9rem;
            color: var(--gray-600);
        }

        .game-body {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .game-description {
            font-size: 0.95rem;
            color: var(--gray-700);
            line-height: 1.6;
            margin-bottom: 20px;
            flex: 1;
        }

        .game-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 16px;
            border-top: 1px solid var(--gray-300);
        }

        .game-tags {
            display: flex;
            gap: 6px;
        }

        .game-tag {
            padding: 4px 10px;
            background: var(--gray-100);
            border: 1px solid var(--gray-300);
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .game-rating {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.9rem;
            color: var(--gray-700);
        }

        .game-rating i {
            color: var(--accent);
        }

        .game-footer {
            padding: 16px 20px;
            background: var(--gray-100);
            border-top: 1px solid var(--gray-300);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .game-stats {
            display: flex;
            gap: 16px;
        }

        .game-stat {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: var(--gray-600);
        }

        .game-stat i {
            width: 16px;
            color: var(--gray-500);
        }

        .play-btn {
            padding: 8px 20px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .play-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Categories */
        .categories {
            margin-bottom: 32px;
        }

        .categories-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 16px;
        }

        .category-filters {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .category-btn {
            padding: 8px 20px;
            background: var(--white);
            border: 1px solid var(--gray-300);
            border-radius: 20px;
            font-size: 0.9rem;
            color: var(--gray-700);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .category-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .category-btn.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border-color: transparent;
        }

        /* Featured Games */
        .featured-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            color: var(--primary);
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-lg);
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.25rem;
        }

        .stat-info h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 4px;
            font-family: 'Space Grotesk', sans-serif;
        }

        .stat-info p {
            font-size: 0.9rem;
            color: var(--gray-600);
        }

        /* Game Modal */
        .game-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
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
            background: var(--white);
            border-radius: var(--radius-lg);
            max-width: 900px;
            width: 100%;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: 24px 32px;
            border-bottom: 1px solid var(--gray-300);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
        }

        .close-modal {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gray-100);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            color: var(--gray-700);
            cursor: pointer;
            transition: var(--transition);
        }

        .close-modal:hover {
            background: var(--danger);
            border-color: var(--danger);
            color: var(--white);
        }

        .modal-body {
            padding: 32px;
            flex: 1;
            overflow-y: auto;
        }

        .game-preview {
            margin-bottom: 24px;
        }

        .game-preview img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: var(--radius);
        }

        .game-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .detail-group h4 {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .detail-group p {
            color: var(--gray-600);
            line-height: 1.6;
        }

        .modal-footer {
            padding: 24px 32px;
            border-top: 1px solid var(--gray-300);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 16px;
        }

        .secondary-btn {
            padding: 10px 20px;
            background: var(--gray-100);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            color: var(--gray-700);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .secondary-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .primary-btn {
            padding: 10px 24px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .primary-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Scrollbar */
        .games-container::-webkit-scrollbar {
            width: 6px;
        }

        .games-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .games-container::-webkit-scrollbar-thumb {
            background: var(--gray-400);
            border-radius: 3px;
        }

        .games-container::-webkit-scrollbar-thumb:hover {
            background: var(--gray-500);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .app {
                grid-template-columns: 1fr;
                grid-template-rows: 72px 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .games-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .app {
                grid-template-rows: 64px 1fr;
            }
            
            .header {
                padding: 0 16px;
            }
            
            .logo span {
                display: none;
            }
            
            .user-details {
                display: none;
            }
            
            .page-header {
                padding: 20px 24px;
            }
            
            .page-title h1 {
                font-size: 1.5rem;
            }
            
            .games-container {
                padding: 20px;
            }
            
            .games-grid {
                grid-template-columns: 1fr;
            }
            
            .game-details {
                grid-template-columns: 1fr;
            }
            
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .game-header {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }
            
            .game-meta {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
            
            .game-footer {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app">
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <a href="student-dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Dashboard
                </a>
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <span>Nexa Games</span>
                </div>
            </div>
            
            <div class="header-actions">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="user-details">
                        <h4><?php echo htmlspecialchars($student_name); ?></h4>
                        <p>Class <?php echo htmlspecialchars($student_class); ?></p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-section">
                <h3 class="sidebar-title">Navigation</h3>
                <div class="nav-items">
                    <a href="student-dashboard.php" class="nav-item">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                    <a href="ai-tutor.php" class="nav-item">
                        <i class="fas fa-robot"></i>
                        AI Tutor
                    </a>
                    <a href="games.php" class="nav-item active">
                        <i class="fas fa-gamepad"></i>
                        Learning Games
                    </a>
                    <a href="learning-resources.php" class="nav-item">
                        <i class="fas fa-book"></i>
                        Resources
                    </a>
                    <a href="progress.php" class="nav-item">
                        <i class="fas fa-chart-line"></i>
                        Progress
                    </a>
                </div>
            </div>
            
            <div class="sidebar-section">
                <h3 class="sidebar-title">Game Categories</h3>
                <div class="nav-items">
                    <a href="#" class="nav-item category-link" data-category="all">
                        <i class="fas fa-th"></i>
                        All Games
                    </a>
                    <a href="#" class="nav-item category-link" data-category="math">
                        <i class="fas fa-calculator"></i>
                        Math Games
                    </a>
                    <a href="#" class="nav-item category-link" data-category="science">
                        <i class="fas fa-flask"></i>
                        Science Games
                    </a>
                    <a href="#" class="nav-item category-link" data-category="english">
                        <i class="fas fa-book"></i>
                        English Games
                    </a>
                    <a href="#" class="nav-item category-link" data-category="puzzle">
                        <i class="fas fa-puzzle-piece"></i>
                        Puzzles
                    </a>
                </div>
            </div>
            
            <div class="sidebar-section">
                <h3 class="sidebar-title">Your Stats</h3>
                <div class="stats-cards" style="grid-template-columns: 1fr; gap: 8px;">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-gamepad"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="gamesPlayed">0</h3>
                            <p>Games Played</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="highScore">0</h3>
                            <p>High Score</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div class="page-title">
                    <h1>🎮 Learning Games</h1>
                    <p>Learn through play! Choose from our collection of educational games designed to make learning fun and engaging.</p>
                </div>
            </div>
            
            <div class="games-container">
                <!-- Stats Overview -->
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark));">
                            <i class="fas fa-gamepad"></i>
                        </div>
                        <div class="stat-info">
                            <h3>24</h3>
                            <p>Total Games</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, var(--secondary), #059669);">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="userScore">0</h3>
                            <p>Your Score</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, var(--accent), #d97706);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="playTime">0</h3>
                            <p>Hours Played</p>
                        </div>
                    </div>
                </div>
                
                <!-- Featured Games -->
                <div class="featured-section">
                    <h2 class="section-title">
                        <i class="fas fa-star"></i>
                        Featured Games
                    </h2>
                    
                    <div class="games-grid">
                        <!-- Math Blaster Game -->
                        <div class="game-card" data-game="math-blaster" data-category="math">
                            <div class="game-header">
                                <div class="game-icon math">
                                    <i class="fas fa-calculator"></i>
                                </div>
                                <div class="game-info">
                                    <h3>Math Blaster</h3>
                                    <p>Grade 4-6 • Math</p>
                                </div>
                            </div>
                            
                            <div class="game-body">
                                <p class="game-description">
                                    Blast through math problems in this exciting space adventure. Solve equations, complete patterns, and earn power-ups!
                                </p>
                                
                                <div class="game-meta">
                                    <div class="game-tags">
                                        <span class="game-tag">Arithmetic</span>
                                        <span class="game-tag">Patterns</span>
                                    </div>
                                    <div class="game-rating">
                                        <i class="fas fa-star"></i>
                                        <span>4.8</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="game-footer">
                                <div class="game-stats">
                                    <div class="game-stat">
                                        <i class="fas fa-users"></i>
                                        <span>1.2k plays</span>
                                    </div>
                                    <div class="game-stat">
                                        <i class="fas fa-clock"></i>
                                        <span>15 min avg</span>
                                    </div>
                                </div>
                                <button class="play-btn play-game-btn">
                                    <i class="fas fa-play"></i>
                                    Play Now
                                </button>
                            </div>
                        </div>
                        
                        <!-- Science Explorer Game -->
                        <div class="game-card" data-game="science-explorer" data-category="science">
                            <div class="game-header">
                                <div class="game-icon science">
                                    <i class="fas fa-flask"></i>
                                </div>
                                <div class="game-info">
                                    <h3>Science Explorer</h3>
                                    <p>Grade 5-7 • Science</p>
                                </div>
                            </div>
                            
                            <div class="game-body">
                                <p class="game-description">
                                    Explore the wonders of science through interactive experiments and challenges. Learn about physics, chemistry, and biology.
                                </p>
                                
                                <div class="game-meta">
                                    <div class="game-tags">
                                        <span class="game-tag">Physics</span>
                                        <span class="game-tag">Chemistry</span>
                                    </div>
                                    <div class="game-rating">
                                        <i class="fas fa-star"></i>
                                        <span>4.6</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="game-footer">
                                <div class="game-stats">
                                    <div class="game-stat">
                                        <i class="fas fa-users"></i>
                                        <span>890 plays</span>
                                    </div>
                                    <div class="game-stat">
                                        <i class="fas fa-clock"></i>
                                        <span>20 min avg</span>
                                    </div>
                                </div>
                                <button class="play-btn play-game-btn">
                                    <i class="fas fa-play"></i>
                                    Play Now
                                </button>
                            </div>
                        </div>
                        
                        <!-- Word Master Game -->
                        <div class="game-card" data-game="word-master" data-category="english">
                            <div class="game-header">
                                <div class="game-icon english">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div class="game-info">
                                    <h3>Word Master</h3>
                                    <p>Grade 3-5 • English</p>
                                </div>
                            </div>
                            
                            <div class="game-body">
                                <p class="game-description">
                                    Master vocabulary, spelling, and grammar through fun word puzzles and challenges. Expand your language skills!
                                </p>
                                
                                <div class="game-meta">
                                    <div class="game-tags">
                                        <span class="game-tag">Vocabulary</span>
                                        <span class="game-tag">Grammar</span>
                                    </div>
                                    <div class="game-rating">
                                        <i class="fas fa-star"></i>
                                        <span>4.9</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="game-footer">
                                <div class="game-stats">
                                    <div class="game-stat">
                                        <i class="fas fa-users"></i>
                                        <span>2.1k plays</span>
                                    </div>
                                    <div class="game-stat">
                                        <i class="fas fa-clock"></i>
                                        <span>10 min avg</span>
                                    </div>
                                </div>
                                <button class="play-btn play-game-btn">
                                    <i class="fas fa-play"></i>
                                    Play Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- All Games -->
                <div class="featured-section">
                    <h2 class="section-title">
                        <i class="fas fa-th"></i>
                        All Games
                    </h2>
                    
                    <div class="categories">
                        <div class="categories-title">Filter by Category:</div>
                        <div class="category-filters">
                            <button class="category-btn active" data-category="all">
                                <i class="fas fa-th"></i>
                                All Games
                            </button>
                            <button class="category-btn" data-category="math">
                                <i class="fas fa-calculator"></i>
                                Math
                            </button>
                            <button class="category-btn" data-category="science">
                                <i class="fas fa-flask"></i>
                                Science
                            </button>
                            <button class="category-btn" data-category="english">
                                <i class="fas fa-book"></i>
                                English
                            </button>
                            <button class="category-btn" data-category="puzzle">
                                <i class="fas fa-puzzle-piece"></i>
                                Puzzle
                            </button>
                            <button class="category-btn" data-category="memory">
                                <i class="fas fa-brain"></i>
                                Memory
                            </button>
                        </div>
                    </div>
                    
                    <div class="games-grid" id="gamesGrid">
                        <!-- Games will be loaded dynamically -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Game Modal -->
    <div class="game-modal" id="gameModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalGameTitle">Game Title</h2>
                <button class="close-modal" id="closeModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="game-preview">
                    <div id="gamePreviewContent" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); height: 200px; border-radius: var(--radius); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                        <i class="fas fa-gamepad"></i>
                    </div>
                </div>
                
                <div class="game-details">
                    <div class="detail-group">
                        <h4>Description</h4>
                        <p id="modalGameDescription">Game description will appear here.</p>
                    </div>
                    
                    <div class="detail-group">
                        <h4>Details</h4>
                        <p id="modalGameDetails">Grade level: 4-6<br>Duration: 15 minutes<br>Skills: Math, Problem Solving</p>
                    </div>
                    
                    <div class="detail-group">
                        <h4>Learning Objectives</h4>
                        <p id="modalGameObjectives">• Improve math skills<br>• Enhance problem-solving abilities<br>• Develop logical thinking</p>
                    </div>
                    
                    <div class="detail-group">
                        <h4>High Scores</h4>
                        <p id="modalGameScores">Your best: 0<br>Class average: 1,250<br>Top score: 2,500</p>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="secondary-btn" id="cancelBtn">
                    Cancel
                </button>
                <button class="primary-btn" id="startGameBtn">
                    <i class="fas fa-play"></i>
                    Start Game
                </button>
            </div>
        </div>
    </div>

    <script>
        // Game Data
        const games = [
            {
                id: 'math-blaster',
                title: 'Math Blaster',
                category: 'math',
                grade: 'Grade 4-6',
                subject: 'Math',
                icon: 'fa-calculator',
                iconClass: 'math',
                description: 'Blast through math problems in this exciting space adventure. Solve equations, complete patterns, and earn power-ups!',
                tags: ['Arithmetic', 'Patterns'],
                rating: 4.8,
                plays: 1200,
                avgTime: '15 min',
                skills: 'Math, Problem Solving',
                objectives: '• Improve math skills<br>• Enhance problem-solving abilities<br>• Develop logical thinking',
                link: 'games/math-blaster.php'
            },
            {
                id: 'science-explorer',
                title: 'Science Explorer',
                category: 'science',
                grade: 'Grade 5-7',
                subject: 'Science',
                icon: 'fa-flask',
                iconClass: 'science',
                description: 'Explore the wonders of science through interactive experiments and challenges. Learn about physics, chemistry, and biology.',
                tags: ['Physics', 'Chemistry'],
                rating: 4.6,
                plays: 890,
                avgTime: '20 min',
                skills: 'Science, Observation',
                objectives: '• Understand scientific concepts<br>• Develop observation skills<br>• Learn experimental methods',
                link: 'games/science-explorer.php'
            },
            {
                id: 'word-master',
                title: 'Word Master',
                category: 'english',
                grade: 'Grade 3-5',
                subject: 'English',
                icon: 'fa-book',
                iconClass: 'english',
                description: 'Master vocabulary, spelling, and grammar through fun word puzzles and challenges. Expand your language skills!',
                tags: ['Vocabulary', 'Grammar'],
                rating: 4.9,
                plays: 2100,
                avgTime: '10 min',
                skills: 'Language, Spelling',
                objectives: '• Expand vocabulary<br>• Improve spelling<br>• Master grammar rules',
                link: 'games/word-master.php'
            },
            {
                id: 'memory-match',
                title: 'Memory Match',
                category: 'memory',
                grade: 'Grade 2-4',
                subject: 'Memory',
                icon: 'fa-brain',
                iconClass: 'memory',
                description: 'Train your memory with matching card games. Match pairs of words, images, or concepts to win!',
                tags: ['Memory', 'Concentration'],
                rating: 4.7,
                plays: 1500,
                avgTime: '8 min',
                skills: 'Memory, Focus',
                objectives: '• Improve memory retention<br>• Enhance concentration<br>• Develop pattern recognition',
                link: 'games/memory-match.php'
            },
            {
                id: 'math-puzzle',
                title: 'Math Puzzle Adventure',
                category: 'puzzle',
                grade: 'Grade 4-6',
                subject: 'Math',
                icon: 'fa-puzzle-piece',
                iconClass: 'puzzle',
                description: 'Solve challenging math puzzles to progress through exciting levels. Each puzzle teaches a new concept!',
                tags: ['Puzzles', 'Logic'],
                rating: 4.5,
                plays: 950,
                avgTime: '12 min',
                skills: 'Logic, Math',
                objectives: '• Develop logical thinking<br>• Apply math concepts<br>• Solve complex problems',
                link: 'games/math-puzzle.php'
            },
            {
                id: 'science-quiz',
                title: 'Science Quiz Challenge',
                category: 'science',
                grade: 'Grade 6-8',
                subject: 'Science',
                icon: 'fa-question-circle',
                iconClass: 'quiz',
                description: 'Test your science knowledge with timed quizzes. Covering biology, chemistry, physics, and earth science.',
                tags: ['Quiz', 'Trivia'],
                rating: 4.4,
                plays: 1100,
                avgTime: '18 min',
                skills: 'Knowledge, Speed',
                objectives: '• Test science knowledge<br>• Improve recall speed<br>• Learn new facts',
                link: 'games/science-quiz.php'
            },
            {
                id: 'grammar-quest',
                title: 'Grammar Quest',
                category: 'english',
                grade: 'Grade 4-6',
                subject: 'English',
                icon: 'fa-keyboard',
                iconClass: 'english',
                description: 'Journey through a magical land while learning grammar rules. Correct sentences to defeat monsters!',
                tags: ['Grammar', 'Adventure'],
                rating: 4.8,
                plays: 1300,
                avgTime: '14 min',
                skills: 'Grammar, Writing',
                objectives: '• Master grammar rules<br>• Improve writing skills<br>• Learn sentence structure',
                link: 'games/grammar-quest.php'
            },
            {
                id: 'geometry-builder',
                title: 'Geometry Builder',
                category: 'math',
                grade: 'Grade 5-7',
                subject: 'Math',
                icon: 'fa-shapes',
                iconClass: 'math',
                description: 'Build and create with geometric shapes. Learn about angles, shapes, and spatial relationships.',
                tags: ['Geometry', 'Shapes'],
                rating: 4.6,
                plays: 800,
                avgTime: '16 min',
                skills: 'Geometry, Creativity',
                objectives: '• Understand geometric shapes<br>• Learn spatial relationships<br>• Develop creative thinking',
                link: 'games/geometry-builder.php'
            }
        ];

        // DOM Elements
        const gamesGrid = document.getElementById('gamesGrid');
        const gameModal = document.getElementById('gameModal');
        const modalGameTitle = document.getElementById('modalGameTitle');
        const modalGameDescription = document.getElementById('modalGameDescription');
        const modalGameDetails = document.getElementById('modalGameDetails');
        const modalGameObjectives = document.getElementById('modalGameObjectives');
        const modalGameScores = document.getElementById('modalGameScores');
        const gamePreviewContent = document.getElementById('gamePreviewContent');
        const closeModal = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const startGameBtn = document.getElementById('startGameBtn');
        const categoryButtons = document.querySelectorAll('.category-btn, .category-link');
        const gamesPlayedEl = document.getElementById('gamesPlayed');
        const highScoreEl = document.getElementById('highScore');
        const userScoreEl = document.getElementById('userScore');
        const playTimeEl = document.getElementById('playTime');

        // Current selected game
        let currentGame = null;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadGames();
            loadUserStats();
            setupEventListeners();
        });

        // Load games into grid
        function loadGames() {
            gamesGrid.innerHTML = '';
            
            games.forEach(game => {
                const gameCard = createGameCard(game);
                gamesGrid.appendChild(gameCard);
            });
        }

        // Create game card element
        function createGameCard(game) {
            const div = document.createElement('div');
            div.className = 'game-card';
            div.dataset.game = game.id;
            div.dataset.category = game.category;
            
            div.innerHTML = `
                <div class="game-header">
                    <div class="game-icon ${game.iconClass}">
                        <i class="fas ${game.icon}"></i>
                    </div>
                    <div class="game-info">
                        <h3>${game.title}</h3>
                        <p>${game.grade} • ${game.subject}</p>
                    </div>
                </div>
                
                <div class="game-body">
                    <p class="game-description">${game.description}</p>
                    
                    <div class="game-meta">
                        <div class="game-tags">
                            ${game.tags.map(tag => `<span class="game-tag">${tag}</span>`).join('')}
                        </div>
                        <div class="game-rating">
                            <i class="fas fa-star"></i>
                            <span>${game.rating}</span>
                        </div>
                    </div>
                </div>
                
                <div class="game-footer">
                    <div class="game-stats">
                        <div class="game-stat">
                            <i class="fas fa-users"></i>
                            <span>${game.plays.toLocaleString()} plays</span>
                        </div>
                        <div class="game-stat">
                            <i class="fas fa-clock"></i>
                            <span>${game.avgTime} avg</span>
                        </div>
                    </div>
                    <button class="play-btn play-game-btn">
                        <i class="fas fa-play"></i>
                        Play Now
                    </button>
                </div>
            `;
            
            return div;
        }

        // Filter games by category
        function filterGames(category) {
            const gameCards = document.querySelectorAll('.game-card');
            
            gameCards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Update active category buttons
            categoryButtons.forEach(btn => {
                if (btn.dataset.category === category) {
                    btn.classList.add('active');
                    if (btn.classList.contains('nav-item')) {
                        btn.classList.add('active');
                    }
                } else {
                    btn.classList.remove('active');
                    if (btn.classList.contains('nav-item')) {
                        btn.classList.remove('active');
                    }
                }
            });
        }

        // Show game modal
        function showGameModal(gameId) {
            const game = games.find(g => g.id === gameId);
            if (!game) return;
            
            currentGame = game;
            
            // Update modal content
            modalGameTitle.textContent = game.title;
            modalGameDescription.textContent = game.description;
            modalGameDetails.textContent = `Grade level: ${game.grade}\nDuration: ${game.avgTime}\nSkills: ${game.skills}`;
            modalGameObjectives.innerHTML = game.objectives;
            
            // Get user stats for this game
            const userStats = JSON.parse(localStorage.getItem(`game_stats_${game.id}`) || '{}');
            const userBest = userStats.highScore || 0;
            const classAvg = Math.floor(game.plays * game.rating * 10);
            const topScore = Math.floor(classAvg * 1.2);
            
            modalGameScores.textContent = `Your best: ${userBest}\nClass average: ${classAvg.toLocaleString()}\nTop score: ${topScore.toLocaleString()}`;
            
            // Update preview
            gamePreviewContent.innerHTML = `
                <div style="text-align: center;">
                    <i class="fas ${game.icon}" style="font-size: 3rem; margin-bottom: 10px;"></i>
                    <h3 style="margin: 0; font-size: 1.5rem;">${game.title}</h3>
                    <p style="margin: 5px 0 0; opacity: 0.9;">${game.grade} • ${game.subject}</p>
                </div>
            `;
            gamePreviewContent.style.background = `linear-gradient(135deg, var(--primary), var(--primary-dark))`;
            
            // Show modal
            gameModal.classList.add('active');
        }

        // Start selected game
        function startGame() {
            if (!currentGame) return;
            
            // Record game play
            const stats = JSON.parse(localStorage.getItem('user_game_stats') || '{}');
            stats.gamesPlayed = (stats.gamesPlayed || 0) + 1;
            stats.lastPlayed = new Date().toISOString();
            localStorage.setItem('user_game_stats', JSON.stringify(stats));
            
            // Update display
            gamesPlayedEl.textContent = stats.gamesPlayed;
            
            // Redirect to game page
            window.location.href = currentGame.link;
        }

        // Load user stats
        function loadUserStats() {
            const stats = JSON.parse(localStorage.getItem('user_game_stats') || '{}');
            
            // Initialize if not exists
            if (!stats.gamesPlayed) {
                stats.gamesPlayed = 0;
                stats.highScore = 0;
                stats.totalScore = 0;
                stats.playTime = 0; // in minutes
            }
            
            // Calculate total play time in hours
            const playTimeHours = Math.floor(stats.playTime / 60);
            
            // Update displays
            gamesPlayedEl.textContent = stats.gamesPlayed;
            highScoreEl.textContent = stats.highScore.toLocaleString();
            userScoreEl.textContent = stats.totalScore.toLocaleString();
            playTimeEl.textContent = playTimeHours;
            
            // Save updated stats
            localStorage.setItem('user_game_stats', JSON.stringify(stats));
        }

        // Setup event listeners
        function setupEventListeners() {
            // Category filter buttons
            categoryButtons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const category = btn.dataset.category;
                    filterGames(category);
                });
            });

            // Game card click events (delegated)
            document.addEventListener('click', (e) => {
                const playBtn = e.target.closest('.play-game-btn');
                const gameCard = e.target.closest('.game-card');
                
                if (playBtn && gameCard) {
                    e.preventDefault();
                    showGameModal(gameCard.dataset.game);
                }
            });

            // Modal controls
            closeModal.addEventListener('click', () => {
                gameModal.classList.remove('active');
            });

            cancelBtn.addEventListener('click', () => {
                gameModal.classList.remove('active');
            });

            startGameBtn.addEventListener('click', startGame);

            // Close modal on background click
            gameModal.addEventListener('click', (e) => {
                if (e.target === gameModal) {
                    gameModal.classList.remove('active');
                }
            });

            // Escape key to close modal
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && gameModal.classList.contains('active')) {
                    gameModal.classList.remove('active');
                }
            });
        }

        // Initial filter
        filterGames('all');
    </script>
</body>
</html>