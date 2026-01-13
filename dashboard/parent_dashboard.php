 <?php
    session_start();
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'parent') {
        header('Location: ../Frontend/login.php');
        exit();
    }
    ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard • Nexa AI</title>
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

        /* Sidebar - Green Theme */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #10B981, #34D399);
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
            background: linear-gradient(135deg, var(--secondary), #059669);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
        }

        .child-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 15px;
            margin-top: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .child-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--accent), var(--pink));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
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
            border-color: var(--secondary);
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
            background: var(--secondary);
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
            background: linear-gradient(135deg, var(--secondary), #34D399);
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

        /* Children Grid */
        .children-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .child-card {
            background: var(--white);
            padding: 30px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-left: 4px solid var(--secondary);
        }

        .child-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .child-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .child-avatar-large {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--accent), var(--pink));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: var(--white);
        }

        .child-details h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .child-details p {
            color: var(--medium-gray);
            font-size: 0.95rem;
        }

        .child-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--medium-gray);
            font-size: 0.9rem;
        }

        /* Performance Section */
        .performance-section {
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
            color: var(--secondary);
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

        .performance-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }

        .performance-card {
            background: var(--white);
            border-radius: 16px;
            padding: 30px;
            box-shadow: var(--shadow);
        }

        .performance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .performance-title {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .performance-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary);
        }

        .progress-bar {
            height: 10px;
            background: var(--border);
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--secondary), #34D399);
            border-radius: 5px;
            transition: width 1s ease-out;
        }

        .subject-list {
            margin-top: 25px;
        }

        .subject-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border);
        }

        .subject-item:last-child {
            border-bottom: none;
        }

        .subject-name {
            font-weight: 500;
        }

        .subject-score {
            font-weight: 600;
            color: var(--secondary);
        }

        /* Recent Activity */
        .activity-section {
            margin-bottom: 40px;
        }

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

        /* Parent Controls */
        .controls-section {
            background: var(--white);
            border-radius: 16px;
            padding: 30px;
            box-shadow: var(--shadow);
            margin-bottom: 40px;
        }

        .controls-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }

        .control-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: var(--light-gray);
            border-radius: 12px;
            transition: var(--transition);
            cursor: pointer;
        }

        .control-item:hover {
            background: var(--white);
            border: 2px solid var(--secondary);
            transform: translateY(-2px);
        }

        .control-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--secondary), #34D399);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.3rem;
        }

        .control-content h4 {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .control-content p {
            color: var(--medium-gray);
            font-size: 0.9rem;
        }

        /* Messages Section */
        .messages-section {
            background: linear-gradient(135deg, var(--black), #1F2937);
            color: var(--white);
            padding: 30px;
            border-radius: 20px;
        }

        .messages-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .messages-header h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
        }

        .message-input {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .message-input input {
            flex: 1;
            padding: 14px 20px;
            border-radius: 12px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            font-family: 'Inter', sans-serif;
        }

        .message-input input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .message-input input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.15);
        }

        .message-input button {
            padding: 14px 25px;
            background: linear-gradient(135deg, var(--secondary), #34D399);
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .message-input button:hover {
            transform: translateY(-2px);
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
            .children-grid, .performance-cards, .controls-grid {
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
            .child-stats {
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
                    <i class="fas fa-users"></i>
                </div>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></h3>
                    <p>Parent Account</p>
                </div>
            </div>
            
            <div class="child-info">
                <div class="child-avatar">
                    <i class="fas fa-child"></i>
                </div>
                <div class="user-info">
                    <h3>Child: <?php echo htmlspecialchars($_SESSION['child_username']); ?></h3>
                    <p>Active Student</p>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="#" class="nav-item active">
                <i class="fas fa-home"></i>
                <span class="nav-label">Dashboard</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-chart-line"></i>
                <span class="nav-label">Progress Reports</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span class="nav-label">Schedule</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-tasks"></i>
                <span class="nav-label">Assignments</span>
                <span class="badge">3</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-award"></i>
                <span class="nav-label">Achievements</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-clock"></i>
                <span class="nav-label">Screen Time</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-comments"></i>
                <span class="nav-label">Messages</span>
                <span class="badge">2</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                <span class="nav-label">Settings</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-shield-alt"></i>
                <span class="nav-label">Parental Controls</span>
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
                <input type="text" placeholder="Search child's progress or messages...">
            </div>
            
            <div class="top-actions">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">5</span>
                </button>
                <a href="../auth/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="banner-shapes"></div>
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>! 👨‍👩‍👧‍👦</h1>
            <p>Stay connected with your child's learning journey. Your child has completed 5 assignments this week.</p>
        </div>

        <!-- Children Grid -->
        <div class="children-grid">
            <div class="child-card">
                <div class="child-header">
                    <div class="child-avatar-large">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="child-details">
                        <h3><?php echo htmlspecialchars($_SESSION['child_username']); ?></h3>
                        <p>Class 6 • Active for 2h 30m today</p>
                    </div>
                </div>
                
                <div class="child-stats">
                    <div class="stat-item">
                        <div class="stat-number">92%</div>
                        <div class="stat-label">Overall Score</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">18</div>
                        <div class="stat-label">Badges</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">85%</div>
                        <div class="stat-label">Attendance</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24h</div>
                        <div class="stat-label">This Week</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Section -->
        <div class="performance-section">
            <div class="section-header">
                <h2>Weekly Performance</h2>
                <a href="#" class="view-all">
                    View Details
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="performance-cards">
                <div class="performance-card">
                    <div class="performance-header">
                        <div class="performance-title">Mathematics</div>
                        <div class="performance-value">94%</div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 94%"></div>
                    </div>
                    
                    <div class="subject-list">
                        <div class="subject-item">
                            <span class="subject-name">Algebra</span>
                            <span class="subject-score">96%</span>
                        </div>
                        <div class="subject-item">
                            <span class="subject-name">Geometry</span>
                            <span class="subject-score">92%</span>
                        </div>
                        <div class="subject-item">
                            <span class="subject-name">Statistics</span>
                            <span class="subject-score">94%</span>
                        </div>
                    </div>
                </div>
                
                <div class="performance-card">
                    <div class="performance-header">
                        <div class="performance-title">Science</div>
                        <div class="performance-value">88%</div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 88%"></div>
                    </div>
                    
                    <div class="subject-list">
                        <div class="subject-item">
                            <span class="subject-name">Physics</span>
                            <span class="subject-score">90%</span>
                        </div>
                        <div class="subject-item">
                            <span class="subject-name">Chemistry</span>
                            <span class="subject-score">85%</span>
                        </div>
                        <div class="subject-item">
                            <span class="subject-name">Biology</span>
                            <span class="subject-score">89%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parent Controls -->
        <div class="controls-section">
            <h2>Parental Controls</h2>
            <p>Manage your child's learning experience and set appropriate limits.</p>
            
            <div class="controls-grid">
                <div class="control-item">
                    <div class="control-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="control-content">
                        <h4>Screen Time</h4>
                        <p>Set daily learning limits</p>
                    </div>
                </div>
                
                <div class="control-item">
                    <div class="control-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <div class="control-content">
                        <h4>Game Access</h4>
                        <p>Control game categories</p>
                    </div>
                </div>
                
                <div class="control-item">
                    <div class="control-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="control-content">
                        <h4>Notifications</h4>
                        <p>Set progress alerts</p>
                    </div>
                </div>
                
                <div class="control-item">
                    <div class="control-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="control-content">
                        <h4>Reports</h4>
                        <p>Weekly progress reports</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="activity-section">
            <div class="section-header">
                <h2>Child's Recent Activity</h2>
            </div>
            
            <div class="activity-list">
                <div class="activity-item">
                    <div class="activity-icon" style="background: linear-gradient(135deg, var(--secondary), #34D399);">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Earned "Math Master" Badge</div>
                        <div class="activity-time">Today • 10:30 AM</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon" style="background: linear-gradient(135deg, var(--primary), var(--purple));">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Completed Science Assignment</div>
                        <div class="activity-time">Yesterday • 4:15 PM • Score: 95%</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon" style="background: linear-gradient(135deg, var(--accent), #FBBF24);">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Played "Grammar Quest" Game</div>
                        <div class="activity-time">Yesterday • 3:00 PM • 45 minutes</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon" style="background: linear-gradient(135deg, var(--pink), var(--purple));">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Asked AI Tutor for help</div>
                        <div class="activity-time">2 days ago • 2:30 PM</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages Section -->
        <div class="messages-section">
            <div class="messages-header">
                <h3>Message Teacher</h3>
                <span class="badge">2 Unread</span>
            </div>
            
            <p>Need to discuss your child's progress? Send a message to their teacher.</p>
            
            <div class="message-input">
                <input type="text" placeholder="Type your message here...">
                <button>
                    <i class="fas fa-paper-plane"></i>
                    Send
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
            
            // Update screen time
            function updateScreenTime() {
                const screenTime = document.querySelector('.stat-number:last-child');
                if (screenTime) {
                    const hours = Math.floor(Math.random() * 3) + 2;
                    const minutes = Math.floor(Math.random() * 60);
                    screenTime.textContent = `${hours}h ${minutes}m`;
                }
            }
            updateScreenTime();
            setInterval(updateScreenTime, 60000);
        });
        
        // Control items interaction
        document.querySelectorAll('.control-item').forEach(item => {
            item.addEventListener('click', () => {
                const title = item.querySelector('h4').textContent;
                alert(`Opening ${title} settings...`);
            });
        });
        
        // Message teacher
        const messageInput = document.querySelector('.message-input input');
        const messageButton = document.querySelector('.message-input button');
        
        messageButton.addEventListener('click', () => {
            if (messageInput.value.trim()) {
                alert(`Message sent to teacher: "${messageInput.value}"`);
                messageInput.value = '';
            }
        });
        
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                messageButton.click();
            }
        });
        
        // Notification click
        document.querySelector('.notification-btn').addEventListener('click', () => {
            alert('You have 5 new notifications:\n1. Child earned new badge\n2. Weekly report available\n3. Assignment due tomorrow\n4. Teacher replied to your message\n5. Screen time limit reached');
        });
    </script>
</body>
</html>