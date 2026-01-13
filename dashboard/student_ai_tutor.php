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
    <title>AI Tutor • Nexa</title>
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
            --white: #ffffffff;
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

        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .feature-card {
            background: var(--white);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            padding: 12px;
            cursor: pointer;
            transition: var(--transition);
        }

        .feature-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .feature-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .feature-title {
            font-weight: 600;
            font-size: 0.8rem;
            color: var(--dark);
            margin-bottom: 2px;
        }

        .feature-desc {
            font-size: 0.7rem;
            color: var(--gray-600);
        }

        .stats-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            padding: 12px;
            text-align: center;
        }

        .stat-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 2px;
        }

        .stat-label {
            font-size: 0.7rem;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Main Content */
        .main-content {
            background: var(--gray-100);
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }

        /* Chat Header */
        .chat-header {
            background: var(--white);
            border-bottom: 1px solid var(--gray-300);
            padding: 20px 44px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-title h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .chat-title p {
            font-size: 0.9rem;
            color: var(--gray-600);
        }

        .chat-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: var(--gray-100);
            border: 1px solid var(--gray-300);
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-badge.online {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.2);
            color: var(--secondary);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--gray-500);
        }

        .status-badge.online .status-dot {
            background: var(--secondary);
            animation: pulse 2s infinite;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            color: var(--gray-700);
            cursor: pointer;
            transition: var(--transition);
        }

        .action-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Chat Container */
        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 24px;
            overflow: hidden;
        }

        /* Messages Container */
        .messages-container {
            flex: 1;
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-300);
            overflow-y: auto;
            padding: 24px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Message Styling */
        .message {
            display: flex;
            max-width: 85%;
        }

        .message.user {
            margin-left: auto;
            flex-direction: row-reverse;
        }

        .message-content {
            padding: 12px 16px;
            border-radius: var(--radius-lg);
            font-size: 0.95rem;
            line-height: 1.5;
            word-wrap: break-word;
        }

        .message.bot .message-content {
            background: var(--gray-100);
            border: 1px solid var(--gray-300);
            border-bottom-left-radius: var(--radius-sm);
            color: var(--dark);
        }

        .message.user .message-content {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border-bottom-right-radius: var(--radius-sm);
        }

        .message.system {
            max-width: 100%;
            justify-content: center;
        }

        .message.system .message-content {
            background: rgba(245, 158, 11, 0.1);
            color: var(--accent);
            font-size: 0.9rem;
            text-align: center;
        }

        .message-time {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 4px;
            padding: 0 4px;
        }

        .message.user .message-time {
            text-align: right;
        }

        .message.bot .message-time {
            text-align: left;
        }

        /* AI Response Components */
        .ai-response {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .subject-tag {
            display: inline-block;
            padding: 4px 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .learning-tip {
            background: rgba(16, 185, 129, 0.1);
            border-left: 3px solid var(--secondary);
            padding: 10px 12px;
            border-radius: var(--radius);
            font-size: 0.9rem;
            margin-top: 8px;
        }

        .follow-up {
            background: rgba(37, 99, 235, 0.1);
            border-left: 3px solid var(--primary);
            padding: 10px 12px;
            border-radius: var(--radius);
            font-size: 0.9rem;
            margin-top: 8px;
        }

        /* Input Area */
        .input-container {
            background: var(--white);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-lg);
            padding: 16px;
            position: relative;
        }

        .chat-input {
            width: 100%;
            border: none;
            outline: none;
            resize: none;
            font-family: inherit;
            font-size: 0.95rem;
            line-height: 1.5;
            color: var(--dark);
            background: transparent;
            min-height: 56px;
            max-height: 120px;
        }

        .chat-input::placeholder {
            color: var(--gray-500);
        }

        .input-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 12px;
        }

        .quick-actions {
            display: flex;
            gap: 8px;
        }

        .quick-btn {
            padding: 6px 12px;
            background: var(--gray-100);
            border: 1px solid var(--gray-300);
            border-radius: 20px;
            font-size: 0.8rem;
            color: var(--gray-700);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .quick-btn:hover {
            background: var(--white);
            border-color: var(--primary);
            color: var(--primary);
        }

        .send-btn {
            padding: 8px 24px;
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

        .send-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Typing Indicator */
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            background: var(--gray-100);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-lg);
            max-width: 200px;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
        }

        .typing-dots span {
            width: 6px;
            height: 6px;
            background: var(--gray-500);
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }

        .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        .typing-dots span:nth-child(3) { animation-delay: 0.4s; }

        /* Animations */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        @keyframes typing {
            0%, 100% { transform: translateY(0); opacity: 0.6; }
            50% { transform: translateY(-4px); opacity: 1; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message {
            animation: fadeIn 0.2s ease-out;
        }

        /* Scrollbar Styling */
        .messages-container::-webkit-scrollbar {
            width: 6px;
        }

        .messages-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .messages-container::-webkit-scrollbar-thumb {
            background: var(--gray-400);
            border-radius: 3px;
        }

        .messages-container::-webkit-scrollbar-thumb:hover {
            background: var(--gray-500);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .app {
                grid-template-columns: 1fr;
                grid-template-rows: 72px 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .features-grid,
            .stats-cards {
                grid-template-columns: repeat(4, 1fr);
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
            
            .chat-header {
                padding: 16px;
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .chat-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .chat-container {
                padding: 16px;
            }
            
            .messages-container {
                padding: 16px;
            }
            
            .features-grid,
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .input-actions {
                flex-direction: column;
                gap: 12px;
            }
            
            .quick-actions {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .message {
                max-width: 95%;
            }
            
            .message-content {
                font-size: 0.9rem;
                padding: 10px 14px;
            }
            
            .quick-btn span {
                display: none;
            }
            
            .quick-btn {
                padding: 8px;
            }
        }

        /* Utility Classes */
        .hidden {
            display: none !important;
        }

        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .text-gradient {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
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
                        <i class="fas fa-robot"></i>
                    </div>
                    <span>Nexa AI</span>
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
                    <a href="#" class="nav-item active">
                        <i class="fas fa-comments"></i>
                        AI Tutor Chat
                    </a>
                    <a href="student-dashboard.php" class="nav-item">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                    <a href="learning-resources.php" class="nav-item">
                        <i class="fas fa-book"></i>
                        Learning Resources
                    </a>
                    <a href="progress.php" class="nav-item">
                        <i class="fas fa-chart-line"></i>
                        Progress
                    </a>
                </div>
            </div>
            
            <div class="sidebar-section">
                <h3 class="sidebar-title">Quick Actions</h3>
                <div class="features-grid">
                    <div class="feature-card" id="featureMath">
                        <div class="feature-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="feature-title">Math</div>
                        <div class="feature-desc">Step-by-step solutions</div>
                    </div>
                    
                    <div class="feature-card" id="featureScience">
                        <div class="feature-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div class="feature-title">Science</div>
                        <div class="feature-desc">Concepts explained</div>
                    </div>
                    
                    <div class="feature-card" id="featureEnglish">
                        <div class="feature-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="feature-title">English</div>
                        <div class="feature-desc">Grammar & writing</div>
                    </div>
                    
                    <div class="feature-card" id="featureHomework">
                        <div class="feature-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="feature-title">Homework</div>
                        <div class="feature-desc">Assignment help</div>
                    </div>
                </div>
            </div>
            
            <div class="sidebar-section">
                <h3 class="sidebar-title">Learning Stats</h3>
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-value" id="questionsAsked">0</div>
                        <div class="stat-label">Questions</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value" id="subjectsLearned">0</div>
                        <div class="stat-label">Subjects</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value" id="aiRating">4.9</div>
                        <div class="stat-label">AI Rating</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value" id="learningStreak">1</div>
                        <div class="stat-label">Day Streak</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="chat-header">
                <div class="chat-title">
                    <h2>Nexa AI Tutor</h2>
                    <p>Ask me anything about your subjects, homework, or concepts</p>
                </div>
                <div class="chat-actions">
                    <div class="status-badge online" id="aiStatus">
                        <span class="status-dot"></span>
                        <span>AI Connected</span>
                    </div>
                    <button class="action-btn" id="clearChatBtn" title="Clear Chat">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    <button class="action-btn" id="exportChatBtn" title="Export Notes">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
            
            <div class="chat-container">
                <div class="messages-container" id="chatMessages">
                    <!-- Welcome Message -->
                    <div class="message system">
                        <div class="message-content">
                            👋 Welcome to Nexa AI Tutor! I'm here to help you learn. Ask me anything!
                        </div>
                        <div class="message-time"><?php echo date('h:i A'); ?></div>
                    </div>
                </div>
                
                <div class="input-container">
                    <textarea 
                        class="chat-input" 
                        id="chatInput" 
                        placeholder="Type your question here... (Press Shift+Enter for new line)"
                        rows="3"
                    ></textarea>
                    
                    <div class="input-actions">
                        <div class="quick-actions">
                            <button class="quick-btn" data-question="Explain algebra to me">
                                <i class="fas fa-calculator"></i>
                                <span>Math Help</span>
                            </button>
                            <button class="quick-btn" data-question="What is photosynthesis?">
                                <i class="fas fa-flask"></i>
                                <span>Science Help</span>
                            </button>
                            <button class="quick-btn" data-question="Help me with grammar">
                                <i class="fas fa-book"></i>
                                <span>English Help</span>
                            </button>
                            <button class="quick-btn" data-question="Give me a practice question">
                                <i class="fas fa-question-circle"></i>
                                <span>Practice</span>
                            </button>
                        </div>
                        
                        <button class="send-btn" id="sendBtn">
                            <i class="fas fa-paper-plane"></i>
                            Send Message
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // DOM Elements
        const chatInput = document.getElementById('chatInput');
        const sendBtn = document.getElementById('sendBtn');
        const chatMessages = document.getElementById('chatMessages');
        const aiStatus = document.getElementById('aiStatus');
        const quickButtons = document.querySelectorAll('.quick-btn');
        const featureCards = document.querySelectorAll('.feature-card');
        const clearChatBtn = document.getElementById('clearChatBtn');
        const exportChatBtn = document.getElementById('exportChatBtn');
        
        // Stats elements
        const questionsAskedEl = document.getElementById('questionsAsked');
        const subjectsLearnedEl = document.getElementById('subjectsLearned');
        const aiRatingEl = document.getElementById('aiRating');
        const learningStreakEl = document.getElementById('learningStreak');

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadConversationHistory();
            loadLearningStats();
            checkAIHealth();
            chatInput.focus();
        });

        // Add message to chat
        function addMessage(message, isUser = false, isSystem = false, subject = null) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isSystem ? 'system' : isUser ? 'user' : 'bot'}`;
            
            const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            let contentHTML = '';
            
            if (isSystem) {
                contentHTML = `<div class="message-content">${escapeHtml(message)}</div>`;
            } else if (isUser) {
                contentHTML = `<div class="message-content">${escapeHtml(message)}</div>`;
            } else {
                // AI response with formatting
                let formattedResponse = message;
                if (subject) {
                    formattedResponse = `
                        <div class="ai-response">
                            <span class="subject-tag">${subject}</span>
                            <div>${formatText(message)}</div>
                        </div>
                    `;
                } else {
                    formattedResponse = `<div class="ai-response">${formatText(message)}</div>`;
                }
                contentHTML = `<div class="message-content">${formattedResponse}</div>`;
            }
            
            contentHTML += `<div class="message-time">${time}</div>`;
            messageDiv.innerHTML = contentHTML;
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            if (!isSystem) {
                saveToHistory(message, isUser, subject);
                if (isUser) updateQuestionsAsked();
            }
        }

        // Format text
        function formatText(text) {
            return text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>')
                .replace(/`(.*?)`/g, '<code>$1</code>');
        }

        // Escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Show typing indicator
        function showTyping() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'typing-indicator';
            typingDiv.id = 'typingIndicator';
            typingDiv.innerHTML = `
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span style="color: var(--gray-600); font-size: 0.85rem;">AI is thinking...</span>
            `;
            
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Hide typing indicator
        function hideTyping() {
            const typing = document.getElementById('typingIndicator');
            if (typing) typing.remove();
        }

        // Check AI Health
        async function checkAIHealth() {
            try {
                const response = await fetch('../includes/ai_handler.php?action=health');
                const data = await response.json();
                
                if (data.success && data.healthy) {
                    aiStatus.className = 'status-badge online';
                    aiStatus.innerHTML = '<span class="status-dot"></span><span>AI Connected</span>';
                } else {
                    aiStatus.className = 'status-badge';
                    aiStatus.innerHTML = '<span class="status-dot"></span><span>AI Offline</span>';
                }
            } catch (error) {
                console.error('Health check failed:', error);
                aiStatus.className = 'status-badge';
                aiStatus.innerHTML = '<span class="status-dot"></span><span>Connection Error</span>';
            }
        }

        // Ask AI Tutor
        // Global conversation history for API
let conversationHistory = [];

async function askAITutor(question) {
    if (!question.trim()) return;
    
    addMessage(question, true);
    chatInput.value = '';
    sendBtn.disabled = true;
    showTyping();
    
    try {
        const formData = new FormData();
        formData.append('question', question);
        formData.append('action', 'ask_ai');
        formData.append('student_level', 'primary');
        
        // Send conversation history as JSON string
        formData.append('history', JSON.stringify(conversationHistory));
        
        const response = await fetch('../includes/ai_handler.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        hideTyping();
        sendBtn.disabled = false;
        
        if (data.success) {
            let formattedResponse = data.response.answer;
            
            // Add to conversation history for next request
            conversationHistory.push({
                student: question,
                tutor: formattedResponse
            });
            
            // Keep only last 10 exchanges (20 messages) to avoid token limits
            if (conversationHistory.length > 10) {
                conversationHistory = conversationHistory.slice(-10);
            }
            
            if (data.response.learning_tip) {
                formattedResponse += `<div class="learning-tip"><strong>💡 Learning Tip:</strong> ${data.response.learning_tip}</div>`;
            }
            if (data.response.follow_up_question) {
                formattedResponse += `<div class="follow-up"><strong>🤔 Try This:</strong> ${data.response.follow_up_question}</div>`;
            }
            
            addMessage(formattedResponse, false, false, data.response.subject);
        } else {
            addMessage("Sorry, I'm having trouble connecting. Please try again.", false);
        }
    } catch (error) {
        console.error('AI Error:', error);
        hideTyping();
        sendBtn.disabled = false;
        addMessage("I'm connecting to the AI server. Please try again in a moment.", false);
    }
}

// Update the clear chat function to also clear conversation history
// Replace the existing clearChatBtn event listener
clearChatBtn.addEventListener('click', () => {
    if (confirm('Clear all chat history?')) {
        // Clear localStorage
        localStorage.removeItem('nexa_ai_tutor_history');
        
        // Clear conversation history for API
        conversationHistory = [];
        
        // Remove messages from UI
        const messages = chatMessages.querySelectorAll('.message:not(.system)');
        messages.forEach(msg => msg.remove());
        
        // Reset stats
        const stats = JSON.parse(localStorage.getItem('nexa_ai_stats') || '{}');
        stats.questions_asked = 0;
        localStorage.setItem('nexa_ai_stats', JSON.stringify(stats));
        questionsAskedEl.textContent = '0';
        
        addMessage("Chat cleared. Ready to help!", false, true);
    }
});


        // Load conversation history
       function loadConversationHistory() {
    const history = JSON.parse(localStorage.getItem('nexa_ai_tutor_history') || '[]');
    const messages = chatMessages.querySelectorAll('.message:not(.system)');
    messages.forEach(msg => msg.remove());
    
    // Rebuild conversation history for API
    conversationHistory = [];
    let currentPair = null;
    
    history.forEach(msg => {
        addMessage(msg.message, msg.isUser, false, msg.subject);
        
        // Build conversation pairs for API
        if (msg.isUser) {
            currentPair = { student: msg.message };
        } else if (currentPair) {
            currentPair.tutor = msg.message;
            conversationHistory.push(currentPair);
            currentPair = null;
        }
    });
    
    // Keep only last 10 exchanges
    if (conversationHistory.length > 10) {
        conversationHistory = conversationHistory.slice(-10);
    }
}

        // Save conversation
        function saveToHistory(message, isUser, subject = null) {
            const history = JSON.parse(localStorage.getItem('nexa_ai_tutor_history') || '[]');
            const cleanMessage = typeof message === 'string' ? 
                message.replace(/<[^>]*>/g, '').substring(0, 500) : message;
            
            history.push({ 
                message: cleanMessage,
                isUser, 
                subject,
                timestamp: new Date().toISOString() 
            });
            
            if (history.length > 100) history.splice(0, history.length - 100);
            localStorage.setItem('nexa_ai_tutor_history', JSON.stringify(history));
        }

        // Load learning stats
        function loadLearningStats() {
            const stats = JSON.parse(localStorage.getItem('nexa_ai_stats') || '{}');
            
            if (!stats.questions_asked) {
                stats.questions_asked = 0;
                stats.subjects = new Set();
                stats.last_activity = null;
                stats.streak = 1;
            }
            
            // Update streak
            const today = new Date().toDateString();
            if (stats.last_activity && stats.last_activity !== today) {
                const lastDate = new Date(stats.last_activity);
                const diffDays = Math.floor((Date.now() - lastDate) / (1000 * 60 * 60 * 24));
                stats.streak = diffDays === 1 ? stats.streak + 1 : 1;
            }
            stats.last_activity = today;
            
            questionsAskedEl.textContent = stats.questions_asked;
            subjectsLearnedEl.textContent = stats.subjects.size || 0;
            learningStreakEl.textContent = stats.streak;
            
            localStorage.setItem('nexa_ai_stats', JSON.stringify({
                ...stats,
                subjects: Array.from(stats.subjects || [])
            }));
        }

        function updateQuestionsAsked() {
            const stats = JSON.parse(localStorage.getItem('nexa_ai_stats') || '{}');
            stats.questions_asked = (stats.questions_asked || 0) + 1;
            questionsAskedEl.textContent = stats.questions_asked;
            localStorage.setItem('nexa_ai_stats', JSON.stringify(stats));
        }

        // Event Listeners
        sendBtn.addEventListener('click', () => askAITutor(chatInput.value.trim()));

        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                askAITutor(chatInput.value.trim());
            }
        });

        chatInput.addEventListener('input', () => {
            chatInput.style.height = 'auto';
            chatInput.style.height = Math.min(chatInput.scrollHeight, 120) + 'px';
            sendBtn.disabled = !chatInput.value.trim();
        });

        quickButtons.forEach(btn => {
            btn.addEventListener('click', () => askAITutor(btn.dataset.question));
        });

        featureCards.forEach(card => {
            card.addEventListener('click', () => {
                const questions = {
                    featureMath: "Help me with a math problem",
                    featureScience: "Explain a science concept",
                    featureEnglish: "Help me with English grammar",
                    featureHomework: "I need help with homework"
                };
                askAITutor(questions[card.id]);
            });
        });

        clearChatBtn.addEventListener('click', () => {
            if (confirm('Clear all chat history?')) {
                localStorage.removeItem('nexa_ai_tutor_history');
                const messages = chatMessages.querySelectorAll('.message:not(.system)');
                messages.forEach(msg => msg.remove());
                
                const stats = JSON.parse(localStorage.getItem('nexa_ai_stats') || '{}');
                stats.questions_asked = 0;
                localStorage.setItem('nexa_ai_stats', JSON.stringify(stats));
                questionsAskedEl.textContent = '0';
                
                addMessage("Chat cleared. Ready to help!", false, true);
            }
        });

        exportChatBtn.addEventListener('click', () => {
            const history = JSON.parse(localStorage.getItem('nexa_ai_tutor_history') || '[]');
            if (history.length === 0) {
                alert('No chat history to export.');
                return;
            }
            
            let exportText = `Nexa AI Tutor Conversation - ${new Date().toLocaleDateString()}\n\n`;
            history.forEach(msg => {
                exportText += `${new Date(msg.timestamp).toLocaleTimeString()} - ${msg.isUser ? 'You' : 'AI'}:\n${msg.message}\n\n`;
            });
            
            const blob = new Blob([exportText], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `nexa-tutor-${new Date().toISOString().split('T')[0]}.txt`;
            a.click();
            URL.revokeObjectURL(url);
            
            addMessage("✅ Notes exported successfully!", false, true);
        });

        // Auto-check AI status
        setInterval(checkAIHealth, 120000);
    </script>
</body>
</html>