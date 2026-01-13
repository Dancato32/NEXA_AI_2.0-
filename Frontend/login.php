<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login • Nexa AI</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1100px;
            width: 100%;
            background-color: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            min-height: 700px;
        }

        /* Left Side - Form */
        .login-form-section {
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .back-home {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--medium-gray);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 30px;
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

        .welcome-text h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--black);
        }

        .welcome-text p {
            color: var(--medium-gray);
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-gray);
            font-size: 0.95rem;
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

        .form-input {
            width: 100%;
            padding: 16px 16px 16px 48px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            color: var(--black);
            background-color: var(--white);
            transition: var(--transition);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
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
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        .remember-me label {
            font-size: 0.9rem;
            color: var(--medium-gray);
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .login-button {
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
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2);
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
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

        .social-login {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
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
        }

        .social-btn:hover {
            border-color: var(--primary);
            background-color: var(--light-gray);
        }

        .signup-link {
            text-align: center;
            color: var(--medium-gray);
            font-size: 0.95rem;
        }

        .signup-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
            transition: var(--transition);
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        /* Right Side - Visual */
        .login-visual-section {
            background: linear-gradient(135deg, #4F46E5, #8B5CF6);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .visual-content {
            text-align: center;
            color: var(--white);
            z-index: 1;
            position: relative;
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
            max-width: 400px;
        }

        .user-types {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .user-type {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 15px 20px;
            backdrop-filter: blur(10px);
            transition: var(--transition);
            cursor: pointer;
        }

        .user-type:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }

        .user-type i {
            font-size: 1.5rem;
            margin-bottom: 10px;
            display: block;
        }

        .user-type span {
            font-weight: 500;
            font-size: 0.9rem;
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

        /* Error styles */
        .error-message {
            color: #EF4444;
            font-size: 0.85rem;
            margin-top: 5px;
            display: none;
        }
        
        .input-error {
            border-color: #EF4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 600px;
            }

            .login-visual-section {
                display: none;
            }

            .login-form-section {
                padding: 50px 40px;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }

            .login-form-section {
                padding: 40px 25px;
            }

            .welcome-text h1 {
                font-size: 2rem;
            }

            .social-login {
                flex-direction: column;
            }

            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .user-types {
                flex-direction: column;
                align-items: center;
            }

            .user-type {
                width: 100%;
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Login Form -->
        <div class="login-form-section">
            <a href="home.php" class="back-home">
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
                <h1>Welcome Back!</h1>
                <p>Sign in to continue your learning journey with Nexa AI</p>
            </div>

            <form id="loginForm">
                <div class="form-group">
                    <label for="identifier">Email or Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="identifier" name="identifier" class="form-input" placeholder="Enter your email or username" required>
                    </div>
                    <div class="error-message" id="identifier-error"></div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required autocomplete="current-password">
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="error-message" id="password-error"></div>
                </div>

                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="login-button">Sign In</button>
            </form>

            <div class="divider">
                <span>Or continue with</span>
            </div>

            <div class="social-login">
                <button class="social-btn">
                    <i class="fab fa-google"></i>
                    Google
                </button>
                <button class="social-btn">
                    <i class="fab fa-apple"></i>
                    Apple
                </button>
            </div>

            <p class="signup-link">
                Don't have an account?
                <a href="signup.php">Create Account</a>
            </p>
        </div>

        <!-- Right Side - Visual -->
        <div class="login-visual-section">
            <div class="bg-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>

            <div class="visual-content">
                <div class="visual-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h2>Learning Made Fun</h2>
                <p>Join thousands of students, parents, and teachers in Ghana using Nexa AI to transform education.</p>
                
                <div class="user-types">
                    <div class="user-type">
                        <i class="fas fa-user-graduate"></i>
                        <span>For Students</span>
                    </div>
                    <div class="user-type">
                        <i class="fas fa-users"></i>
                        <span>For Parents</span>
                    </div>
                    <div class="user-type">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>For Teachers</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
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
        
        // Form submission
        const loginForm = document.getElementById('loginForm');
        
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const identifier = document.getElementById('identifier').value.trim();
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            
            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => {
                el.style.display = 'none';
                el.textContent = '';
            });
            
            document.querySelectorAll('.form-input').forEach(el => {
                el.classList.remove('input-error');
            });
            
            // Simple validation
            if (!identifier || !password) {
                if (!identifier) {
                    document.getElementById('identifier-error').textContent = 'Email or username is required';
                    document.getElementById('identifier-error').style.display = 'block';
                    document.getElementById('identifier').classList.add('input-error');
                }
                if (!password) {
                    document.getElementById('password-error').textContent = 'Password is required';
                    document.getElementById('password-error').style.display = 'block';
                    document.getElementById('password').classList.add('input-error');
                }
                return;
            }
            
            // Show loading state
            const submitBtn = loginForm.querySelector('.login-button');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
            submitBtn.disabled = true;
            
            // Prepare form data
            const formData = new FormData();
            formData.append('identifier', identifier);
            formData.append('password', password);
            formData.append('remember', remember ? '1' : '0');
            
            // Send AJAX request
            fetch('../auth/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    submitBtn.innerHTML = '<i class="fas fa-check"></i> ' + data.message;
                    
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
                    
                    // Display errors
                    if (data.errors) {
                        if (data.errors.identifier) {
                            document.getElementById('identifier-error').textContent = data.errors.identifier;
                            document.getElementById('identifier-error').style.display = 'block';
                            document.getElementById('identifier').classList.add('input-error');
                        }
                        if (data.errors.password) {
                            document.getElementById('password-error').textContent = data.errors.password;
                            document.getElementById('password-error').style.display = 'block';
                            document.getElementById('password').classList.add('input-error');
                        }
                    }
                    
                    // Clear password field
                    document.getElementById('password').value = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                // Show network error
                const errorEl = document.getElementById('identifier-error');
                errorEl.textContent = 'Network error. Please try again.';
                errorEl.style.display = 'block';
            });
        });
        
        // Social login buttons
        document.querySelectorAll('.social-btn').forEach(button => {
            button.addEventListener('click', function() {
                const platform = this.textContent.trim();
                alert(`In a real app, this would redirect to ${platform} authentication`);
            });
        });
        
        // Forgot password
        document.querySelector('.forgot-password').addEventListener('click', function(e) {
            e.preventDefault();
            alert('Password reset functionality would be implemented here');
        });
        
        // Clear error on input
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('input-error');
                const errorEl = this.parentElement.nextElementSibling;
                if (errorEl && errorEl.classList.contains('error-message')) {
                    errorEl.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>