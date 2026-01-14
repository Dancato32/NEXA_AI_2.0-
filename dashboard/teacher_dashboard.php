  <?php
    session_start();
    
    // Check if user is logged in as teacher
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
        header('Location: ../Frontend/login.php');
        exit();
    }

    // Get teacher information
    $teacher_id = $_SESSION['user_id'];
    $teacher_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    $teacher_subject = $_SESSION['subject'] ?? 'Teacher';
    $teacher_email = $_SESSION['email'];
    
    // Database connection
    require_once '../includes/config.php';
    
    // Get classes this teacher teaches
    $classes_query = "SELECT class_name FROM teacher_class_relationship WHERE teacher_id = ? ORDER BY class_name";
    $stmt = mysqli_prepare($conn, $classes_query);
    mysqli_stmt_bind_param($stmt, 'i', $teacher_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $teacher_classes = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $teacher_classes[] = $row['class_name'];
        }
    }
    mysqli_stmt_close($stmt);
    
    // Get total students in teacher's classes
    $students_count = 0;
    if (!empty($teacher_classes)) {
        $class_list = "'" . implode("','", $teacher_classes) . "'";
        $students_query = "SELECT COUNT(*) as total FROM student_details WHERE Class IN ($class_list)";
        $result = mysqli_query($conn, $students_query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $students_count = $row['total'];
        }
    }
    
    // Get teacher's assignments count
    $assignments_query = "SELECT COUNT(*) as total FROM assignments WHERE teacher_id = ?";
    $stmt = mysqli_prepare($conn, $assignments_query);
    mysqli_stmt_bind_param($stmt, 'i', $teacher_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $assignments_count = 0;
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $assignments_count = $row['total'];
    }
    mysqli_stmt_close($stmt);
    
    mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard • Nexa AI</title>
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

        /* Sidebar - Orange Theme */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #F59E0B, #FBBF24);
            color: var(--black);
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
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
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
            background: rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            margin-top: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--accent), #D97706);
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

        .class-info {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            padding: 15px;
            margin-top: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .class-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--purple));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            color: var(--white);
        }

        .sidebar-nav {
            padding: 0 20px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px 20px;
            color: rgba(0, 0, 0, 0.7);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 8px;
            transition: var(--transition);
            font-weight: 500;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255, 255, 255, 0.4);
            color: var(--black);
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
            background: var(--primary);
            color: var(--white);
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
            border-color: var(--accent);
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
            background: var(--accent);
            color: var(--white);
            transform: translateY(-2px);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 20px;
            height: 20px;
            background: var(--primary);
            color: var(--white);
            border-radius: 50%;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, var(--accent), #FBBF24);
            color: var(--black);
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
            background: rgba(255, 255, 255, 0.1);
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
            border-left: 4px solid var(--accent);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
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
            background: linear-gradient(135deg, var(--accent), #FBBF24);
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .trend-up {
            color: var(--secondary);
        }

        .trend-down {
            color: #EF4444;
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

        /* Students Grid */
        .students-section {
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
            color: var(--accent);
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

        .students-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .student-card {
            background: var(--white);
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .student-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .student-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--purple));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
        }

        .student-info h3 {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .student-info p {
            color: var(--medium-gray);
            font-size: 0.9rem;
        }

        .student-progress {
            margin-top: 20px;
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
            background: linear-gradient(90deg, var(--accent), #FBBF24);
            border-radius: 4px;
            transition: width 1s ease-out;
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: var(--medium-gray);
        }

        /* Assignments Section */
        .assignments-section {
            margin-bottom: 40px;
        }

        .assignments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }

        .assignment-card {
            background: var(--white);
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--shadow);
            border-top: 4px solid var(--primary);
        }

        .assignment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .assignment-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .assignment-subject {
            color: var(--medium-gray);
            font-size: 0.9rem;
        }

        .assignment-due {
            background: var(--light-gray);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .assignment-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .stat-small {
            text-align: center;
        }

        .stat-number-small {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 5px;
        }

        .stat-label-small {
            color: var(--medium-gray);
            font-size: 0.8rem;
        }

        /* Analytics Section */
        .analytics-section {
            background: var(--white);
            border-radius: 16px;
            padding: 30px;
            box-shadow: var(--shadow);
            margin-bottom: 40px;
        }

        .analytics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .analytics-chart {
            height: 200px;
            background: linear-gradient(135deg, #F8FAFF, #F0F4FF);
            border-radius: 12px;
            display: flex;
            align-items: flex-end;
            gap: 10px;
            padding: 20px;
            margin-top: 20px;
        }

        .chart-bar {
            flex: 1;
            background: linear-gradient(to top, var(--accent), #FBBF24);
            border-radius: 6px 6px 0 0;
            transition: var(--transition);
            position: relative;
        }

        .chart-bar:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }

        .chart-label {
            position: absolute;
            bottom: -25px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 0.8rem;
            color: var(--medium-gray);
        }

        /* Lesson Planner */
        .planner-section {
            background: linear-gradient(135deg, var(--black), #1F2937);
            color: var(--white);
            padding: 30px;
            border-radius: 20px;
        }

        .planner-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .planner-header h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
        }

        .planner-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 25px;
        }

        .planner-input {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .planner-input input,
        .planner-input textarea {
            padding: 14px 20px;
            border-radius: 12px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            font-family: 'Inter', sans-serif;
        }

        .planner-input textarea {
            min-height: 120px;
            resize: vertical;
        }

        .planner-input input::placeholder,
        .planner-input textarea::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .planner-input input:focus,
        .planner-input textarea:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.15);
        }

        .planner-input button {
            padding: 14px 25px;
            background: linear-gradient(135deg, var(--accent), #FBBF24);
            color: var(--black);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }

        .planner-input button:hover {
            transform: translateY(-2px);
        }

        .planner-preview {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
        }

        .preview-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--white);
        }

        .preview-content {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
            line-height: 1.6;
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
            .stats-grid,
            .students-grid,
            .assignments-grid,
            .planner-content {
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
            .assignment-stats {
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
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></h3>
                    <p><?php echo htmlspecialchars($_SESSION['subject']); ?> Teacher</p>
                </div>
            </div>
            
            <div class="class-info">
                <div class="class-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="user-info">
                    <h3>Classes</h3>
                    <p>3 Active Classes • 85 Students</p>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="#" class="nav-item active">
                <i class="fas fa-home"></i>
                <span class="nav-label">Dashboard</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-users"></i>
                <span class="nav-label">My Students</span>
                <span class="badge">85</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-tasks"></i>
                <span class="nav-label">Assignments</span>
                <span class="badge">12</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-chart-bar"></i>
                <span class="nav-label">Analytics</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span class="nav-label">Lesson Plans</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-comments"></i>
                <span class="nav-label">Messages</span>
                <span class="badge">8</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-graduation-cap"></i>
                <span class="nav-label">Resources</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                <span class="nav-label">Settings</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-user-friends"></i>
                <span class="nav-label">Teacher Community</span>
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
                <input type="text" placeholder="Search students, assignments, or resources...">
            </div>
            
            <div class="top-actions">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">8</span>
                </button>
                <a href="../auth/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="banner-shapes"></div>
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>! 🎓</h1>
            <p>Ready to inspire young minds? You have 12 assignments to review and 8 messages from parents.</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>5%</span>
                    </div>
                </div>
                <div class="stat-number">85</div>
                <div class="stat-label">Active Students</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-trend trend-down">
                        <i class="fas fa-arrow-down"></i>
                        <span>3%</span>
                    </div>
                </div>
                <div class="stat-number">12</div>
                <div class="stat-label">Pending Assignments</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>8%</span>
                    </div>
                </div>
                <div class="stat-number">92%</div>
                <div class="stat-label">Class Average</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>12%</span>
                    </div>
                </div>
                <div class="stat-number">24</div>
                <div class="stat-label">Parent Messages</div>
            </div>
        </div>

        <!-- Students Section -->
        <div class="students-section">
            <div class="section-header">
                <h2>Top Performing Students</h2>
                <a href="#" class="view-all">
                    View All Students
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="students-grid">
                <div class="student-card">
                    <div class="student-header">
                        <div class="student-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="student-info">
                            <h3>Kwame Mensah</h3>
                            <p>Class 6A • Mathematics</p>
                        </div>
                    </div>
                    
                    <div class="student-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 98%"></div>
                        </div>
                        <div class="progress-info">
                            <span>Performance</span>
                            <span>98%</span>
                        </div>
                    </div>
                </div>
                
                <div class="student-card">
                    <div class="student-header">
                        <div class="student-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="student-info">
                            <h3>Ama Serwaa</h3>
                            <p>Class 6B • Science</p>
                        </div>
                    </div>
                    
                    <div class="student-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 96%"></div>
                        </div>
                        <div class="progress-info">
                            <span>Performance</span>
                            <span>96%</span>
                        </div>
                    </div>
                </div>
                
                <div class="student-card">
                    <div class="student-header">
                        <div class="student-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="student-info">
                            <h3>Kofi Asante</h3>
                            <p>Class 6C • English</p>
                        </div>
                    </div>
                    
                    <div class="student-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 94%"></div>
                        </div>
                        <div class="progress-info">
                            <span>Performance</span>
                            <span>94%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignments Section -->
        <div class="assignments-section">
            <div class="section-header">
                <h2>Recent Assignments</h2>
                <a href="#" class="view-all">
                    Create New
                    <i class="fas fa-plus"></i>
                </a>
            </div>
            
            <div class="assignments-grid">
                <div class="assignment-card">
                    <div class="assignment-header">
                        <div>
                            <div class="assignment-title">Algebra Basics Quiz</div>
                            <div class="assignment-subject">Mathematics • Class 6A</div>
                        </div>
                        <div class="assignment-due">Due Tomorrow</div>
                    </div>
                    
                    <p>Test student understanding of basic algebraic concepts and equations.</p>
                    
                    <div class="assignment-stats">
                        <div class="stat-small">
                            <div class="stat-number-small">42</div>
                            <div class="stat-label-small">Submitted</div>
                        </div>
                        <div class="stat-small">
                            <div class="stat-number-small">85%</div>
                            <div class="stat-label-small">Average</div>
                        </div>
                        <div class="stat-small">
                            <div class="stat-number-small">8</div>
                            <div class="stat-label-small">Pending</div>
                        </div>
                    </div>
                </div>
                
                <div class="assignment-card">
                    <div class="assignment-header">
                        <div>
                            <div class="assignment-title">Science Experiment Report</div>
                            <div class="assignment-subject">Science • Class 6B</div>
                        </div>
                        <div class="assignment-due">In 3 Days</div>
                    </div>
                    
                    <p>Document findings from the recent physics experiment on motion.</p>
                    
                    <div class="assignment-stats">
                        <div class="stat-small">
                            <div class="stat-number-small">38</div>
                            <div class="stat-label-small">Submitted</div>
                        </div>
                        <div class="stat-small">
                            <div class="stat-number-small">88%</div>
                            <div class="stat-label-small">Average</div>
                        </div>
                        <div class="stat-small">
                            <div class="stat-number-small">12</div>
                            <div class="stat-label-small">Pending</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Section -->
        <div class="analytics-section">
            <div class="analytics-header">
                <h2>Class Performance Analytics</h2>
                <span>Last 7 Days</span>
            </div>
            
            <p>Track your students' engagement and performance across different subjects.</p>
            
            <div class="analytics-chart">
                <div class="chart-bar" style="height: 80%">
                    <div class="chart-label">Mon</div>
                </div>
                <div class="chart-bar" style="height: 90%">
                    <div class="chart-label">Tue</div>
                </div>
                <div class="chart-bar" style="height: 85%">
                    <div class="chart-label">Wed</div>
                </div>
                <div class="chart-bar" style="height: 95%">
                    <div class="chart-label">Thu</div>
                </div>
                <div class="chart-bar" style="height: 92%">
                    <div class="chart-label">Fri</div>
                </div>
                <div class="chart-bar" style="height: 88%">
                    <div class="chart-label">Sat</div>
                </div>
                <div class="chart-bar" style="height: 75%">
                    <div class="chart-label">Sun</div>
                </div>
            </div>
        </div>

        <!-- Lesson Planner -->
        <div class="planner-section">
            <div class="planner-header">
                <h3>AI Lesson Planner</h3>
                <span class="badge">NEW</span>
            </div>
            
            <p>Use AI to create engaging lesson plans tailored to your students' needs.</p>
            
            <div class="planner-content">
                <div class="planner-input">
                    <input type="text" placeholder="Lesson Topic" id="lessonTopic">
                    <input type="text" placeholder="Grade Level" id="gradeLevel">
                    <textarea placeholder="Learning Objectives" id="learningObjectives"></textarea>
                    <button id="generatePlan">
                        <i class="fas fa-magic"></i>
                        Generate Lesson Plan
                    </button>
                </div>
                
                <div class="planner-preview">
                    <div class="preview-title">Sample Lesson Plan:</div>
                    <div class="preview-content">
                        <p><strong>Topic:</strong> Introduction to Fractions</p>
                        <p><strong>Grade:</strong> 6</p>
                        <p><strong>Objectives:</strong> Understand basic fractions, compare fractions, add simple fractions</p>
                        <p><strong>Activities:</strong> Interactive fraction games, group pizza-cutting exercise, fraction bingo</p>
                        <p><strong>Assessment:</strong> Online quiz with instant feedback, group presentation</p>
                    </div>
                </div>
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
            
            // Animate chart bars
            const chartBars = document.querySelectorAll('.chart-bar');
            chartBars.forEach(bar => {
                const height = bar.style.height;
                bar.style.height = '0%';
                setTimeout(() => {
                    bar.style.height = height;
                }, 500);
            });
            
            // Update assignment stats
            function updateAssignmentStats() {
                const pendingElements = document.querySelectorAll('.stat-number-small:last-child');
                pendingElements.forEach(el => {
                    const current = parseInt(el.textContent);
                    if (current > 0) {
                        el.textContent = current - 1;
                    }
                });
            }
            
            // Simulate assignment submissions every 30 seconds
            setInterval(updateAssignmentStats, 30000);
        });
        
        // AI Lesson Planner
        const generatePlanBtn = document.getElementById('generatePlan');
        const lessonTopic = document.getElementById('lessonTopic');
        const gradeLevel = document.getElementById('gradeLevel');
        const learningObjectives = document.getElementById('learningObjectives');
        const previewContent = document.querySelector('.preview-content');
        
        generatePlanBtn.addEventListener('click', () => {
            if (lessonTopic.value.trim() && gradeLevel.value.trim()) {
                // Simulate AI generation
                generatePlanBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
                generatePlanBtn.disabled = true;
                
                setTimeout(() => {
                    const samplePlans = [
                        {
                            topic: "Introduction to Fractions",
                            grade: "6",
                            objectives: "Understand basic fractions, compare fractions, add simple fractions",
                            activities: "Interactive fraction games, group pizza-cutting exercise, fraction bingo",
                            assessment: "Online quiz with instant feedback, group presentation"
                        },
                        {
                            topic: "Basic Physics Concepts",
                            grade: "7",
                            objectives: "Understand motion, force, and energy, conduct simple experiments",
                            activities: "Ball rolling experiments, force measurement games, energy conservation simulations",
                            assessment: "Lab report, multiple choice test, group project"
                        },
                        {
                            topic: "Grammar Fundamentals",
                            grade: "6",
                            objectives: "Master basic sentence structure, understand parts of speech",
                            activities: "Sentence building games, grammar treasure hunt, story creation exercise",
                            assessment: "Writing assignment, grammar quiz, peer review"
                        }
                    ];
                    
                    const randomPlan = samplePlans[Math.floor(Math.random() * samplePlans.length)];
                    
                    previewContent.innerHTML = `
                        <p><strong>Topic:</strong> ${lessonTopic.value || randomPlan.topic}</p>
                        <p><strong>Grade:</strong> ${gradeLevel.value || randomPlan.grade}</p>
                        <p><strong>Objectives:</strong> ${learningObjectives.value || randomPlan.objectives}</p>
                        <p><strong>Activities:</strong> ${randomPlan.activities}</p>
                        <p><strong>Assessment:</strong> ${randomPlan.assessment}</p>
                        <p><em>Generated by Nexa AI • Tailored to your students' learning patterns</em></p>
                    `;
                    
                    generatePlanBtn.innerHTML = '<i class="fas fa-check"></i> Plan Generated!';
                    setTimeout(() => {
                        generatePlanBtn.innerHTML = '<i class="fas fa-magic"></i> Generate Lesson Plan';
                        generatePlanBtn.disabled = false;
                    }, 2000);
                }, 1500);
            } else {
                alert('Please enter at least a topic and grade level.');
            }
        });
        
        // Chart bar interaction
        document.querySelectorAll('.chart-bar').forEach(bar => {
            bar.addEventListener('mouseover', function() {
                const height = this.style.height;
                this.title = `Performance: ${height}`;
            });
        });
        
        // Notification click
        document.querySelector('.notification-btn').addEventListener('click', () => {
            alert('You have 8 new notifications:\n1. 5 new assignment submissions\n2. 2 parent messages\n3. 1 student question\n4. Weekly report ready\n5. New teaching resources available');
        });
    </script>
</body>
</html>