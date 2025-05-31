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

// Initialize variables
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Define report type configurations (matches your all_reports.php)
$reportTypes = [
    'first_aid' => [
        'title' => 'First Aid Kit Reports',
        'table' => 'first_aid_kit_checklists',
        'badge_class' => 'badge-first-aid',
        'date_field' => 'confirm_date',
        'name_field' => 'confirm_name',
        'id_field' => 'id',
        'reference_field' => 'checklist_no',
        'search_fields' => ['confirm_name', 'team_leader', 'checklist_no'],
        'view_url' => 'view_single_report.php?id='
    ],
    'incident' => [
        'title' => 'Incident Reports',
        'table' => 'incident_reports',
        'badge_class' => 'badge-incident',
        'date_field' => 'date_reported',
        'name_field' => 'reported_by',
        'id_field' => 'id',
        'reference_field' => 'incident_no',
        'search_fields' => ['reported_by', 'client_name', 'project_name', 'incident_no'],
        'view_url' => 'view_incident_report.php?id='
    ],
    'ppe' => [
        'title' => 'PPE Register Reports',
        'table' => 'ppe_register',
        'badge_class' => 'badge-ppe',
        'date_field' => 'date',
        'name_field' => 'teamLeaderName',
        'id_field' => 'id',
        'reference_field' => "CONCAT(siteID, ' - ', siteName)",
        'search_fields' => ['teamLeaderName', 'siteID', 'siteName'],
        'view_url' => 'view_ppe_report_detail.php?id='
    ],
    'site_induction' => [
        'title' => 'Site Induction Reports',
        'table' => 'site_induction',
        'badge_class' => 'badge-site-induction',
        'date_field' => 'inductionDate',
        'name_field' => 'siteName',
        'id_field' => 'inductionId',
        'reference_field' => 'siteNumber',
        'search_fields' => ['siteName', 'siteNumber', 'visitorSignature'],
        'view_url' => 'view_single_site_induction.php?inductionId='
    ],
    'toolbox' => [
        'title' => 'Toolbox Meeting Reports',
        'table' => 'toolbox_meetings',
        'badge_class' => 'badge-toolbox',
        'date_field' => 'meeting_date',
        'name_field' => 'site_name',
        'id_field' => 'id',
        'reference_field' => 'activity_details',
        'search_fields' => ['site_name', 'activity_details'],
        'view_url' => 'view_single_toolbox_report.php?id='
    ],
    'risk_assessment' => [
        'title' => 'Risk Assessment Reports',
        'table' => 'risk_assessments',
        'badge_class' => 'badge-risk-assessment',
        'date_field' => 'assessment_date',
        'name_field' => 'site_info',
        'id_field' => 'id',
        'reference_field' => 'assessment_no',
        'search_fields' => ['site_info', 'assessment_no'],
        'view_url' => 'view_risk_assessment.php?id='
    ],
    'waste_management' => [
        'title' => 'Waste Management Reports',
        'table' => 'waste_management',
        'badge_class' => 'badge-waste-management',
        'date_field' => 'collection_date',
        'name_field' => 'site_location',
        'id_field' => 'id',
        'reference_field' => "CONCAT('WM-', id)",
        'search_fields' => ['site_location', 'confirmed_by', 'team_lead'],
        'view_url' => 'view_waste_management.php?id='
    ]
];

// Get all existing report types
$existingReportTypes = [];
foreach ($reportTypes as $typeId => $type) {
    $sql = "SHOW TABLES LIKE '{$type['table']}'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $existingReportTypes[$typeId] = $type;
    }
}

// Prepare search results array
$searchResults = array();

// Build date condition for SQL queries
$dateCondition = '';
if (!empty($start_date) && !empty($end_date)) {
    $dateCondition = " AND {date_field} BETWEEN '$start_date' AND '$end_date'";
} elseif (!empty($start_date)) {
    $dateCondition = " AND {date_field} >= '$start_date'";
} elseif (!empty($end_date)) {
    $dateCondition = " AND {date_field} <= '$end_date'";
}

// Search specific report type if specified, otherwise search all types
$typesToSearch = [];
if (!empty($report_type) && isset($existingReportTypes[$report_type])) {
    $typesToSearch[$report_type] = $existingReportTypes[$report_type];
} else {
    $typesToSearch = $existingReportTypes;
}

// Perform search for each report type
foreach ($typesToSearch as $typeId => $typeConfig) {
    // Skip if table doesn't exist
    $sql = "SHOW TABLES LIKE '{$typeConfig['table']}'";
    $result = $conn->query($sql);
    if (!$result || $result->num_rows === 0) {
        continue;
    }

    try {
        // Build search condition
        $searchConditions = [];
        foreach ($typeConfig['search_fields'] as $field) {
            $searchConditions[] = "$field LIKE '%$keyword%'";
        }
        $searchCondition = count($searchConditions) > 0 
            ? "(" . implode(" OR ", $searchConditions) . ")" 
            : "1";

        // Replace date placeholder
        $tableDateCondition = str_replace('{date_field}', $typeConfig['date_field'], $dateCondition);

        // Build the query
        $sql = "SELECT 
                '{$typeConfig['title']}' as type, 
                {$typeConfig['id_field']} as id, 
                {$typeConfig['name_field']} as name, 
                {$typeConfig['date_field']} as date,
                {$typeConfig['reference_field']} as reference,
                '{$typeConfig['badge_class']}' as badge_class,
                '{$typeConfig['view_url']}' as view_url_base
            FROM {$typeConfig['table']} 
            WHERE $searchCondition$tableDateCondition
            ORDER BY {$typeConfig['date_field']} DESC";

        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Build the full view URL
                $row['view_url'] = $row['view_url_base'] . $row['id'];
                unset($row['view_url_base']); // Remove the base URL from the result
                
                // Add to results
                $searchResults[] = $row;
            }
        }
    } catch (Exception $e) {
        // Silently skip tables with errors
        error_log("Error searching in {$typeConfig['table']}: " . $e->getMessage());
    }
}

// Get the report type name for display
$reportTypeName = 'All Reports';
if (!empty($report_type) && isset($reportTypes[$report_type])) {
    $reportTypeName = $reportTypes[$report_type]['title'];
}

// Add the search summary
$searchSummary = "Showing results";
if (!empty($keyword)) {
    $searchSummary .= " for keyword: <strong>\"" . htmlspecialchars($keyword) . "\"</strong>";
}
if (!empty($report_type)) {
    $searchSummary .= " in <strong>" . $reportTypeName . "</strong>";
}
if (!empty($start_date) && !empty($end_date)) {
    $searchSummary .= " between <strong>" . date('M d, Y', strtotime($start_date)) . "</strong> and <strong>" . date('M d, Y', strtotime($end_date)) . "</strong>";
} elseif (!empty($start_date)) {
    $searchSummary .= " from <strong>" . date('M d, Y', strtotime($start_date)) . "</strong>";
} elseif (!empty($end_date)) {
    $searchSummary .= " until <strong>" . date('M d, Y', strtotime($end_date)) . "</strong>";
}

// Add result count
$resultCount = count($searchResults);
$searchSummary .= " | <strong>" . $resultCount . "</strong> report" . ($resultCount != 1 ? "s" : "") . " found";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - <?php echo $reportTypeName; ?></title>
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
        
        h1, h2 {
            color: #2a5298;
            margin-bottom: 20px;
            text-align: center;
        }

        .search-form {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
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

        .search-results {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }

        .search-summary {
            margin-bottom: 20px;
            text-align: center;
            color: #666;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
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
            background-color: #28a745;
        }

        .badge-incident {
            background-color: #dc3545;
        }

        .badge-ppe {
            background-color: #fd7e14;
        }

        .badge-site-induction {
            background-color: #6f42c1;
        }

        .badge-toolbox {
            background-color: #17a2b8;
        }
        
        .badge-risk-assessment {
            background-color: #e83e8c;
        }
        
        .badge-waste-management {
            background-color: #20c997;
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

        .export-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .export-btn {
            padding: 8px 15px;
            background: #2a5298;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background 0.3s;
        }

        .export-btn:hover {
            background-color: #1e3c72;
        }

        .no-results {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 1.1rem;
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

        @media screen and (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
            th, td {
                padding: 8px;
            }
            .report-type-badge {
                font-size: 0.7rem;
                padding: 3px 8px;
            }
            .view-btn {
                padding: 4px 8px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="view_team_leaders.php"><i class="fas fa-users"></i> Team Leaders</a></li>
            <li><a href="register_team_leader.html"><i class="fas fa-user-plus"></i> Add Leader</a></li>
            <li><a href="all_reports.php" class="active"><i class="fas fa-file-alt"></i> Reports</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Search Results</h1>

        <!-- Search Form -->
        <form class="search-form" action="search_reports.php" method="GET">
            <input type="text" name="keyword" placeholder="Search by keyword" value="<?php echo htmlspecialchars($keyword); ?>">
            <select name="report_type">
                <option value="" <?php echo empty($report_type) ? 'selected' : ''; ?>>All Report Types</option>
                <?php foreach ($existingReportTypes as $typeId => $type): ?>
                    <option value="<?php echo $typeId; ?>" <?php echo $typeId == $report_type ? 'selected' : ''; ?>>
                        <?php echo $type['title']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="start_date" value="<?php echo $start_date; ?>">
            <input type="date" name="end_date" value="<?php echo $end_date; ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Search Results -->
        <div class="search-results">
            <h2><?php echo $reportTypeName; ?></h2>
            
            <div class="search-summary">
                <?php echo $searchSummary; ?>
            </div>

            <?php if (!empty($searchResults)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Name/Site</th>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($searchResults as $result): ?>
                            <tr>
                                <td>
                                    <span class="report-type-badge <?php echo $result['badge_class']; ?>">
                                        <?php echo $result['type']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($result['name']); ?></td>
                                <td><?php echo htmlspecialchars($result['reference']); ?></td>
                                <td><?php echo htmlspecialchars($result['date']); ?></td>
                                <td>
                                    <a href="<?php echo $result['view_url']; ?>" class="view-btn">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="export-buttons">
                    <button class="export-btn" onclick="printResults()">
                        <i class="fas fa-print"></i> Print Results
                    </button>
                    <button class="export-btn" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf"></i> Export to PDF
                    </button>
                    <button class="export-btn" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </button>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search" style="font-size: 3rem; color: #ddd; margin-bottom: 15px;"></i>
                    <p>No reports found matching your search criteria.</p>
                    <p>Try adjusting your search terms or filtering options.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <a href="login_admin.php" class="logout-btn">Logout</a>

    <!-- JavaScript for export functionality -->
    <script>
        function printResults() {
            window.print();
        }

        function exportToPDF() {
            // This is a basic implementation. For production, you might want to use a more robust solution
            alert('PDF Export functionality will be implemented.\nFor now, you can use the Print function and save as PDF.');
        }

        function exportToExcel() {
            // Basic implementation - would need server-side processing for full Excel export
            alert('Excel Export functionality will be implemented.\nThis typically requires server-side processing.');
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>