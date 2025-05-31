<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login_teamleader.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jarlso H&S | Team Leader Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <style>
        :root {
            --primary-color: #1e3c72;
            --primary-light: #2a5298;
            --primary-dark: #0F2942;
            --accent-color: #E53E3E;
            --text-light: #F0F8FF;
            --text-dark: #2D3748;
            --bg-light: #FFFFFF;
            --bg-grey: #F7FAFC;
            --input-border: #CBD5E0;
            --box-shadow: 0 10px 25px rgba(30, 60, 114, 0.15);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            background-attachment: fixed;
            min-height: 100vh;
            color: var(--text-dark);
            position: relative;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 80px;
        }

        .dashboard-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: var(--primary-color);
            color: var(--text-light);
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            background: var(--bg-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-logo i {
            font-size: 20px;
            color: var(--primary-color);
        }

        .brand-name {
            font-weight: 600;
            font-size: 1.2rem;
        }

        .user-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            padding: 5px 10px;
            border-radius: 30px;
            font-size: 0.8rem;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: var(--text-light);
            border: none;
            padding: 5px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .dashboard-container {
            background: var(--bg-light);
            width: 100%;
            max-width: 800px;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            animation: fadeIn 0.5s ease-in-out;
        }

        .dashboard-content {
            padding: 25px;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dashboard-title {
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dashboard-title i {
            font-size: 1.6rem;
        }

        .welcome-message {
            color: var(--text-dark);
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .instructions {
            background-color: var(--bg-grey);
            padding: 15px;
            border-left: 4px solid var(--primary-color);
            border-radius: 5px;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }

        .instructions strong {
            color: var(--primary-color);
        }

        .forms-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.3rem;
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .forms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 15px;
        }

        .form-card {
            background: var(--bg-grey);
            border-radius: 8px;
            transition: var(--transition);
            overflow: hidden;
        }

        .form-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(30, 60, 114, 0.1);
        }

        .form-link {
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: var(--text-dark);
            height: 100%;
        }

        .form-icon {
            background: var(--primary-color);
            color: var(--text-light);
            padding: 20px 0;
            text-align: center;
        }

        .form-icon i {
            font-size: 2rem;
        }

        .form-title {
            padding: 15px;
            text-align: center;
            font-weight: 500;
            font-size: 1rem;
            border-top: 1px solid rgba(30, 60, 114, 0.1);
        }

        .waste-management {
            margin-top: 20px;
            border-top: 1px solid var(--input-border);
            padding-top: 20px;
        }

        .waste-container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 15px;
        }

        .waste-image-container {
            background: var(--bg-grey);
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: var(--transition);
            overflow: hidden;
            border: 2px solid var(--primary-color);
        }

        .waste-image-container:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(30, 60, 114, 0.2);
        }

        .waste-image {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .waste-description {
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-dark);
            max-width: 300px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: var(--text-dark);
            font-size: 0.8rem;
            opacity: 0.7;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
                padding-top: 70px;
            }
            
            .dashboard-header {
                padding: 10px 15px;
            }
            
            .brand-name {
                font-size: 1rem;
            }
            
            .dashboard-content {
                padding: 20px;
            }
            
            .dashboard-title {
                font-size: 1.5rem;
            }
            
            .welcome-message {
                font-size: 1rem;
            }
            
            .user-badge {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .dashboard-content {
                padding: 15px;
            }
            
            .forms-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-title {
                font-size: 1.3rem;
            }
            
            .user-controls {
                gap: 8px;
            }
            
            .logout-btn span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Dashboard Header -->
    <header class="dashboard-header">
        <div class="brand">
            <div class="brand-logo">
                <i class="fas fa-hard-hat"></i>
            </div>
            <div class="brand-name">Jarlso H&S</div>
        </div>
        <div class="user-controls">
            <div class="user-badge">
                <i class="fas fa-user"></i>
                <span>Team Leader</span>
            </div>
            <a href="register_team_leader.html" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </header>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <div class="dashboard-content">
            <h1 class="dashboard-title">
                <i class="fas fa-tachometer-alt"></i>
                Team Leader Dashboard
            </h1>
            
            <p class="welcome-message">Welcome, <?php echo isset($_SESSION['name']) ? $_SESSION['name'] : 'Team Leader'; ?>! Here you can access and submit all required documentation.</p>
            
            <div class="instructions">
                <strong>Important:</strong> All Team Leaders must complete each form below with accurate information. Regular submission of these forms ensures compliance with health and safety regulations.
            </div>

            <!-- Forms Section -->
            <div class="forms-section">
                <h2 class="section-title">
                    <i class="fas fa-clipboard-list"></i>
                    Required Forms
                </h2>
                
                <div class="forms-grid">
                    <div class="form-card">
                        <a href="toolbox_form.html" class="form-link">
                            <div class="form-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            <div class="form-title">Toolbox Form</div>
                        </a>
                    </div>
                    
                    <div class="form-card">
                        <a href="daily_risk_assessment_form.html" class="form-link">
                            <div class="form-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="form-title">Risk Assessment</div>
                        </a>
                    </div>
                    
                    <div class="form-card">
                        <a href="first_aid_kit.html" class="form-link">
                            <div class="form-icon">
                                <i class="fas fa-first-aid"></i>
                            </div>
                            <div class="form-title">First Aid Kit</div>
                        </a>
                    </div>
                    
                    <div class="form-card">
                        <a href="Incident_report_form.html" class="form-link">
                            <div class="form-icon">
                                <i class="fas fa-file-medical-alt"></i>
                            </div>
                            <div class="form-title">Incident Report</div>
                        </a>
                    </div>
                    
                    <div class="form-card">
                        <a href="ppe_register_form.html" class="form-link">
                            <div class="form-icon">
                                <i class="fas fa-hard-hat"></i>
                            </div>
                            <div class="form-title">PPE Register</div>
                        </a>
                    </div>
                    
                    <div class="form-card">
                        <a href="site_induction_form.html" class="form-link">
                            <div class="form-icon">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="form-title">Site Induction</div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Waste Management Section -->
            <div class="waste-management">
                <h2 class="section-title">
                    <i class="fas fa-recycle"></i>
                    Waste Management
                </h2>
                
                <div class="waste-container">
                    <div class="waste-image-container" onclick="redirectToWasteManagement()">
                        <img src="images/gunoiera2-removebg-preview.png" alt="Waste Management" class="waste-image">
                    </div>
                    <p class="waste-description">
                        Access the waste management system to record and track all waste disposal activities
                    </p>
                </div>
            </div>

            <div class="footer">
                <p>&copy; 2025 Jarlso Health & Safety Management System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        function redirectToWasteManagement() {
            window.location.href = "waste_management.html";
        }
    </script>
</body>
</html>