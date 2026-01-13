<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexa AI • Intelligent Learning Assistant</title>
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
            background-color: var(--white);
            color: var(--black);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            z-index: 1000;
            border-bottom: 1px solid var(--border);
            padding: 20px 0;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--black);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--purple));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.2rem;
        }

        .nav-links {
            display: flex;
            gap: 40px;
        }

        .nav-links a {
            color: var(--dark-gray);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            transition: var(--transition);
            position: relative;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--purple));
            transition: var(--transition);
            border-radius: 2px;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .cta-button {
            background: linear-gradient(135deg, var(--primary), var(--purple));
            color: var(--white);
            border: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--black);
            cursor: pointer;
        }

        /* Hero Section */
        .hero {
            padding: 160px 0 100px;
            background: linear-gradient(135deg, #F8FAFF 0%, #F0F4FF 100%);
            position: relative;
            overflow: hidden;
        }

        .hero-bg-shape {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.05), rgba(139, 92, 246, 0.05));
            top: -200px;
            right: -200px;
            z-index: 0;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .hero-text h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 20px;
            color: var(--black);
            background: linear-gradient(135deg, var(--primary), var(--purple));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: inline-block;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--dark-gray);
            margin-bottom: 40px;
            max-width: 500px;
            line-height: 1.8;
        }

        .hero-subtitle span {
            color: var(--primary);
            font-weight: 600;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .secondary-button {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .secondary-button:hover {
            background-color: var(--primary);
            color: var(--white);
            transform: translateY(-2px);
        }

        .hero-visual {
            position: relative;
            height: 400px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .visual-container {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .floating-element {
            position: absolute;
            background-color: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            padding: 25px;
            width: 280px;
            transition: var(--transition);
            border-top: 4px solid;
        }

        .floating-element.student {
            top: 0;
            left: 0;
            z-index: 1;
            transform: rotate(-5deg);
            border-color: var(--primary);
        }

        .floating-element.parent {
            top: 50%;
            right: 0;
            transform: translateY(-50%) rotate(5deg);
            z-index: 2;
            border-color: var(--secondary);
        }

        .floating-element.teacher {
            bottom: 0;
            left: 20%;
            transform: rotate(2deg);
            z-index: 3;
            border-color: var(--accent);
        }

        .floating-element:hover {
            transform: translateY(-10px) rotate(0deg);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .element-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: var(--white);
        }

        .student .element-icon {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
        }

        .parent .element-icon {
            background: linear-gradient(135deg, var(--secondary), #34D399);
        }

        .teacher .element-icon {
            background: linear-gradient(135deg, var(--accent), #FBBF24);
        }

        .floating-element h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .floating-element p {
            font-size: 0.9rem;
            color: var(--medium-gray);
            line-height: 1.6;
        }

        /* Features Section */
        .features {
            padding: 100px 0;
            background-color: var(--white);
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--black);
        }

        .section-header p {
            font-size: 1.1rem;
            color: var(--medium-gray);
            max-width: 600px;
            margin: 0 auto;
        }

        .features-tabs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 50px;
            flex-wrap: wrap;
        }

        .tab-button {
            background-color: transparent;
            color: var(--medium-gray);
            border: 2px solid var(--border);
            padding: 14px 30px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tab-button.active {
            background: linear-gradient(135deg, var(--primary), var(--purple));
            color: var(--white);
            border-color: transparent;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2);
        }

        .tab-button:hover:not(.active) {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.5s ease-out;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background-color: var(--white);
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--purple));
        }

        .feature-card:hover {
            border-color: var(--primary);
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            font-size: 1.8rem;
            color: var(--white);
            background: linear-gradient(135deg, var(--primary), var(--purple));
        }

        .feature-card h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            font-weight: 600;
            color: var(--black);
        }

        .feature-card p {
            color: var(--medium-gray);
            font-size: 0.95rem;
            line-height: 1.7;
        }

        /* AI Features Section */
        .ai-features {
            padding: 100px 0;
            background: linear-gradient(135deg, #F8FAFF 0%, #F0F4FF 100%);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .ai-features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
        }

        .ai-feature {
            background-color: var(--white);
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            gap: 25px;
            align-items: flex-start;
            border: 1px solid transparent;
        }

        .ai-feature:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
        }

        .ai-feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--accent), var(--pink));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: var(--white);
            flex-shrink: 0;
        }

        .ai-feature-content h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--black);
        }

        .ai-feature-content p {
            color: var(--medium-gray);
            font-size: 0.95rem;
            line-height: 1.7;
        }

        /* Stats Section */
        .stats {
            padding: 80px 0;
            background-color: var(--white);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .stat-item {
            text-align: center;
            padding: 30px;
            border-radius: 16px;
            background-color: var(--light-gray);
            transition: var(--transition);
        }

        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .stat-number {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--primary), var(--purple));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .stat-item p {
            color: var(--medium-gray);
            font-size: 1rem;
        }

        /* Footer */
        footer {
            padding: 80px 0 40px;
            background-color: var(--black);
            color: var(--white);
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 50px;
            margin-bottom: 60px;
        }

        .footer-column h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.3rem;
            margin-bottom: 25px;
            font-weight: 600;
            color: var(--white);
        }

        .footer-logo {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--white), #E5E7EB);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .footer-about p {
            color: #9CA3AF;
            margin-bottom: 25px;
            line-height: 1.7;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: #9CA3AF;
            text-decoration: none;
            transition: var(--transition);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-links a:hover {
            color: var(--white);
        }

        .footer-links i {
            width: 20px;
            text-align: center;
        }

        .copyright {
            text-align: center;
            padding-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #9CA3AF;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .hero-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .hero-text h1 {
                font-size: 2.8rem;
            }

            .hero-visual {
                height: 350px;
            }

            .floating-element {
                width: 240px;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background-color: var(--white);
                flex-direction: column;
                padding: 20px;
                box-shadow: var(--shadow);
                border-top: 1px solid var(--border);
                gap: 20px;
            }

            .nav-links.active {
                display: flex;
            }

            .mobile-menu-btn {
                display: block;
            }

            .hero {
                padding: 140px 0 80px;
            }

            .hero-text h1 {
                font-size: 2.3rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: flex-start;
            }

            .features-tabs {
                flex-direction: column;
                align-items: center;
            }

            .tab-button {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }

            .ai-features-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .hero-text h1 {
                font-size: 2rem;
            }

            .hero-visual {
                height: 300px;
            }

            .floating-element {
                width: 200px;
            }

            .section-header h2 {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container nav-container">
            <a href="#" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-robot"></i>
                </div>
                NEXA AI
            </a>
            
            <div class="nav-links" id="navLinks">
                <a href="#features">Features</a>
                <a href="#for-students">For Students</a>
                <a href="#for-parents">For Parents</a>
                <a href="#for-teachers">For Teachers</a>
                <a href="#ai-features">AI Learning</a>
            </div>
            
            <div class="nav-actions">
               <a href="signup.php"> <button class="cta-button" >Get Started</button></a>
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg-shape"></div>
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Welcome to Nexa AI</h1>
                    <p class="hero-subtitle">Your intelligent AI assistant <span>making learning fun and exciting</span> for every child in Ghana! Join students, parents, and teachers in our educational revolution.</p>
                    <div class="hero-buttons">
                        <button class="cta-button">
                            <i class="fas fa-play-circle"></i>
                            Start Learning Free
                        </button>
                        <button class="secondary-button">
                            <i class="fas fa-video"></i>
                            Watch Demo
                        </button>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="visual-container">
                        <div class="floating-element student">
                            <div class="element-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h3>For Students</h3>
                            <p>Fun games, AI tutor, and personalized learning journey</p>
                        </div>
                        
                        <div class="floating-element parent">
                            <div class="element-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3>For Parents</h3>
                            <p>Track progress, set goals, and support learning at home</p>
                        </div>
                        
                        <div class="floating-element teacher">
                            <div class="element-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <h3>For Teachers</h3>
                            <p>Classroom tools, analytics, and lesson planning support</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header">
                <h2>Designed For Everyone</h2>
                <p>Nexa AI provides tailored learning experiences for students, powerful tools for parents, and classroom support for teachers</p>
            </div>
            
            <div class="features-tabs">
                <button class="tab-button active" data-tab="students">
                    <i class="fas fa-graduation-cap"></i>
                    For Students
                </button>
                <button class="tab-button" data-tab="parents">
                    <i class="fas fa-users"></i>
                    For Parents
                </button>
                <button class="tab-button" data-tab="teachers">
                    <i class="fas fa-chalkboard-teacher"></i>
                    For Teachers
                </button>
            </div>
            
            <!-- Students Tab -->
            <div class="tab-content active" id="students-content">
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-gamepad"></i>
                        </div>
                        <h3>Learning Games</h3>
                        <p>Interactive games that make learning exciting and engaging for students of all ages.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h3>AI Assignments</h3>
                        <p>Personalized assignments that adapt to each student's learning pace and style.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h3>Achievements</h3>
                        <p>Earn badges and rewards as you progress, making learning a rewarding journey.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h3>AI Tutor</h3>
                        <p>24/7 AI tutor ready to explain concepts, answer questions, and provide guidance.</p>
                    </div>
                </div>
            </div>
            
            <!-- Parents Tab -->
            <div class="tab-content" id="parents-content">
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h3>Parent Portal</h3>
                        <p>Secure access to monitor your child's progress, set limits, and manage their learning.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Progress Reports</h3>
                        <p>Detailed analytics and reports on your child's learning journey and achievements.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <h3>Homework Help</h3>
                        <p>Resources and tools to assist your child with homework and challenging subjects.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>Contact Us</h3>
                        <p>Direct line to our support team for any questions, concerns, or feedback.</p>
                    </div>
                </div>
            </div>
            
            <!-- Teachers Tab -->
            <div class="tab-content" id="teachers-content">
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chalkboard"></i>
                        </div>
                        <h3>Classroom Tools</h3>
                        <p>Digital tools to create interactive lessons, assignments, and classroom activities.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h3>Student Analytics</h3>
                        <p>Track student performance, identify learning gaps, and personalize instruction.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3>Lesson Planning</h3>
                        <p>AI-powered tools to create, organize, and optimize lesson plans and curricula.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h3>Teacher Community</h3>
                        <p>Connect with other educators, share resources, and collaborate on best practices.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="section-header">
                <h2>Transforming Education in Ghana</h2>
                <p>Join thousands of students, parents, and teachers already experiencing the Nexa AI advantage</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">5,000+</div>
                    <p>Happy Students</p>
                </div>
                <div class="stat-item">
                    <div class="stat-number">1,200+</div>
                    <p>Engaged Parents</p>
                </div>
                <div class="stat-item">
                    <div class="stat-number">300+</div>
                    <p>Partner Teachers</p>
                </div>
                <div class="stat-item">
                    <div class="stat-number">98%</div>
                    <p>Satisfaction Rate</p>
                </div>
            </div>
        </div>
    </section>

    <!-- AI Features Section -->
    <section class="ai-features" id="ai-features">
        <div class="container">
            <div class="section-header">
                <h2>AI Learning Features</h2>
                <p>Experience the future of education with our AI-powered tools designed to transform how children learn</p>
            </div>
            
            <div class="ai-features-grid">
                <div class="ai-feature">
                    <div class="ai-feature-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="ai-feature-content">
                        <h3>AI Tutor</h3>
                        <p>Personalized learning assistant that adapts to each student's needs and provides instant feedback and guidance throughout their learning journey.</p>
                    </div>
                </div>
                
                <div class="ai-feature">
                    <div class="ai-feature-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div class="ai-feature-content">
                        <h3>Smart Assignments</h3>
                        <p>AI-generated assignments that adjust difficulty based on student performance and learning patterns, ensuring optimal challenge and growth.</p>
                    </div>
                </div>
                
                <div class="ai-feature">
                    <div class="ai-feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="ai-feature-content">
                        <h3>Learning Analytics</h3>
                        <p>Comprehensive data insights to track progress, identify strengths, and target areas for improvement with precision and clarity.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <div class="footer-logo">NEXA AI</div>
                    <p class="footer-about">Making learning fun and exciting for every child in Ghana through intelligent AI-powered education tools for students, parents, and teachers.</p>
                    <button class="cta-button">
                        <i class="fas fa-rocket"></i>
                        Start Free Trial
                    </button>
                </div>
                
                <div class="footer-column">
                    <h3>For Students</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-gamepad"></i> Learning Games</a></li>
                        <li><a href="#"><i class="fas fa-tasks"></i> AI Assignments</a></li>
                        <li><a href="#"><i class="fas fa-trophy"></i> Achievements</a></li>
                        <li><a href="#"><i class="fas fa-robot"></i> AI Tutor</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>For Parents & Teachers</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-user-shield"></i> Parent Portal</a></li>
                        <li><a href="#"><i class="fas fa-chart-line"></i> Progress Reports</a></li>
                        <li><a href="#"><i class="fas fa-chalkboard"></i> Classroom Tools</a></li>
                        <li><a href="#"><i class="fas fa-headset"></i> Contact Support</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2023 Nexa AI. All rights reserved. | Making learning exciting for every child in Ghana</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navLinks = document.getElementById('navLinks');
        
        mobileMenuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            mobileMenuBtn.innerHTML = navLinks.classList.contains('active') 
                ? '<i class="fas fa-times"></i>' 
                : '<i class="fas fa-bars"></i>';
        });
        
        // Tab Switching
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const targetTab = button.getAttribute('data-tab');
                
                // Update active tab button
                tabButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                // Show corresponding content
                tabContents.forEach(content => {
                    content.classList.remove('active');
                    if (content.id === `${targetTab}-content`) {
                        content.classList.add('active');
                    }
                });
            });
        });
        
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if(targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                    
                    // Close mobile menu if open
                    if(navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                        mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                    }
                }
            });
        });
        
        // Add animation to floating elements
        const floatingElements = document.querySelectorAll('.floating-element');
        floatingElements.forEach((element, index) => {
            element.style.animationDelay = `${index * 0.2}s`;
        });
        
        // Button interaction feedback
        document.querySelectorAll('.cta-button, .secondary-button').forEach(button => {
            button.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
                
                // If it's a CTA button, show a success message
                if(this.classList.contains('cta-button')) {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-check"></i> Redirecting...';
                    
                setTimeout(() => {
                    window.location.href = 'signup.php';
                }, 1000);
                }
            });
        });
    </script>
</body>
</html>