<?php
    session_start();
    
    // Check if user is logged in as student
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
        header('Location: ../Frontend/login.php');
        exit();
    }

    // Get student information
    $student_id = $_SESSION['user_id'];
    $student_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    $student_class = $_SESSION['class'] ?? 'Not specified';
    $student_username = $_SESSION['username'];
    
    // Database connection
    require_once '../includes/config.php';
    
    // Get assignments for student's class from teachers who teach that class
    $assignments_query = "
        SELECT 
            a.id,
            a.title,
            a.description,
            a.subject,
            a.class,
            a.due_date,
            a.total_points,
            a.created_at,
            t.firstName as teacher_first,
            t.lastName as teacher_last,
            t.emailAddress as teacher_email,
            s.id as submission_id,
            s.submission_text,
            s.attachment_url,
            s.submitted_at,
            s.graded_at,
            s.score,
            s.feedback,
            s.status,
            -- Determine display status
            CASE 
                WHEN s.status = 'graded' THEN 'graded'
                WHEN s.status = 'submitted' THEN 'submitted'
                WHEN s.submitted_at IS NOT NULL THEN 'submitted'
                WHEN a.due_date < NOW() AND (s.status IS NULL OR s.status = 'pending') THEN 'late'
                WHEN s.status = 'missing' THEN 'missing'
                ELSE 'pending'
            END as display_status
        FROM assignments a
        INNER JOIN teachers_details t ON a.teacher_id = t.id
        -- Check if teacher is assigned to teach this class
        INNER JOIN teacher_class_relationship tcr ON t.id = tcr.teacher_id AND tcr.class_name = a.class
        -- Get student's submission if exists
        LEFT JOIN assignment_submissions s ON a.id = s.assignment_id AND s.student_id = ?
        WHERE a.class = ?
        ORDER BY a.due_date ASC
    ";
    
    $stmt = mysqli_prepare($conn, $assignments_query);
    mysqli_stmt_bind_param($stmt, 'is', $student_id, $student_class);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $assignments = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Calculate if assignment is overdue
            $due_date = new DateTime($row['due_date']);
            $now = new DateTime();
            $is_overdue = $due_date < $now && ($row['display_status'] == 'pending' || $row['display_status'] == 'late');
            
            // Update display status if overdue
            if ($is_overdue && $row['display_status'] == 'pending') {
                $row['display_status'] = 'late';
            }
            
            $assignments[] = $row;
        }
    } else {
        error_log("Assignment query failed: " . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt);
    
    // Count assignments by status
    $stats = [
        'pending' => 0,
        'submitted' => 0,
        'graded' => 0,
        'late' => 0,
        'total' => count($assignments)
    ];
    
    foreach ($assignments as $assignment) {
        switch ($assignment['display_status']) {
            case 'graded':
                $stats['graded']++;
                break;
            case 'submitted':
                $stats['submitted']++;
                break;
            case 'late':
                $stats['late']++;
                break;
            default:
                $stats['pending']++;
        }
    }
    
    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments • Nexa AI</title>
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
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
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

        /* Debug info */
        .debug-info {
            background: #f3f4f6;
            padding: 10px;
            border-radius: 8px;
            margin: 10px;
            font-family: monospace;
            font-size: 12px;
            color: #6b7280;
            border-left: 4px solid var(--primary);
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
            color: var(--medium-gray);
            max-width: 600px;
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-top: 4px solid;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card.pending { border-color: var(--info); }
        .stat-card.submitted { border-color: var(--warning); }
        .stat-card.graded { border-color: var(--secondary); }
        .stat-card.late { border-color: var(--danger); }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
        }

        .stat-card.pending .stat-icon { background: linear-gradient(135deg, var(--info), #60a5fa); }
        .stat-card.submitted .stat-icon { background: linear-gradient(135deg, var(--warning), #fbbf24); }
        .stat-card.graded .stat-icon { background: linear-gradient(135deg, var(--secondary), #34d399); }
        .stat-card.late .stat-icon { background: linear-gradient(135deg, var(--danger), #f87171); }

        .stat-info h3 {
            font-size: 2rem;
            font-weight: 700;
            font-family: 'Space Grotesk', sans-serif;
            margin-bottom: 5px;
        }

        .stat-info p {
            font-size: 0.9rem;
            color: var(--medium-gray);
        }

        /* Assignments Grid */
        .assignments-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .assignment-card {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid var(--border);
        }

        .assignment-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .assignment-header {
            padding: 25px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .assignment-info {
            flex: 1;
        }

        .assignment-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--black);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .assignment-subject {
            display: inline-block;
            padding: 4px 12px;
            background: var(--light-gray);
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--medium-gray);
        }

        .assignment-description {
            color: var(--medium-gray);
            margin: 15px 0;
            line-height: 1.6;
            max-height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .assignment-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 0.9rem;
            color: var(--medium-gray);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .assignment-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending { background: #dbeafe; color: #1d4ed8; }
        .status-submitted { background: #fef3c7; color: #d97706; }
        .status-graded { background: #d1fae5; color: #047857; }
        .status-late { background: #fee2e2; color: #dc2626; }
        .status-missing { background: #f3f4f6; color: #6b7280; }

        .assignment-score {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .assignment-body {
            padding: 25px;
        }

        .assignment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .detail-card {
            padding: 20px;
            background: var(--light-gray);
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .detail-label {
            font-size: 0.9rem;
            color: var(--medium-gray);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--black);
        }

        .assignment-actions {
            display: flex;
            gap: 15px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .btn-primary, .btn-secondary, .btn-success, .btn-danger {
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

        .btn-success {
            background: linear-gradient(135deg, var(--secondary), #34d399);
            color: var(--white);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #f87171);
            color: var(--white);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        }

        /* Feedback Section */
        .feedback-section {
            background: #f0f9ff;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid var(--primary);
        }

        .feedback-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: var(--primary);
            font-weight: 600;
        }

        .feedback-text {
            color: var(--dark-gray);
            line-height: 1.6;
            padding: 10px;
            background: var(--white);
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        /* Modal */
        .modal {
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

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            border-radius: 16px;
            max-width: 500px;
            width: 100%;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            padding: 25px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--black);
        }

        .close-modal {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-gray);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--medium-gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .close-modal:hover {
            background: var(--danger);
            border-color: var(--danger);
            color: var(--white);
        }

        .modal-body {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-gray);
        }

        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            resize: vertical;
            min-height: 150px;
            transition: var(--transition);
        }

        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 15px;
            justify-content: flex-end;
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
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .assignment-header {
                flex-direction: column;
            }
            .assignment-details {
                grid-template-columns: 1fr;
            }
            .assignment-actions {
                flex-direction: column;
            }
            .top-bar {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .assignment-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Debug Information -->
    <div class="debug-info">
        Student ID: <?php echo $student_id; ?> | 
        Student Name: <?php echo htmlspecialchars($student_name); ?> | 
        Class: <?php echo htmlspecialchars($student_class); ?> | 
        Total Assignments: <?php echo $stats['total']; ?>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-tasks"></i>
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
            <a href="student_dashboard.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span class="nav-label">Dashboard</span>
            </a>
            <a href="games.php" class="nav-item">
                <i class="fas fa-gamepad"></i>
                <span class="nav-label">Learning Games</span>
            </a>
            <a href="student_assignment.php" class="nav-item active">
                <i class="fas fa-tasks"></i>
                <span class="nav-label">Assignments</span>
                <span class="badge"><?php echo $stats['total']; ?></span>
            </a>
            <a href="student_ai_tutor.php" class="nav-item">
                <i class="fas fa-robot"></i>
                <span class="nav-label">AI Tutor</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-chart-line"></i>
                <span class="nav-label">Progress</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-trophy"></i>
                <span class="nav-label">Achievements</span>
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
            
            <div class="page-title">
                <h1>📚 My Assignments</h1>
                <p>View, submit, and track your assignments. <?php echo $stats['total']; ?> total assignments assigned.</p>
            </div>
            
            <div class="top-actions">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge"><?php echo $stats['pending'] + $stats['late']; ?></span>
                </button>
                <a href="../auth/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card pending">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['pending']; ?></h3>
                    <p>Pending Assignments</p>
                </div>
            </div>
            
            <div class="stat-card submitted">
                <div class="stat-icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['submitted']; ?></h3>
                    <p>Submitted</p>
                </div>
            </div>
            
            <div class="stat-card graded">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['graded']; ?></h3>
                    <p>Graded</p>
                </div>
            </div>
            
            <div class="stat-card late">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['late']; ?></h3>
                    <p>Late/Missing</p>
                </div>
            </div>
        </div>

        <!-- Assignments Grid -->
        <div class="assignments-grid">
            <?php if (!empty($assignments)): ?>
                <?php foreach ($assignments as $assignment): ?>
                    <?php 
                    // Format dates
                    $due_date = date('M d, Y', strtotime($assignment['due_date']));
                    $due_time = date('h:i A', strtotime($assignment['due_date']));
                    $created_date = date('M d, Y', strtotime($assignment['created_at']));
                    $submitted_date = $assignment['submitted_at'] ? date('M d, Y h:i A', strtotime($assignment['submitted_at'])) : null;
                    $graded_date = $assignment['graded_at'] ? date('M d, Y', strtotime($assignment['graded_at'])) : null;
                    $teacher_name = $assignment['teacher_first'] . ' ' . $assignment['teacher_last'];
                    
                    // Determine status class
                    $status_class = 'status-' . $assignment['display_status'];
                    
                    // Check if assignment is overdue
                    $due_datetime = new DateTime($assignment['due_date']);
                    $now = new DateTime();
                    $is_overdue = $due_datetime < $now && $assignment['display_status'] !== 'submitted' && $assignment['display_status'] !== 'graded';
                    ?>
                    
                    <div class="assignment-card <?php echo $is_overdue ? 'overdue' : ''; ?>">
                        <div class="assignment-header">
                            <div class="assignment-info">
                                <h3 class="assignment-title">
                                    <?php echo htmlspecialchars($assignment['title']); ?>
                                    <span class="assignment-subject"><?php echo htmlspecialchars($assignment['subject']); ?></span>
                                    <span class="assignment-status <?php echo $status_class; ?>">
                                        <?php echo ucfirst($assignment['display_status']); ?>
                                        <?php if ($is_overdue): ?>
                                            ⚠️
                                        <?php endif; ?>
                                    </span>
                                </h3>
                                
                                <p class="assignment-description">
                                    <?php echo htmlspecialchars($assignment['description']); ?>
                                </p>
                                
                                <div class="assignment-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        <span>Teacher: <?php echo htmlspecialchars($teacher_name); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-calendar-day"></i>
                                        <span>Due: <?php echo $due_date; ?> at <?php echo $due_time; ?></span>
                                        <?php if ($is_overdue): ?>
                                            <span style="color: var(--danger); font-weight: bold;">(Overdue)</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-star"></i>
                                        <span>Points: <?php echo $assignment['total_points']; ?></span>
                                    </div>
                                    <?php if ($assignment['score'] !== null): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-chart-line"></i>
                                            <span>Score: <strong class="assignment-score"><?php echo $assignment['score']; ?>/<?php echo $assignment['total_points']; ?></strong></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if ($assignment['score'] !== null): ?>
                                <div class="assignment-score">
                                    <?php echo $assignment['score']; ?>/<?php echo $assignment['total_points']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="assignment-body">
                            <div class="assignment-details">
                                <div class="detail-card">
                                    <div class="detail-label">
                                        <i class="fas fa-info-circle"></i>
                                        Assignment Details
                                    </div>
                                    <div class="detail-value">
                                        Class: <?php echo htmlspecialchars($assignment['class']); ?>
                                    </div>
                                    <div class="detail-value" style="font-size: 1rem; margin-top: 5px;">
                                        Assigned: <?php echo $created_date; ?>
                                    </div>
                                </div>
                                
                                <div class="detail-card">
                                    <div class="detail-label">
                                        <i class="fas fa-paper-plane"></i>
                                        Submission Status
                                    </div>
                                    <div class="detail-value">
                                        <?php if ($assignment['submitted_at']): ?>
                                            Submitted: <?php echo $submitted_date; ?>
                                        <?php else: ?>
                                            Not Submitted
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($assignment['graded_at']): ?>
                                        <div class="detail-value" style="font-size: 1rem; margin-top: 5px;">
                                            Graded: <?php echo $graded_date; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($assignment['attachment_url']): ?>
                                <div class="detail-card">
                                    <div class="detail-label">
                                        <i class="fas fa-paperclip"></i>
                                        Attachment
                                    </div>
                                    <div class="detail-value">
                                        <a href="../<?php echo htmlspecialchars($assignment['attachment_url']); ?>" target="_blank" style="color: var(--primary); text-decoration: none;">
                                            <i class="fas fa-download"></i> Download Attachment
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($assignment['feedback']): ?>
                                <div class="feedback-section">
                                    <div class="feedback-header">
                                        <i class="fas fa-comment-dots"></i>
                                        Teacher's Feedback
                                    </div>
                                    <div class="feedback-text">
                                        <?php echo htmlspecialchars($assignment['feedback']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="assignment-actions">
                                <?php if ($assignment['display_status'] === 'pending' || $assignment['display_status'] === 'late'): ?>
                                    <button class="btn-success submit-assignment-btn" 
                                            data-assignment-id="<?php echo $assignment['id']; ?>" 
                                            data-title="<?php echo htmlspecialchars($assignment['title']); ?>"
                                            data-max-points="<?php echo $assignment['total_points']; ?>">
                                        <i class="fas fa-paper-plane"></i>
                                        Submit Assignment
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($assignment['submitted_at'] && $assignment['display_status'] === 'submitted'): ?>
                                    <button class="btn-secondary" disabled>
                                        <i class="fas fa-check"></i>
                                        Submitted - Awaiting Grade
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($assignment['submitted_at'] && ($assignment['display_status'] === 'pending' || $assignment['display_status'] === 'late')): ?>
                                    <button class="btn-secondary update-submission-btn" 
                                            data-assignment-id="<?php echo $assignment['id']; ?>"
                                            data-title="<?php echo htmlspecialchars($assignment['title']); ?>">
                                        <i class="fas fa-edit"></i>
                                        Update Submission
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn-primary view-details-btn" data-description="<?php echo htmlspecialchars($assignment['description']); ?>">
                                    <i class="fas fa-eye"></i>
                                    View Full Description
                                </button>
                                
                                <a href="mailto:<?php echo htmlspecialchars($assignment['teacher_email']); ?>?subject=Question about assignment: <?php echo urlencode($assignment['title']); ?>&body=Dear <?php echo urlencode($teacher_name); ?>,%0D%0A%0D%0AI have a question about the assignment: <?php echo urlencode($assignment['title']); ?>%0D%0A%0D%0AFrom: <?php echo urlencode($student_name); ?> (Class: <?php echo urlencode($student_class); ?>)" 
                                   class="btn-secondary">
                                    <i class="fas fa-question-circle"></i>
                                    Ask Teacher
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-check"></i>
                    <h3>No Assignments Yet</h3>
                    <p>You don't have any assignments right now. Check back later or ask your teacher for new assignments.</p>
                    <button class="btn-primary" onclick="window.location.reload()">
                        <i class="fas fa-sync"></i>
                        Refresh Page
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Submit Assignment Modal -->
    <div class="modal" id="submitModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Submit Assignment</h3>
                <button class="close-modal" id="closeModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="submitForm" method="POST" action="submit_assignment.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="assignmentId" name="assignment_id">
                    <input type="hidden" id="studentId" name="student_id" value="<?php echo $student_id; ?>">
                    
                    <div class="form-group">
                        <label for="submissionText">
                            <i class="fas fa-edit"></i>
                            Your Submission <span id="charCount">(0/5000 characters)</span>
                        </label>
                        <textarea 
                            id="submissionText" 
                            name="submission_text" 
                            placeholder="Type your assignment submission here..." 
                            maxlength="5000"
                            required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="attachment">
                            <i class="fas fa-paperclip"></i>
                            Attach File (Optional)
                        </label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="file" id="attachment" name="attachment" class="btn-secondary" style="flex: 1; padding: 12px;">
                            <button type="button" id="clearAttachment" class="btn-danger" style="padding: 12px;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <small style="color: var(--medium-gray); font-size: 0.85rem; margin-top: 5px; display: block;">
                            Supported formats: PDF, DOC, DOCX, JPG, PNG, TXT (Max: 10MB)
                        </small>
                    </div>
                    
                    <div style="background: #f0f9ff; padding: 15px; border-radius: 8px; margin-top: 20px;">
                        <div style="display: flex; align-items: center; gap: 10px; color: var(--primary); font-weight: 600; margin-bottom: 10px;">
                            <i class="fas fa-info-circle"></i>
                            Important Notes
                        </div>
                        <ul style="color: var(--medium-gray); padding-left: 20px; font-size: 0.9rem;">
                            <li>Make sure to review your submission before sending</li>
                            <li>You can update your submission until the due date</li>
                            <li>Late submissions may affect your grade</li>
                            <li>Contact your teacher if you have questions</li>
                            <li>Maximum file size: 10MB</li>
                        </ul>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="cancelBtn">
                        Cancel
                    </button>
                    <button type="submit" class="btn-success" id="submitBtn">
                        <i class="fas fa-paper-plane"></i>
                        Submit Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Description Modal -->
    <div class="modal" id="descriptionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Assignment Description</h3>
                <button class="close-modal" id="closeDescriptionModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="fullDescription"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-primary" id="closeDescBtn">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(e.target) && menuToggle && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
        
        // Modal functionality
        const submitModal = document.getElementById('submitModal');
        const descriptionModal = document.getElementById('descriptionModal');
        const closeModal = document.getElementById('closeModal');
        const closeDescriptionModal = document.getElementById('closeDescriptionModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const closeDescBtn = document.getElementById('closeDescBtn');
        const submitButtons = document.querySelectorAll('.submit-assignment-btn, .update-submission-btn');
        const viewDetailsButtons = document.querySelectorAll('.view-details-btn');
        const modalTitle = document.getElementById('modalTitle');
        const assignmentIdInput = document.getElementById('assignmentId');
        const submissionText = document.getElementById('submissionText');
        const charCount = document.getElementById('charCount');
        const attachmentInput = document.getElementById('attachment');
        const clearAttachmentBtn = document.getElementById('clearAttachment');
        const submitBtn = document.getElementById('submitBtn');
        
        // Character counter for textarea
        if (submissionText && charCount) {
            submissionText.addEventListener('input', function() {
                const length = this.value.length;
                charCount.textContent = `(${length}/5000 characters)`;
                
                if (length > 4500) {
                    charCount.style.color = 'var(--danger)';
                } else if (length > 4000) {
                    charCount.style.color = 'var(--warning)';
                } else {
                    charCount.style.color = 'var(--medium-gray)';
                }
            });
        }
        
        // Clear attachment button
        if (clearAttachmentBtn && attachmentInput) {
            clearAttachmentBtn.addEventListener('click', function() {
                attachmentInput.value = '';
            });
        }
        
        // Open submit modal when submit button is clicked
        if (submitButtons) {
            submitButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const assignmentId = this.getAttribute('data-assignment-id');
                    const assignmentTitle = this.getAttribute('data-title');
                    const isUpdate = this.classList.contains('update-submission-btn');
                    
                    modalTitle.textContent = isUpdate ? `Update Submission: ${assignmentTitle}` : `Submit: ${assignmentTitle}`;
                    assignmentIdInput.value = assignmentId;
                    
                    // Clear form
                    submissionText.value = '';
                    if (attachmentInput) attachmentInput.value = '';
                    if (charCount) charCount.textContent = '(0/5000 characters)';
                    if (charCount) charCount.style.color = 'var(--medium-gray)';
                    
                    submitModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            });
        }
        
        // Open description modal
        if (viewDetailsButtons) {
            viewDetailsButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const description = this.getAttribute('data-description');
                    document.getElementById('fullDescription').textContent = description;
                    descriptionModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            });
        }
        
        // Close modals
        function closeSubmitModal() {
            submitModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        function closeDescriptionModalFunc() {
            descriptionModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        if (closeModal) closeModal.addEventListener('click', closeSubmitModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeSubmitModal);
        if (closeDescriptionModal) closeDescriptionModal.addEventListener('click', closeDescriptionModalFunc);
        if (closeDescBtn) closeDescBtn.addEventListener('click', closeDescriptionModalFunc);
        
        // Close modal when clicking outside
        if (submitModal) {
            submitModal.addEventListener('click', (e) => {
                if (e.target === submitModal) {
                    closeSubmitModal();
                }
            });
        }
        
        if (descriptionModal) {
            descriptionModal.addEventListener('click', (e) => {
                if (e.target === descriptionModal) {
                    closeDescriptionModalFunc();
                }
            });
        }
        
        // Escape key to close modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (submitModal.classList.contains('active')) {
                    closeSubmitModal();
                }
                if (descriptionModal.classList.contains('active')) {
                    closeDescriptionModalFunc();
                }
            }
        });
        
        // Form submission with AJAX
        if (document.getElementById('submitForm')) {
            document.getElementById('submitForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
                submitBtn.disabled = true;
                
                // Submit via AJAX
                fetch('submit_assignment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        alert('Assignment submitted successfully! Your teacher will review it soon.');
                        closeSubmitModal();
                        
                        // Reload the page to show updated status
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        // Show error message
                        alert('Error: ' + data.error);
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while submitting. Please try again.');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            });
        }
        
        // File size validation
        if (attachmentInput) {
            attachmentInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const maxSize = 10 * 1024 * 1024; // 10MB
                    if (file.size > maxSize) {
                        alert('File size exceeds 10MB limit. Please choose a smaller file.');
                        this.value = '';
                    }
                    
                    // Validate file type
                    const allowedTypes = ['application/pdf', 'application/msword', 
                                         'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                         'image/jpeg', 'image/png', 'text/plain'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('File type not allowed. Please upload PDF, DOC, DOCX, JPG, PNG, or TXT files.');
                        this.value = '';
                    }
                }
            });
        }
        
        // Notification click
        const notificationBtn = document.querySelector('.notification-btn');
        if (notificationBtn) {
            notificationBtn.addEventListener('click', () => {
                const pending = <?php echo $stats['pending']; ?>;
                const late = <?php echo $stats['late']; ?>;
                
                if (pending + late > 0) {
                    alert(`You have ${pending} pending and ${late} late assignments.`);
                } else {
                    alert('All assignments are up to date!');
                }
            });
        }
        
        // Auto-check for due dates
        function checkDueDates() {
            const now = new Date();
            const assignmentCards = document.querySelectorAll('.assignment-card');
            
            assignmentCards.forEach(card => {
                const dueDateText = card.querySelector('.meta-item:nth-child(2) span').textContent;
                const dueMatch = dueDateText.match(/Due: (\w+ \d+, \d+) at (\d+:\d+ [AP]M)/);
                
                if (dueMatch) {
                    const dueDateStr = `${dueMatch[1]} ${dueMatch[2]}`;
                    const dueDate = new Date(dueDateStr);
                    
                    if (dueDate < now) {
                        const statusBadge = card.querySelector('.assignment-status');
                        if (statusBadge && !statusBadge.classList.contains('status-late') && 
                            !statusBadge.classList.contains('status-graded') &&
                            !statusBadge.classList.contains('status-submitted')) {
                            
                            statusBadge.textContent = 'Late';
                            statusBadge.className = 'assignment-status status-late';
                            
                            // Add warning animation
                            card.style.animation = 'pulse 2s infinite';
                            setTimeout(() => {
                                card.style.animation = '';
                            }, 2000);
                        }
                    }
                }
            });
        }
        
        // Check due dates on load and every minute
        checkDueDates();
        setInterval(checkDueDates, 60000);
        
        // Add CSS for pulse animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes pulse {
                0% { box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05); }
                50% { box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.3); }
                100% { box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05); }
            }
            
            .assignment-card.overdue {
                border-left: 4px solid var(--danger);
            }
        `;
        document.head.appendChild(style);
        
        // Auto-refresh assignments every 5 minutes
        setTimeout(() => {
            window.location.reload();
        }, 300000); // 5 minutes
    </script>
</body>
</html>