<?php
session_start();

// Check if the session is valid
if (!isset($_SESSION['username'])) {
    // If session is not valid, redirect to the login page
    header("Location: login_admin.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "health_safety_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Configure report types
$reportTypes = [
    [
        'id' => 'first_aid',
        'table' => 'first_aid_kit_checklists',
        'title' => 'First Aid Kit Reports',
        'icon' => 'fas fa-first-aid',
        'view_page' => 'view_first_aid_kit_checklists.php',
        'badge_class' => 'badge-first-aid'
    ],
    [
        'id' => 'incident',
        'table' => 'incident_reports',
        'title' => 'Incident Reports',
        'icon' => 'fas fa-exclamation-triangle',
        'view_page' => 'view_incident_reports.php',
        'badge_class' => 'badge-incident'
    ],
    [
        'id' => 'ppe',
        'table' => 'ppe_register',
        'title' => 'PPE Reports',
        'icon' => 'fas fa-hard-hat',
        'view_page' => 'view_ppe_reports.php',
        'badge_class' => 'badge-ppe'
    ],
    [
        'id' => 'site_induction',
        'table' => 'site_induction',
        'title' => 'Site Induction Reports',
        'icon' => 'fas fa-clipboard-check',
        'view_page' => 'view_site_induction_reports.php',
        'badge_class' => 'badge-site-induction'
    ],
    [
        'id' => 'toolbox',
        'table' => 'toolbox_meetings',
        'title' => 'Toolbox Reports',
        'icon' => 'fas fa-tools',
        'view_page' => 'view_all_toolbox_reports.php',
        'badge_class' => 'badge-toolbox'
    ],
    [
        'id' => 'risk_assessment',
        'table' => 'risk_assessments',
        'title' => 'Risk Assessment Reports',
        'icon' => 'fas fa-exclamation-circle',
        'view_page' => 'view_risk_assessment_reports.php',
        'badge_class' => 'badge-risk-assessment'
    ],
    [
        'id' => 'waste_management',
        'table' => 'waste_management',
        'title' => 'Waste Management Reports',
        'icon' => 'fas fa-recycle',
        'view_page' => 'view_waste_management_reports.php',
        'badge_class' => 'badge-waste-management'
    ]
];

// Check which tables exist and count records
$reportCounts = array();
$existingReportTypes = array();

foreach ($reportTypes as $type) {
    $sql = "SHOW TABLES LIKE '{$type['table']}'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        // Table exists, count records
        $countSql = "SELECT COUNT(*) as count FROM {$type['table']}";
        $countResult = $conn->query($countSql);
        $count = 0;
        
        if ($countResult && $countResult->num_rows > 0) {
            $count = $countResult->fetch_assoc()['count'];
        }
        
        $reportCounts[$type['id']] = $count;
        $existingReportTypes[] = $type;
    }
}

// Get recent reports from all existing tables
$recentReports = array();

// Function to safely get recent reports
function getRecentReports($conn, $table, $fields) {
    $reports = [];
    try {
        $sql = "SELECT '{$fields['type']}' as type, {$fields['id_col']} as id, {$fields['name_col']} as name, 
                {$fields['date_col']} as date 
                FROM {$table} 
                ORDER BY {$fields['date_col']} DESC LIMIT 2";
                
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $idParam = $fields['id_param'] ?? 'id';
                $row['view_url'] = $fields['view_page'] . '?' . $idParam . '=' . $row['id'];
                $row['badge_class'] = $fields['badge_class'];
                $reports[] = $row;
            }
        }
    } catch (Exception $e) {
        // Silently handle errors
    }
    
    return $reports;
}

// Setup fields for each report type
$reportFields = [
    'first_aid_kit_checklists' => [
        'type' => 'First Aid Kit',
        'id_col' => 'id',
        'name_col' => 'team_leader',
        'date_col' => 'confirmation_date',
        'view_page' => 'view_first_aid_kit_checklist.php',
        'badge_class' => 'badge-first-aid'
    ],
    'incident_reports' => [
        'type' => 'Incident',
        'id_col' => 'id',
        'name_col' => 'reported_by',
        'date_col' => 'date_reported',
        'view_page' => 'view_incident_report.php',
        'badge_class' => 'badge-incident'
    ],
    'ppe_register' => [
        'type' => 'PPE Register',
        'id_col' => 'id',
        'name_col' => 'teamLeaderName',
        'date_col' => 'date',
        'view_page' => 'view_ppe_report_detail.php',
        'badge_class' => 'badge-ppe'
    ],
    'site_induction' => [
        'type' => 'Site Induction',
        'id_col' => 'inductionId',
        'name_col' => 'site_name',
        'date_col' => 'induction_date',
        'view_page' => 'view_single_site_induction.php',
        'id_param' => 'inductionId',
        'badge_class' => 'badge-site-induction'
    ],
    'toolbox_meetings' => [
        'type' => 'Toolbox Meeting',
        'id_col' => 'id',
        'name_col' => 'site_name',
        'date_col' => 'meeting_date',
        'view_page' => 'view_single_toolbox_report.php',
        'badge_class' => 'badge-toolbox'
    ],
    'risk_assessments' => [
        'type' => 'Risk Assessment',
        'id_col' => 'id',
        'name_col' => 'site_info',
        'date_col' => 'assessment_date',
        'view_page' => 'view_risk_assessment.php',
        'badge_class' => 'badge-risk-assessment'
    ],
    'waste_management' => [
        'type' => 'Waste Management',
        'id_col' => 'id',
        'name_col' => 'site_location',
        'date_col' => 'collection_date',
        'view_page' => 'view_waste_management.php',
        'badge_class' => 'badge-waste-management'
    ]
];

// Get recent reports from each existing table
foreach ($reportFields as $table => $fields) {
    // Check if table exists
    $sql = "SHOW TABLES LIKE '$table'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $reports = getRecentReports($conn, $table, $fields);
        $recentReports = array_merge($recentReports, $reports);
    }
}

// Sort recent reports by date (newest first)
usort($recentReports, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Keep only the 10 most recent reports
$recentReports = array_slice($recentReports, 0, 10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Dashboard</title>
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
            position: relative;
        }
        
        body::before {
            content: "";
            background: url('images/jarlso.png') no-repeat center center;
            opacity: 0.05;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
            background-size: 30%;
            pointer-events: none;
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
        .sidebar-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar-logo img {
            max-width: 80%;
            height: auto;
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
        .sidebar ul li a.active {
            background-color: #16325c;
            font-weight: bold;
        }
        .main-content {
            margin-left: 220px;
            padding: 40px;
            width: calc(100% - 220px);
            background-color: #f4f7fc;
        }
        .main-content h1 {
            color: #2a5298;
            font-size: 2em;
            margin-bottom: 20px;
            text-align: center;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .card h3 {
            color: #2a5298;
            margin-bottom: 10px;
        }
        .card-count {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2a5298;
            margin: 10px 0;
        }
        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #2a5298;
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
        
        /* Card accent colors based on report type */
        .card.first_aid::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background-color: #c62828;
        }
        .card.incident::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background-color: #d32f2f;
        }
        .card.ppe::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background-color: #ed6c02;
        }
        .card.site_induction::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background-color: #6a1b9a;
        }
        .card.toolbox::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background-color: #0277bd;
        }
        .card.risk_assessment::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background-color: #d81b60;
        }
        .card.waste_management::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background-color: #2e7d32;
        }
        
        .search-section {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        .search-section h2 {
            margin-bottom: 20px;
            color: #2a5298;
        }
        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }
        .search-form input,
        .search-form select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            flex-grow: 1;
        }
        .search-form button {
            padding: 10px 20px;
            background: #2a5298;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .search-form button:hover {
            background-color: #1e3c72;
        }
        .recent-reports {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        .recent-reports h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #2a5298;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            color: #2a5298;
        }
        tbody tr:hover {
            background-color: #f8f9fa;
        }
        .report-type-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #fff;
        }
        .badge-first-aid {
            background-color: #c62828;
        }
        .badge-incident {
            background-color: #d32f2f;
        }
        .badge-ppe {
            background-color: #ed6c02;
        }
        .badge-site-induction {
            background-color: #6a1b9a;
        }
        .badge-toolbox {
            background-color: #0277bd;
        }
        .badge-risk-assessment {
            background-color: #d81b60;
        }
        .badge-waste-management {
            background-color: #2e7d32;
        }
        .view-btn {
            display: inline-block;
            padding: 5px 10px;
            background: #2a5298;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: background 0.3s;
        }
        .view-btn:hover {
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
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            th, td {
                padding: 8px 10px;
            }

            .report-type-badge {
                font-size: 0.7rem;
                padding: 3px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="images/jarlso.png" alt="Jarlso Logo">
        </div>
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="view_team_leaders.php"><i class="fas fa-users"></i> Team Leaders</a></li>
            <li><a href="register_team_leader.html"><i class="fas fa-user-plus"></i> Add Leader</a></li>
            <li><a href="all_reports.php" class="active"><i class="fas fa-file-alt"></i> Reports</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Reports Dashboard</h1>

        <div class="dashboard-grid">
            <?php foreach ($existingReportTypes as $type): ?>
                <div class="card <?php echo $type['id']; ?>">
                    <i class="<?php echo $type['icon']; ?> card-icon"></i>
                    <h3><?php echo $type['title']; ?></h3>
                    <div class="card-count"><?php echo $reportCounts[$type['id']]; ?></div>
                    <a href="<?php echo $type['view_page']; ?>">View All</a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="search-section">
            <h2>Search Reports</h2>
            <form class="search-form" action="search_reports.php" method="GET">
                <input type="text" name="keyword" placeholder="Search by keyword">
                <select name="report_type">
                    <option value="">All Report Types</option>
                    <?php foreach ($existingReportTypes as $type): ?>
                        <option value="<?php echo $type['id']; ?>"><?php echo $type['title']; ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="start_date" placeholder="Start Date">
                <input type="date" name="end_date" placeholder="End Date">
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="recent-reports">
            <h2>Recent Reports</h2>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Name/Site</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recentReports) > 0): ?>
                        <?php foreach ($recentReports as $report): ?>
                            <tr>
                                <td>
                                    <span class="report-type-badge <?php echo $report['badge_class']; ?>">
                                        <?php echo $report['type']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($report['name']); ?></td>
                                <td><?php echo htmlspecialchars($report['date']); ?></td>
                                <td>
                                    <a href="<?php echo $report['view_url']; ?>" class="view-btn">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No recent reports available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="login_admin.php" class="logout-btn">Logout</a>
</body>
</html>

<?php
$conn->close();
?>