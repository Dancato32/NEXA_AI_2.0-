<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up • Nexa AI</title>
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
            background-color: var(--light-gray);
            color: var(--black);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signup-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1200px;
            width: 100%;
            background-color: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            min-height: auto;
            max-height: 95vh;
        }

        /* Left Side - Visual */
        .signup-visual-section {
            background: linear-gradient(135deg, #4F46E5, #8B5CF6);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            min-height: 500px;
        }

        .visual-content {
            text-align: center;
            color: var(--white);
            z-index: 1;
            position: relative;
            width: 100%;
            max-width: 500px;
        }

        .visual-icon {
            width: 100px;
            height: 100px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 2.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .visual-content h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.2rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .visual-content p {
            opacity: 0.9;
            margin-bottom: 30px;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .user-type-preview {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            width: 100%;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
            margin-top: 20px;
        }

        .preview-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
        }

        .student-preview .preview-icon {
            background: linear-gradient(135deg, var(--primary), var(--purple));
        }

        .parent-preview .preview-icon {
            background: linear-gradient(135deg, var(--secondary), #34D399);
        }

        .teacher-preview .preview-icon {
            background: linear-gradient(135deg, var(--accent), #FBBF24);
        }

        .user-type-preview h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
        }

        .user-type-preview p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0;
            line-height: 1.5;
        }

        .bg-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
        }

        .shape-1 {
            width: 300px;
            height: 300px;
            top: -100px;
            right: -100px;
        }

        .shape-2 {
            width: 200px;
            height: 200px;
            bottom: -50px;
            left: -50px;
        }

        .shape-3 {
            width: 150px;
            height: 150px;
            top: 40%;
            right: 20%;
        }

        /* Right Side - Form */
        .signup-form-section {
            padding: 40px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            max-height: 95vh;
        }

        /* Custom scrollbar for form section */
        .signup-form-section::-webkit-scrollbar {
            width: 8px;
        }

        .signup-form-section::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .signup-form-section::-webkit-scrollbar-thumb {
            background: var(--primary-light);
            border-radius: 4px;
        }

        .signup-form-section::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }

        .back-home {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--medium-gray);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 20px;
            transition: var(--transition);
            width: fit-content;
        }

        .back-home:hover {
            color: var(--primary);
        }

        .logo {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
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

        .welcome-text {
            margin-bottom: 25px;
        }

        .welcome-text h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--black);
            line-height: 1.2;
        }

        .welcome-text p {
            color: var(--medium-gray);
            font-size: 1rem;
        }

        .form-container {
            flex: 1;
        }

        .user-type-selector {
            margin-bottom: 25px;
        }

        .user-type-selector label {
            display: block;
            margin-bottom: 12px;
            font-weight: 500;
            color: var(--dark-gray);
            font-size: 1rem;
        }

        .user-type-options {
            display: flex;
            gap: 12px;
            margin-top: 5px;
        }

        .user-type-option {
            flex: 1;
            text-align: center;
            padding: 18px 12px;
            border: 2px solid var(--border);
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition);
            background-color: var(--white);
        }

        .user-type-option:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .user-type-option.selected {
            border-color: transparent;
            box-shadow: var(--shadow);
        }

        .user-type-option.student.selected {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(139, 92, 246, 0.1));
            border-color: var(--primary);
        }

        .user-type-option.parent.selected {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(52, 211, 153, 0.1));
            border-color: var(--secondary);
        }

        .user-type-option.teacher.selected {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(251, 191, 36, 0.1));
            border-color: var(--accent);
        }

        .user-type-option i {
            font-size: 1.5rem;
            margin-bottom: 10px;
            display: block;
        }

        .user-type-option.student i {
            color: var(--primary);
        }

        .user-type-option.parent i {
            color: var(--secondary);
        }

        .user-type-option.teacher i {
            color: var(--accent);
        }

        .user-type-option span {
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Form Styles */
        .form-section {
            display: none;
            animation: fadeIn 0.5s ease-out;
            margin-bottom: 20px;
        }

        .form-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border);
        }

        .form-header h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.4rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .form-header p {
            color: var(--medium-gray);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-gray);
            font-size: 0.95rem;
        }

        .required::after {
            content: ' *';
            color: #EF4444;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--medium-gray);
            font-size: 1.1rem;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            color: var(--black);
            background-color: var(--white);
            transition: var(--transition);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236B7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 20px;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-select {
            cursor: pointer;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--medium-gray);
            cursor: pointer;
            font-size: 1.1rem;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .terms-privacy {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin: 25px 0;
            padding: 20px;
            background-color: rgba(79, 70, 229, 0.03);
            border-radius: 10px;
            border: 1px solid rgba(79, 70, 229, 0.1);
        }

        .terms-privacy input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            margin-top: 3px;
            flex-shrink: 0;
        }

        .terms-privacy label {
            font-size: 0.9rem;
            color: var(--medium-gray);
            line-height: 1.5;
        }

        .terms-privacy a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .terms-privacy a:hover {
            text-decoration: underline;
        }

        .signup-button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary), var(--purple));
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2);
        }

        .signup-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: var(--medium-gray);
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: var(--border);
        }

        .divider span {
            padding: 0 15px;
            font-size: 0.9rem;
        }

        .social-signup {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
        }

        .social-btn {
            flex: 1;
            padding: 14px;
            border: 2px solid var(--border);
            border-radius: 10px;
            background-color: var(--white);
            color: var(--dark-gray);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 0.95rem;
        }

        .social-btn:hover {
            border-color: var(--primary);
            background-color: var(--light-gray);
        }

        .login-link {
            text-align: center;
            color: var(--medium-gray);
            font-size: 0.95rem;
            padding-top: 10px;
            border-top: 1px solid var(--border);
            margin-top: 10px;
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
            transition: var(--transition);
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .signup-container {
                grid-template-columns: 1fr;
                max-width: 700px;
                max-height: 90vh;
            }

            .signup-visual-section {
                min-height: 300px;
                padding: 30px;
            }

            .visual-content h2 {
                font-size: 1.8rem;
            }

            .visual-icon {
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }

            .signup-form-section {
                max-height: 70vh;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .signup-container {
                max-height: 95vh;
            }

            .user-type-options {
                flex-direction: column;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .social-signup {
                flex-direction: column;
            }

            .welcome-text h1 {
                font-size: 1.8rem;
            }

            .form-header h3 {
                font-size: 1.2rem;
            }

            .signup-form-section {
                padding: 30px;
                max-height: 75vh;
            }

            .signup-visual-section {
                padding: 25px;
            }
        }

        @media (max-width: 576px) {
            .signup-container {
                border-radius: 15px;
            }

            .signup-form-section {
                padding: 25px 20px;
            }

            .signup-visual-section {
                min-height: 250px;
                padding: 20px;
            }

            .visual-content h2 {
                font-size: 1.6rem;
            }

            .visual-icon {
                width: 70px;
                height: 70px;
                font-size: 1.8rem;
                margin-bottom: 20px;
            }

            .user-type-preview {
                padding: 20px;
            }

            .welcome-text h1 {
                font-size: 1.6rem;
            }

            .logo {
                font-size: 1.6rem;
            }

            .form-input, .form-select {
                padding: 12px 16px 12px 48px;
                font-size: 0.95rem;
            }
        }

        @media (max-height: 800px) {
            .signup-container {
                max-height: 95vh;
            }

            .signup-form-section {
                max-height: 80vh;
            }

            .signup-visual-section {
                min-height: 250px;
            }
        }

        /* Additional fixes for very small screens */
        @media (max-width: 400px) {
            .signup-form-section {
                padding: 20px 15px;
            }

            .form-header h3 {
                font-size: 1.1rem;
            }

            .form-header p {
                font-size: 0.9rem;
            }

            .user-type-option {
                padding: 15px 10px;
            }

            .terms-privacy {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <!-- Left Side - Visual -->
        <div class="signup-visual-section">
            <div class="bg-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>

            <div class="visual-content">
                <div class="visual-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h2>Start Your Learning Journey</h2>
                <p>Join thousands of students, parents, and teachers using Nexa AI to transform education in Ghana.</p>
                
                <div class="user-type-preview student-preview" id="previewPanel">
                    <div class="preview-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3>For Students</h3>
                    <p>Fun interactive learning with games, AI tutor, and personalized assignments designed just for you.</p>
                </div>
            </div>
        </div>

        <!-- Right Side - Signup Form -->
        <div class="signup-form-section">
            <a href="index.html" class="back-home">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>

            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-robot"></i>
                </div>
                NEXA AI
            </div>

            <div class="welcome-text">
                <h1>Create Your Account</h1>
                <p>Select your role to get started with Nexa AI</p>
            </div>

            <div class="form-container">
                <div class="user-type-selector">
                    <label>Select Your Role:</label>
                    <div class="user-type-options">
                        <div class="user-type-option student selected" data-type="student">
                            <i class="fas fa-user-graduate"></i>
                            <span>Student</span>
                        </div>
                        <div class="user-type-option parent" data-type="parent">
                            <i class="fas fa-users"></i>
                            <span>Parent</span>
                        </div>
                        <div class="user-type-option teacher" data-type="teacher">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span>Teacher</span>
                        </div>
                    </div>
                </div>

                <!-- Student Registration Form -->
                <form id="studentForm" class="form-section active" method="POST" novalidate>
                    <div class="form-header">
                        <h3><i class="fas fa-user-graduate" style="color: var(--primary);"></i> Student Registration</h3>
                        <p>Create your student account to start learning with fun games and AI-powered lessons</p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="studentFirstName" class="required">First Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="studentFirstName" name="first_name" class="form-input" placeholder="Enter your first name" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="studentLastName" class="required">Last Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="studentLastName" name="last_name" class="form-input" placeholder="Enter your last name" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="studentUsername" class="required">Username</label>
                        <div class="input-with-icon">
                            <i class="fas fa-at"></i>
                            <input type="text" id="studentUsername" name="username" class="form-input" placeholder="Choose a username" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="studentGender" class="required">Gender</label>
                            <div class="input-with-icon">
                                <i class="fas fa-venus-mars"></i>
                                <select id="studentGender" name="gender" class="form-select" required>
                                    <option value="" selected disabled>Select gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                    <option value="prefer-not-to-say">Prefer not to say</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="studentClass" class="required">Class/Grade</label>
                            <div class="input-with-icon">
                                <i class="fas fa-graduation-cap"></i>
                                <select id="studentClass" name="class" class="form-select" required>
                                    <option value="" selected disabled>Select class/grade</option>
                                    <option value="class-1">Class 1</option>
                                    <option value="class-2">Class 2</option>
                                    <option value="class-3">Class 3</option>
                                    <option value="class-4">Class 4</option>
                                    <option value="class-5">Class 5</option>
                                    <option value="class-6">Class 6</option>
                                    <option value="jhs-1">JHS 1</option>
                                    <option value="jhs-2">JHS 2</option>
                                    <option value="jhs-3">JHS 3</option>
                                    <option value="shs-1">SHS 1</option>
                                    <option value="shs-2">SHS 2</option>
                                    <option value="shs-3">SHS 3</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="studentPassword" class="required">Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="studentPassword" name="password" class="form-input" placeholder="Create a password" required autocomplete="new-password">
                                <button type="button" class="password-toggle" data-target="studentPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="studentConfirmPassword" class="required">Confirm Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="studentConfirmPassword" name="confirm_password" class="form-input" placeholder="Confirm your password" required autocomplete="new-password">
                                <button type="button" class="password-toggle" data-target="studentConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="terms-privacy">
                        <input type="checkbox" id="studentTerms" name="terms" required>
                        <label for="studentTerms">
                            I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>. I understand that Nexa AI will use my information in accordance with these policies to provide educational services.
                        </label>
                    </div>

                    <button type="submit" class="signup-button student-submit">
                        Create Student Account
                    </button>
                </form>

                <!-- Parent Registration Form -->
                <form id="parentForm" class="form-section" method="POST" novalidate>
                    <div class="form-header">
                        <h3><i class="fas fa-users" style="color: var(--secondary);"></i> Parent Registration</h3>
                        <p>Create your parent account to monitor your child's progress and support their learning journey</p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="parentFirstName" class="required">First Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="parentFirstName" name="first_name" class="form-input" placeholder="Enter your first name" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="parentLastName" class="required">Last Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="parentLastName" name="last_name" class="form-input" placeholder="Enter your last name" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="parentEmail" class="required">Email Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="parentEmail" name="email" class="form-input" placeholder="Enter your email address" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="childUsername" class="required">Child's Username</label>
                        <div class="input-with-icon">
                            <i class="fas fa-child"></i>
                            <input type="text" id="childUsername" name="child_username" class="form-input" placeholder="Enter your child's Nexa AI username" required>
                        </div>
                        <small style="color: var(--medium-gray); font-size: 0.85rem; display: block; margin-top: 5px;">
                            If your child doesn't have an account yet, you can create one after registering
                        </small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="parentPassword" class="required">Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="parentPassword" name="password" class="form-input" placeholder="Create a password" required autocomplete="new-password">
                                <button type="button" class="password-toggle" data-target="parentPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="parentConfirmPassword" class="required">Confirm Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="parentConfirmPassword" name="confirm_password" class="form-input" placeholder="Confirm your password" required autocomplete="new-password">
                                <button type="button" class="password-toggle" data-target="parentConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="terms-privacy">
                        <input type="checkbox" id="parentTerms" name="terms" required>
                        <label for="parentTerms">
                            I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>. I understand that Nexa AI will use my information in accordance with these policies to provide educational services.
                        </label>
                    </div>

                    <button type="submit" class="signup-button parent-submit">
                        Create Parent Account
                    </button>
                </form>

                <!-- Teacher Registration Form -->
                <form id="teacherForm" class="form-section" method="POST" novalidate>
                    <div class="form-header">
                        <h3><i class="fas fa-chalkboard-teacher" style="color: var(--accent);"></i> Teacher Registration</h3>
                        <p>Create your teacher account to access classroom tools, analytics, and teaching resources</p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="teacherFirstName" class="required">First Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="teacherFirstName" name="first_name" class="form-input" placeholder="Enter your first name" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="teacherLastName" class="required">Last Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="teacherLastName" name="last_name" class="form-input" placeholder="Enter your last name" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="teacherEmail" class="required">Email Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="teacherEmail" name="email" class="form-input" placeholder="Enter your school email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="teacherSubject" class="required">Subject You Teach</label>
                        <div class="input-with-icon">
                            <i class="fas fa-book-open"></i>
                            <select id="teacherSubject" name="subject" class="form-select" required>
                                <option value="" selected disabled>Select main subject</option>
                                <option value="mathematics">Mathematics</option>
                                <option value="english">English Language</option>
                                <option value="science">Science</option>
                                <option value="computing">Computing/ICT</option>
                                <option value="social-studies">Social Studies</option>
                                <option value="ghanaian-language">Ghanaian Language</option>
                                <option value="creative-arts">Creative Arts</option>
                                <option value="physical-education">Physical Education</option>
                                <option value="rme">Religious & Moral Education</option>
                                <option value="history">History</option>
                                <option value="geography">Geography</option>
                                <option value="french">French</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="teacherSchool">School Name (Optional)</label>
                        <div class="input-with-icon">
                            <i class="fas fa-school"></i>
                            <input type="text" id="teacherSchool" name="school" class="form-input" placeholder="Enter your school name">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="teacherPassword" class="required">Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="teacherPassword" name="password" class="form-input" placeholder="Create a password" required autocomplete="new-password">
                                <button type="button" class="password-toggle" data-target="teacherPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="teacherConfirmPassword" class="required">Confirm Password</label>
                                <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="teacherConfirmPassword" name="confirm_password" class="form-input" placeholder="Confirm your password" required autocomplete="new-password">
                                <button type="button" class="password-toggle" data-target="teacherConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="terms-privacy">
                        <input type="checkbox" id="teacherTerms" name="terms" required>
                        <label for="teacherTerms">
                            I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>. I understand that Nexa AI will use my information in accordance with these policies to provide educational services.
                        </label>
                    </div>

                    <button type="submit" class="signup-button teacher-submit">
                        Create Teacher Account
                    </button>
                </form>

                <div class="divider">
                    <span>Or sign up with</span>
                </div>

                <div class="social-signup">
                    <button type="button" class="social-btn">
                        <i class="fab fa-google"></i>
                        Google
                    </button>
                    <button type="button" class="social-btn">
                        <i class="fab fa-apple"></i>
                        Apple
                    </button>
                </div>

                <p class="login-link">
                    Already have an account?
                    <a href="login.php">Sign In</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // User type selection
        const userTypeOptions = document.querySelectorAll('.user-type-option');
        const formSections = document.querySelectorAll('.form-section');
        const previewPanel = document.getElementById('previewPanel');
        
        // Preview content for each user type
        const previewContent = {
            student: {
                title: "For Students",
                description: "Fun interactive learning with games, AI tutor, and personalized assignments designed just for you.",
                icon: "fas fa-user-graduate",
                colorClass: "student-preview"
            },
            parent: {
                title: "For Parents", 
                description: "Monitor your child's progress, set learning goals, and support their educational journey from anywhere.",
                icon: "fas fa-users",
                colorClass: "parent-preview"
            },
            teacher: {
                title: "For Teachers",
                description: "Access classroom tools, student analytics, lesson planning resources, and a community of educators.",
                icon: "fas fa-chalkboard-teacher",
                colorClass: "teacher-preview"
            }
        };
        
        userTypeOptions.forEach(option => {
            option.addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                
                // Remove selected class from all options
                userTypeOptions.forEach(opt => opt.classList.remove('selected'));
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Hide all form sections
                formSections.forEach(section => section.classList.remove('active'));
                
                // Show the selected form section
                document.getElementById(`${type}Form`).classList.add('active');
                
                // Update preview panel
                updatePreview(type);
                
                // Update signup button color based on user type
                updateSignupButton(type);
                
                // Scroll to top of form section for better UX
                document.querySelector('.form-section.active').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });
        
        function updatePreview(type) {
            const content = previewContent[type];
            
            // Update preview panel
            previewPanel.className = `user-type-preview ${content.colorClass}`;
            previewPanel.innerHTML = `
                <div class="preview-icon">
                    <i class="${content.icon}"></i>
                </div>
                <h3>${content.title}</h3>
                <p>${content.description}</p>
            `;
        }
        
        function updateSignupButton(type) {
            const signupButton = document.querySelector(`.${type}-submit`);
            
            // Reset all button styles first
            document.querySelectorAll('.signup-button').forEach(btn => {
                btn.style.background = '';
                btn.style.boxShadow = '';
            });
            
            // Set gradient based on user type
            if (type === 'student') {
                signupButton.style.background = 'linear-gradient(135deg, var(--primary), var(--purple))';
                signupButton.style.boxShadow = '0 4px 15px rgba(79, 70, 229, 0.2)';
            } else if (type === 'parent') {
                signupButton.style.background = 'linear-gradient(135deg, var(--secondary), #34D399)';
                signupButton.style.boxShadow = '0 4px 15px rgba(16, 185, 129, 0.2)';
            } else if (type === 'teacher') {
                signupButton.style.background = 'linear-gradient(135deg, var(--accent), #FBBF24)';
                signupButton.style.boxShadow = '0 4px 15px rgba(245, 158, 11, 0.2)';
            }
        }
        
        // Password toggle functionality
        document.querySelectorAll('.password-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                
                if (!passwordInput) return;
                
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle eye icon
                const eyeIcon = this.querySelector('i');
                if (type === 'text') {
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                } else {
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            });
        });
        
        // Form submission handlers
        document.getElementById('studentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm('student');
        });
        
        document.getElementById('parentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm('parent');
        });
        
        document.getElementById('teacherForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm('teacher');
        });
        
        // Submit form function
        function submitForm(userType) {
            const form = document.getElementById(userType + 'Form');
            const submitBtn = form.querySelector('.signup-button');
            const originalText = submitBtn.innerHTML;
            
            // Get form data
            const formData = new FormData(form);
            formData.append('user_type', userType);
            
            // Validate select fields (they need a selected option, not just placeholder)
            const selectFields = form.querySelectorAll('select[required]');
            let hasSelectError = false;
            
            selectFields.forEach(select => {
                if (!select.value) {
                    select.style.borderColor = '#EF4444';
                    select.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
                    hasSelectError = true;
                } else {
                    select.style.borderColor = '';
                    select.style.boxShadow = '';
                }
            });
            
            if (hasSelectError) {
                showError('Please select all required options');
                return;
            }
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
            submitBtn.disabled = true;
            
            // Send AJAX request
           fetch('../auth/register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    submitBtn.innerHTML = '<i class="fas fa-check"></i> ' + data.message;
                    
                    // Remove any previous error highlights
                    form.querySelectorAll('.form-input, .form-select').forEach(input => {
                        input.style.borderColor = '';
                        input.style.boxShadow = '';
                    });
                    
                    // Redirect after delay
                    setTimeout(() => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    }, 1500);
                } else {
                    // Show error message
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    // Show error to user
                    showError(data.message);
                    
                    // Highlight error fields
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            let input;
                            
                            // Handle field name mapping
                            if (field === 'confirm_password') {
                                input = form.querySelector(`[name="${field}"]`);
                            } else if (field === 'general') {
                                // General error, don't highlight specific field
                                return;
                            } else {
                                // Try to find input by name
                                input = form.querySelector(`[name="${field}"]`);
                                
                                // If not found by exact name, try camelCase or other variations
                                if (!input) {
                                    const fieldId = userType + field.charAt(0).toUpperCase() + field.slice(1);
                                    input = document.getElementById(fieldId);
                                }
                            }
                            
                            if (input) {
                                input.style.borderColor = '#EF4444';
                                input.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
                                
                                // Clear error highlight after 5 seconds
                                setTimeout(() => {
                                    input.style.borderColor = '';
                                    input.style.boxShadow = '';
                                }, 5000);
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                showError('Network error. Please try again.');
            });
        }
        
        // Function to show error message
        function showError(message) {
            // Create or update error toast
            let toast = document.getElementById('errorToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'errorToast';
                toast.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: #EF4444;
                    color: white;
                    padding: 15px 20px;
                    border-radius: 10px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                    z-index: 10000;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    animation: slideIn 0.3s ease-out;
                    max-width: 400px;
                `;
                document.body.appendChild(toast);
                
                // Add CSS for animations
                if (!document.querySelector('#toast-animations')) {
                    const style = document.createElement('style');
                    style.id = 'toast-animations';
                    style.textContent = `
                        @keyframes slideIn {
                            from { transform: translateX(100%); opacity: 0; }
                            to { transform: translateX(0); opacity: 1; }
                        }
                        @keyframes slideOut {
                            from { transform: translateX(0); opacity: 1; }
                            to { transform: translateX(100%); opacity: 0; }
                        }
                    `;
                    document.head.appendChild(style);
                }
            }
            
            toast.innerHTML = `
                <i class="fas fa-exclamation-circle"></i>
                <span>${message}</span>
            `;
            
            // Remove toast after 5 seconds
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, 5000);
        }
        
        // Social signup buttons
        document.querySelectorAll('.social-btn').forEach(button => {
            button.addEventListener('click', function() {
                const platform = this.textContent.trim();
                
                // Determine current user type
                const userType = document.querySelector('.user-type-option.selected').getAttribute('data-type');
                const userTypeName = {
                    'student': 'Student',
                    'parent': 'Parent', 
                    'teacher': 'Teacher'
                }[userType];
                
                showError(`In a real app, this would redirect to ${platform} authentication for ${userTypeName} account`);
            });
        });
        
        // Initialize preview panel
        updatePreview('student');
        updateSignupButton('student');
        
        // Auto-adjust container height on load
        window.addEventListener('load', function() {
            const formSection = document.querySelector('.signup-form-section');
            const visualSection = document.querySelector('.signup-visual-section');
            
            // Set form section height to match visual section on larger screens
            if (window.innerWidth > 1024) {
                formSection.style.maxHeight = '95vh';
            }
        });
        
        // Clear validation styles when user interacts with inputs
        document.querySelectorAll('.form-input, .form-select').forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = '';
                this.style.boxShadow = '';
            });
            
            input.addEventListener('change', function() {
                this.style.borderColor = '';
                this.style.boxShadow = '';
            });
        });
    </script>
</body>
</html>