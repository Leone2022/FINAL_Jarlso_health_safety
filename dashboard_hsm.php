<?php
session_start();

// Check if the session is valid
if (!isset($_SESSION['username'])) {
    // If session is not valid, redirect to the login page
    header("Location: login_admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            min-height: 100vh;
            background: #f4f7fc;
        }
        .sidebar {
            width: 200px;
            background-color: #2a5298;
            color: #fff;
            position: fixed;
            top: 0;
            bottom: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .sidebar h2 {
            text-align: center;
            font-size: 1.5em;
            margin-bottom: 20px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin-bottom: 15px;
        }
        .sidebar ul li a {
            display: block;
            padding: 12px;
            color: #fff;
            text-decoration: none;
            background-color: #1e3c72;
            text-align: center;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .sidebar ul li a:hover {
            background-color: #16325c;
        }
        .main-content {
            margin-left: 220px;
            padding: 40px;
            width: calc(100% - 220px);
            background-color: #f4f7fc;
            text-align: center;
        }
        .main-content h1 {
            color: #2a5298;
            font-size: 2em;
            margin-bottom: 20px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            justify-items: center;
        }
        .card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 250px;
        }
        .card h3 {
            color: #2a5298;
            margin-bottom: 10px;
        }
        .card a {
            display: inline-block;
            margin-top: 10px;
            background-color: #2a5298;
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .card a:hover {
            background-color: #1e3c72;
        }
        .logout-btn {
            position: fixed;
            bottom: 30px;
            right: 50px;
            background-color: #e74c3c;
            color: #fff;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.1em;
            transition: background 0.3s;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="view_team_leaders.php"><i class="fas fa-users"></i> Team Leaders</a></li>
            <li><a href="register_team_leader.html"><i class="fas fa-user-plus"></i> Add Leader</a></li>
            <li><a href="all_reports.php"><i class="fas fa-file-alt"></i> Reports</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Welcome, Admin</h1>
        <div class="dashboard-grid">
            <div class="card">
                <h3>Team Leaders</h3>
                <p>Manage and view all registered team leaders.</p>
                <a href="view_team_leaders.php">View</a>
            </div>
            <div class="card">
                <h3>Add Leader</h3>
                <p>Quickly add new team leaders.</p>
                <a href="register_team_leader.html">Add</a>
            </div>
            <div class="card">
                <h3>Reports</h3>
                <p>View all reports submitted by team leaders.</p>
                <a href="all_reports.php">View</a>
            </div>
        </div>
    </div>

    <a href="login_admin.php" class="logout-btn">Logout</a>
</body>
</html>
