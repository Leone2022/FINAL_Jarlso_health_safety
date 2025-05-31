<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Jarlso Health & Safety</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <style>
        /* Variables for consistent colors */
        :root {
            --primary-color: #1e3c72; /* Dark blue - main color */
            --primary-light: #2a5298; /* Medium dark blue */
            --primary-dark: #0F2942; /* Very dark blue */
            --accent-color: #E53E3E; /* Complementary red accent */
            --text-light: #F0F8FF;
            --text-dark: #2D3748;
            --bg-light: #FFFFFF;
            --bg-grey: #F7FAFC;
            --input-border: #CBD5E0;
            --box-shadow: 0 10px 25px rgba(30, 60, 114, 0.15);
            --transition: all 0.3s ease;
        }
        
        /* Base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color), var(--primary-light));
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-light);
            position: relative;
            padding: 20px;
        }
        
        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        .page-wrapper {
            width: 100%;
            max-width: 450px;
        }
        
        .container {
            background: var(--bg-light);
            width: 100%;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: var(--box-shadow);
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }
        
        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(30, 60, 114, 0.2);
        }
        
        .container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-dark), var(--primary-color), var(--primary-light));
        }
        
        .logo {
            text-align: center;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .logo-image {
            width: 70px;
            height: 70px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(30, 60, 114, 0.2);
        }
        
        .logo-image i {
            font-size: 35px;
            color: var(--bg-light);
        }
        
        .logo-text {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        h1 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            position: relative;
            display: inline-block;
            font-weight: 600;
        }
        
        h1::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: var(--primary-color);
        }
        
        .subtitle {
            color: var(--text-dark);
            opacity: 0.7;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        
        .input-group {
            margin-bottom: 1.5rem;
            text-align: left;
            position: relative;
        }
        
        .input-group label {
            display: block;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 38px;
            color: var(--primary-color);
        }
        
        .input-group input, 
        .input-group select {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            outline: none;
            font-size: 1rem;
            transition: var(--transition);
            background-color: var(--bg-grey);
            color: var(--text-dark);
        }
        
        .input-group input:focus, 
        .input-group select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
            background-color: var(--bg-light);
        }
        
        .input-group input::placeholder, 
        .input-group select::placeholder {
            color: #718096;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            font-size: 1rem;
            background: var(--primary-color);
            color: var(--text-light);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: var(--transition);
            margin-top: 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        
        .btn i {
            font-size: 1.1rem;
        }
        
        .btn:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 60, 114, 0.2);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .error {
            color: var(--accent-color);
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        
        .footer {
            margin-top: 2rem;
            font-size: 0.85rem;
            color: var(--text-dark);
            opacity: 0.7;
        }
        
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.15);
            color: var(--text-light);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .back-button:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }
        
        .back-button i {
            font-size: 1.2rem;
        }
        
        .forgot-password {
            text-align: right;
            font-size: 0.85rem;
            margin-top: -10px;
            margin-bottom: 20px;
        }
        
        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .forgot-password a:hover {
            color: var(--primary-light);
            text-decoration: underline;
        }
        
        .admin-badge {
            display: inline-block;
            padding: 5px 12px;
            background: var(--primary-color);
            color: var(--text-light);
            border-radius: 30px;
            font-size: 0.8rem;
            margin-top: 15px;
            font-weight: 500;
        }
        
        /* Pulse animation for the login button */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(30, 60, 114, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(30, 60, 114, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(30, 60, 114, 0);
            }
        }
        
        .btn-pulse {
            animation: pulse 2s infinite;
        }
        
        /* Responsive styles */
        @media (max-width: 480px) {
            .container {
                padding: 2rem;
            }
            
            h1 {
                font-size: 1.6rem;
            }
            
            .input-group {
                margin-bottom: 1.2rem;
            }
            
            .input-group input, 
            .input-group select {
                padding: 10px 15px 10px 40px;
                font-size: 0.95rem;
            }
            
            .btn {
                padding: 12px;
            }
            
            .logo-image {
                width: 60px;
                height: 60px;
            }
            
            .logo-image i {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-button" title="Back to Homepage">
        <i class="fas fa-arrow-left"></i>
    </a>
    
    <div class="page-wrapper">
        <div class="container">
            <div class="logo">
                <div class="logo-image">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="logo-text">Jarlso H&S</div>
                <div class="admin-badge">Administrator Portal</div>
            </div>
            
            <h1>Admin Login</h1>
            <p class="subtitle">Access the Health & Safety Management System</p>
            
            <form method="post" action="process_admin_login.php" id="loginForm">
                <div class="input-group">
                    <label for="role">Role</label>
                    <i class="fas fa-user-shield"></i>
                    <select name="role" id="role" required>
                        <option value="">-- Select Role --</option>
                        <option value="Health & Safety Manager">Health & Safety Manager</option>
                        <option value="HOD">HOD</option>
                        <option value="Manager">Manager</option>
                        <option value="CEO">CEO</option>
                    </select>
                </div>
                
                <div class="input-group">
                    <label for="username">Username</label>
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <div class="forgot-password">
                    <a href="admin_reset_password.php">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn btn-pulse" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Log In as Administrator
                </button>
            </form>
            
            <div class="footer">
                <p>© 2025 Jarlso Health & Safety. All rights reserved.</p>
            </div>
        </div>
    </div>
    
    <script>
        // Remove pulse animation when user interacts with the form
        document.getElementById('loginForm').addEventListener('input', function() {
            document.getElementById('loginBtn').classList.remove('btn-pulse');
        });
        
        // Add subtle interaction to the form elements
        const inputs = document.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-5px)';
                this.parentElement.style.transition = 'transform 0.3s';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>