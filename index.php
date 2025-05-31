<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jarlso Health & Safety Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <style>
        /* Reset and base styles */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary-color: #1e3c72; /* Dark blue - matching other pages */
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
        
        html {
            scroll-behavior: smooth;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            overflow-x: hidden;
        }
        
        /* Header and Navigation */
        header {
            background-color: rgba(255, 255, 255, 0.95);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }
        
        header.scrolled {
            padding: 5px 0;
            background-color: rgba(255, 255, 255, 0.98);
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 50px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo img {
            height: 50px;
        }
        
        .logo span {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-left: 10px;
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
        }
        
        .nav-links a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            font-size: 1rem;
        }
        
        .nav-links a:hover {
            color: var(--primary-color);
        }
        
        .auth-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-light), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 60, 114, 0.3);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }
        
        .btn-secondary:hover {
            background: rgba(30, 60, 114, 0.1);
            transform: translateY(-2px);
        }
        
        .btn-accent {
            background: linear-gradient(135deg, #E53E3E, #ED8936);
            color: white;
            border: none;
        }
        
        .btn-accent:hover {
            background: linear-gradient(135deg, #E53E3E, #C05621);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(229, 62, 62, 0.3);
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--primary-color);
            cursor: pointer;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(30, 60, 114, 0.9), rgba(22, 93, 160, 0.9)), url('images/health-safety-hero.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 180px 50px 100px;
            text-align: center;
            position: relative;
        }
        
        .hero-content {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }
        
        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
        }
        
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-top: 60px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 5px;
            display: block;
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        /* Features Section */
        .features {
            padding: 100px 50px;
            background: var(--bg-grey);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            color: var(--primary-color);
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .section-title h2::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 70px;
            height: 3px;
            background: var(--primary-color);
        }
        
        .section-title p {
            max-width: 700px;
            margin: 0 auto;
            color: var(--text-dark);
            opacity: 0.8;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .feature-card {
            background: var(--bg-light);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            padding: 30px;
            text-align: center;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(30, 60, 114, 0.2);
        }
        
        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 1.8rem;
        }
        
        .feature-card h3 {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: var(--text-dark);
        }
        
        .feature-card p {
            color: var(--text-dark);
            opacity: 0.8;
            font-size: 0.95rem;
        }
        
        /* How It Works Section */
        .how-it-works {
            padding: 100px 50px;
            background: var(--bg-light);
        }
        
        .steps {
            display: flex;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
        }
        
        .steps::before {
            content: "";
            position: absolute;
            top: 50px;
            left: 0;
            width: 100%;
            height: 3px;
            background: #e0e0e0;
            z-index: 1;
        }
        
        .step {
            flex: 1;
            text-align: center;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .step h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: var(--text-dark);
        }
        
        .step p {
            color: var(--text-dark);
            opacity: 0.8;
            font-size: 0.95rem;
        }
        
        /* Login Options Section */
        .login-options {
            padding: 100px 50px;
            background: var(--bg-grey);
            text-align: center;
        }
        
        .login-options-container {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 40px;
        }
        
        .login-option-card {
            background: var(--bg-light);
            border-radius: 10px;
            padding: 40px 30px;
            box-shadow: var(--box-shadow);
            flex: 1;
            max-width: 400px;
            transition: var(--transition);
        }
        
        .login-option-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(30, 60, 114, 0.2);
        }
        
        .login-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .login-option-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--text-dark);
        }
        
        .login-option-card p {
            color: var(--text-dark);
            opacity: 0.8;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }
        
        /* CTA Section */
        .cta {
            padding: 100px 50px;
            background: var(--bg-grey);
            text-align: center;
        }
        
        .cta-container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--bg-light);
            border-radius: 10px;
            padding: 50px;
            box-shadow: var(--box-shadow);
        }
        
        .cta h2 {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .cta p {
            color: var(--text-dark);
            opacity: 0.8;
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Footer */
        footer {
            background: var(--primary-color);
            color: white;
            padding: 70px 50px 20px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 50px;
            max-width: 1200px;
            margin: 0 auto 50px;
        }
        
        .footer-column h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-column h3::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 30px;
            height: 2px;
            background: var(--primary-light);
        }
        
        .footer-column p {
            margin-bottom: 20px;
            opacity: 0.8;
            font-size: 0.95rem;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .footer-links i {
            font-size: 0.8rem;
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-icons a {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: var(--transition);
        }
        
        .social-icons a:hover {
            background: var(--primary-light);
            transform: translateY(-3px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }
        
        /* Animation classes */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .fade-in.appear {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Responsive Design */
        @media screen and (max-width: 1024px) {
            .nav-container {
                padding: 15px 30px;
            }
            
            .hero {
                padding: 150px 30px 80px;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .features, .how-it-works, .login-options, .cta {
                padding: 80px 30px;
            }
            
            .steps {
                flex-direction: column;
                gap: 40px;
            }
            
            .steps::before {
                width: 3px;
                height: 100%;
                left: 50%;
                top: 0;
            }
            
            .login-options-container {
                flex-direction: column;
                align-items: center;
            }
            
            .login-option-card {
                width: 100%;
            }
        }
        
        @media screen and (max-width: 768px) {
            .nav-links, .auth-buttons {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .mobile-menu {
                position: fixed;
                top: 80px;
                left: 0;
                width: 100%;
                background: white;
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
                padding: 20px;
                z-index: 100;
                transform: translateY(-100%);
                opacity: 0;
                transition: var(--transition);
            }
            
            .mobile-menu.active {
                transform: translateY(0);
                opacity: 1;
            }
            
            .mobile-menu a {
                display: block;
                padding: 15px;
                color: var(--text-dark);
                text-decoration: none;
                border-bottom: 1px solid #eee;
                font-weight: 500;
            }
            
            .mobile-menu .auth-buttons {
                display: flex;
                margin-top: 20px;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero-stats {
                flex-direction: column;
                gap: 30px;
            }
            
            .hero-buttons {
                flex-direction: column;
                gap: 15px;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
            }
        }
        
        @media screen and (max-width: 480px) {
            .hero h1 {
                font-size: 1.8rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .cta-container {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Header and Navigation -->
    <header id="header">
        <div class="nav-container">
            <div class="logo">
                <img src="images/jarlso.png" alt="Jarlso Logo">
                <span>Jarlso H&S</span>
            </div>
            
            <nav class="nav-links">
                <a href="#features">Features</a>
                <a href="#how-it-works">How It Works</a>
                <a href="#login-options">Login</a>
                <a href="#contact">Contact</a>
            </nav>
            
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <!-- Mobile Menu (Hidden by default) -->
        <!-- <div class="mobile-menu" id="mobileMenu">
            <a href="#features">Features</a>
            <a href="#how-it-works">How It Works</a>
            <a href="#login-options">Login</a>
            <a href="#contact">Contact</a>
        </div> -->
    </header>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="fade-in">Streamlined Health & Safety Management</h1>
            <p class="fade-in">Comprehensive tools to manage workplace safety efficiently. Track, report, and analyze health and safety data all in one place.</p>
            
            <div class="hero-buttons">
                <a href="#login-options" class="btn btn-primary fade-in"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="#features" class="btn btn-secondary fade-in"><i class="fas fa-info-circle"></i> Learn More</a>
            </div>
            
            <div class="hero-stats">
                <div class="stat-item fade-in">
                    <span class="stat-number">98%</span>
                    <span class="stat-label">Compliance Rate</span>
                </div>
                <div class="stat-item fade-in">
                    <span class="stat-number">60%</span>
                    <span class="stat-label">Time Saved</span>
                </div>
                <div class="stat-item fade-in">
                    <span class="stat-number">5000+</span>
                    <span class="stat-label">Reports Processed</span>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features" id="features">
        <div class="section-title">
            <h2 class="fade-in">Key Features</h2>
            <p class="fade-in">Our system provides a comprehensive suite of tools to manage every aspect of workplace health and safety.</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <h3>Incident Reporting</h3>
                <p>Quickly document workplace incidents with our easy-to-use forms. Track follow-up actions and resolution status.</p>
            </div>
            
            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3>Risk Assessment</h3>
                <p>Identify potential hazards, evaluate risks, and implement effective control measures to prevent accidents.</p>
            </div>
            
            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <h3>Toolbox Meetings</h3>
                <p>Document safety briefings and toolbox talks with attendance tracking and topic management.</p>
            </div>
            
            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="fas fa-hard-hat"></i>
                </div>
                <h3>PPE Management</h3>
                <p>Track personal protective equipment inventory, assignments, and inspection schedules.</p>
            </div>
            
            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="fas fa-first-aid"></i>
                </div>
                <h3>First Aid Compliance</h3>
                <p>Monitor first aid kits, certifications, and training to ensure proper emergency response.</p>
            </div>
            
            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Analytics Dashboard</h3>
                <p>Gain insights with comprehensive reporting tools and visual analytics on safety performance.</p>
            </div>
        </div>
    </section>
    
    <!-- How It Works Section -->
    <section class="how-it-works" id="how-it-works">
        <div class="section-title">
            <h2 class="fade-in">How It Works</h2>
            <p class="fade-in">A simple, streamlined process to manage your organization's health and safety requirements.</p>
        </div>
        
        <div class="steps">
            <div class="step fade-in">
                <div class="step-number">1</div>
                <h3>Register</h3>
                <p>Create an account for your organization and add team leaders with specific access permissions.</p>
            </div>
            
            <div class="step fade-in">
                <div class="step-number">2</div>
                <h3>Configure</h3>
                <p>Set up your safety protocols, checklists, and reporting requirements to match your organization's needs.</p>
            </div>
            
            <div class="step fade-in">
                <div class="step-number">3</div>
                <h3>Document</h3>
                <p>Record incidents, inspections, meetings, and compliance activities with our intuitive forms.</p>
            </div>
            
            <div class="step fade-in">
                <div class="step-number">4</div>
                <h3>Analyze</h3>
                <p>Generate reports and gain insights to continually improve your safety performance and reduce risks.</p>
            </div>
        </div>
    </section>
    
    <!-- Login Options Section -->
    <section class="login-options" id="login-options">
        <div class="section-title">
            <h2 class="fade-in">Login Options</h2>
            <p class="fade-in">Choose the appropriate login based on your role in the organization.</p>
        </div>
        
        <div class="login-options-container">
            <div class="login-option-card fade-in">
                <div class="login-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3>Administrator Login</h3>
                <p>Access administrative controls, system configuration, and comprehensive reporting features.</p>
                <a href="login_admin.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Administrator Login</a>
            </div>
            
            <div class="login-option-card fade-in">
                <div class="login-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Team Leader Login</h3>
                <p>Access team management, incident reporting, and safety assessment tools.</p>
                <a href="register_team_leader.html" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Team Leader Login</a>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta">
        <div class="cta-container fade-in">
            <h2>Ready to Transform Your Safety Management?</h2>
            <p>Join thousands of organizations that have streamlined their health and safety processes with our comprehensive system.</p>
            <a href="register_team_leader.html" class="btn btn-accent"><i class="fas fa-user-plus"></i> Register as Team Leader</a>
        </div>
    </section>
    
    <!-- Footer -->
    <footer id="contact">
        <div class="footer-content">
            <div class="footer-column">
                <h3>About Jarlso</h3>
                <p>Jarlso provides cutting-edge health and safety management solutions to organizations worldwide, helping to create safer workplaces through innovation and excellence.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="footer-column">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="#features"><i class="fas fa-chevron-right"></i> Features</a></li>
                    <li><a href="#how-it-works"><i class="fas fa-chevron-right"></i> How It Works</a></li>
                    <li><a href="#login-options"><i class="fas fa-chevron-right"></i> Login</a></li>
                    <li><a href="register_team_leader.html"><i class="fas fa-chevron-right"></i> Register</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Solutions</h3>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Incident Reporting</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Risk Assessment</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Toolbox Meetings</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> PPE Management</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> First Aid Compliance</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Contact Us</h3>
                <ul class="footer-links">
                    <li><a href="tel:+256771691101"><i class="fas fa-phone"></i> +256 77 169 1101</a></li>
                    <li><a href="mailto:leonechirodza@gmail.com"><i class="fas fa-envelope"></i> leonechirodza@gmail.com</a></li>
                    <li><a href="#"><i class="fas fa-user"></i> ENG LEONE CHIRODZA</a></li>
                </ul>
            </div>
        </div>
        
        <div class="copyright">
            <p>&copy; 2025 Jarlso Health & Safety. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
            mobileMenuBtn.innerHTML = mobileMenu.classList.contains('active') ? 
                '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
        });
        
        // Header Scroll Effect
        const header = document.getElementById('header');
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Animation on Scroll
        document.addEventListener('DOMContentLoaded', () => {
            const faders = document.querySelectorAll('.fade-in');
            
            const appearOptions = {
                threshold: 0.15,
                rootMargin: "0px 0px -100px 0px"
            };
            
            const appearOnScroll = new IntersectionObserver((entries, appearOnScroll) => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) {
                        return;
                    } else {
                        entry.target.classList.add('appear');
                        appearOnScroll.unobserve(entry.target);
                    }
                });
            }, appearOptions);
            
            faders.forEach(fader => {
                appearOnScroll.observe(fader);
            });
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    
                    // Close mobile menu if open
                    if (mobileMenu.classList.contains('active')) {
                        mobileMenu.classList.remove('active');
                        mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                    }
                }
            });
        });
        
        // Counter animation for statistics
        const animateCounters = () => {
            const counters = document.querySelectorAll('.stat-number');
            const speed = 200;
            
            counters.forEach(counter => {
                const target = parseInt(counter.innerText.replace(/[^\d]/g, ''));
                let count = 0;
                const updateCount = () => {
                    const increment = target / speed;
                    if (count < target) {
                        if (counter.innerText.includes('%')) {
                            count += increment;
                            counter.innerText = Math.ceil(count) + '%';
                        } else if (counter.innerText.includes('+')) {
                            count += increment;
                            counter.innerText = Math.ceil(count) + '+';
                        } else {
                            count += increment;
                            counter.innerText = Math.ceil(count);
                        }
                        setTimeout(updateCount, 1);
                    } else {
                        if (counter.innerText.includes('%')) {
                            counter.innerText = target + '%';
                        } else if (counter.innerText.includes('+')) {
                            counter.innerText = target + '+';
                        } else {
                            counter.innerText = target;
                        }
                    }
                };
                updateCount();
            });
        };
        
        // Run counter animation when stats are in view
        const statsSection = document.querySelector('.hero-stats');
        const statsSectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    statsSectionObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        statsSectionObserver.observe(statsSection);
    </script>
</body>
</html>