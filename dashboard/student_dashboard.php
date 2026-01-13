 <?php
    session_start();
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
        header('Location: ../Frontend/login.php');
        exit();
    }
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
            --secondary: #10B981;
            --accent: #F59E0B;
            --pink: #EC4899;
            --purple: #8B5CF6;
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

        /* Courses Grid */
        .courses-section {
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

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .course-card {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .course-image {
            height: 180px;
            background: linear-gradient(135deg, var(--primary), var(--purple));
            position: relative;
        }

        .course-category {
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--white);
            color: var(--primary);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .course-content {
            padding: 25px;
        }

        .course-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .course-description {
            color: var(--medium-gray);
            font-size: 0.95rem;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .course-progress {
            margin-bottom: 20px;
        }

        .progress-bar {
            height: 8px;
            background: var(--border);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--purple));
            border-radius: 4px;
            transition: width 1s ease-out;
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: var(--medium-gray);
        }

        .course-actions {
            display: flex;
            gap: 10px;
        }

        .btn-primary, .btn-secondary {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-family: 'Inter', sans-serif;
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

        /* Recent Activity */
        .activity-list {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px 25px;
            border-bottom: 1px solid var(--border);
            transition: var(--transition);
        }

        .activity-item:hover {
            background: var(--light-gray);
        }

        .activity-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: var(--white);
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .activity-time {
            color: var(--medium-gray);
            font-size: 0.9rem;
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
            .stats-grid, .courses-grid {
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
                    <h3><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></h3>
                    <p>Student • Class <?php echo htmlspecialchars($_SESSION['class']); ?></p>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="#" class="nav-item active">
                <i class="fas fa-home"></i>
                <span class="nav-label">Dashboard</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-book-open"></i>
                <span class="nav-label">My Courses</span>
                <span class="badge">3</span>
            </a>
            <a href="games.php" class="nav-item">
                <i class="fas fa-gamepad"></i>
                <span class="nav-label">Learning Games</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-tasks"></i>
                <span class="nav-label">Assignments</span>
                <span class="badge">5</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-chart-line"></i>
                <span class="nav-label">Progress</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-trophy"></i>
                <span class="nav-label">Achievements</span>
            </a>
            <a href="student_ai_tutor.php" class="nav-item">
                <i class="fas fa-robot"></i>
                <span class="nav-label">AI Tutor</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span class="nav-label">Schedule</span>
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
                <input type="text" placeholder="Search for lessons, games, or help...">
            </div>
            
            <div class="top-actions">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </button>
                <a href="../auth/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="banner-shapes"></div>
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>! 👋</h1>
            <p>Ready to continue your learning adventure? You have 3 new assignments and 2 games to play today.</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>12%</span>
                    </div>
                </div>
                <div class="stat-number">85%</div>
                <div class="stat-label">Game Completion</div>
            </div>
            
            <div class="stat-card green">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>8%</span>
                    </div>
                </div>
                <div class="stat-number">92%</div>
                <div class="stat-label">Assignment Score</div>
            </div>
            
            <div class="stat-card orange">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>15%</span>
                    </div>
                </div>
                <div class="stat-number">24</div>
                <div class="stat-label">Badges Earned</div>
            </div>
            
            <div class="stat-card pink">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-trend trend-down">
                        <i class="fas fa-arrow-down"></i>
                        <span>5%</span>
                    </div>
                </div>
                <div class="stat-number">36h</div>
                <div class="stat-label">Learning Time</div>
            </div>
        </div>

        <!-- Courses Section -->
        <div class="courses-section">
            <div class="section-header">
                <h2>My Courses</h2>
                <a href="#" class="view-all">
                    View All
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="courses-grid">
                <div class="course-card">
                    <div class="course-image">
                        <span class="course-category">Mathematics</span>
                    </div>
                    <div class="course-content">
                        <h3 class="course-title">Algebra Made Fun</h3>
                        <p class="course-description">Learn algebra through interactive games and real-world problems.</p>
                        
                        <div class="course-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 75%"></div>
                            </div>
                            <div class="progress-info">
                                <span>Progress</span>
                                <span>75%</span>
                            </div>
                        </div>
                        
                        <div class="course-actions">
                            <button class="btn-primary">Continue</button>
                            <button class="btn-secondary">View Details</button>
                        </div>
                    </div>
                </div>
                
                <div class="course-card">
                    <div class="course-image">
                        <span class="course-category">Science</span>
                    </div>
                    <div class="course-content">
                        <h3 class="course-title">Physics Adventures</h3>
                        <p class="course-description">Discover the wonders of physics with experiments and simulations.</p>
                        
                        <div class="course-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 60%"></div>
                            </div>
                            <div class="progress-info">
                                <span>Progress</span>
                                <span>60%</span>
                            </div>
                        </div>
                        
                        <div class="course-actions">
                            <button class="btn-primary">Continue</button>
                            <button class="btn-secondary">View Details</button>
                        </div>
                    </div>
                </div>
                
                <div class="course-card">
                    <div class="course-image">
                        <span class="course-category">English</span>
                    </div>
                    <div class="course-content">
                        <h3 class="course-title">Grammar Games</h3>
                        <p class="course-description">Master English grammar through fun games and challenges.</p>
                        
                        <div class="course-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 90%"></div>
                            </div>
                            <div class="progress-info">
                                <span>Progress</span>
                                <span>90%</span>
                            </div>
                        </div>
                        
                        <div class="course-actions">
                            <button class="btn-primary">Continue</button>
                            <button class="btn-secondary">View Details</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="courses-section">
            <div class="section-header">
                <h2>Recent Activity</h2>
            </div>
            
            <div class="activity-list">
                <div class="activity-item">
                    <div class="activity-icon" style="background: linear-gradient(135deg, var(--primary), var(--purple));">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Completed Math Puzzle Game</div>
                        <div class="activity-time">10 minutes ago • Scored 95%</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon" style="background: linear-gradient(135deg, var(--secondary), #34D399);">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Submitted Science Assignment</div>
                        <div class="activity-time">2 hours ago • Waiting for review</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon" style="background: linear-gradient(135deg, var(--accent), #FBBF24);">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Earned "Math Master" Badge</div>
                        <div class="activity-time">Yesterday • 5:30 PM</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon" style="background: linear-gradient(135deg, var(--pink), var(--purple));">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Asked AI Tutor for help</div>
                        <div class="activity-time">Yesterday • 4:15 PM</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Assistant -->
        <div class="ai-assistant">
            <div class="ai-icon">
                <i class="fas fa-robot"></i>
            </div>
            <h3>Need help? Ask Nexa AI</h3>
            <p>Your personal AI tutor is here to help with homework, explain concepts, or answer questions.</p>
            
            <div class="ai-input">
                <input type="text" placeholder="Ask me anything about your lessons...">
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
            
            // Update time
            function updateTime() {
                const now = new Date();
                const timeElements = document.querySelectorAll('.activity-time');
                timeElements.forEach(el => {
                    if (el.textContent.includes('minutes ago')) {
                        const minutes = Math.floor(Math.random() * 60);
                        el.textContent = `${minutes} minutes ago`;
                    }
                });
            }
            updateTime();
            setInterval(updateTime, 60000);
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
            alert('You have 3 new notifications:\n1. New math game available\n2. Assignment due tomorrow\n3. New badge unlocked!');
        });
    </script>
</body>
</html>