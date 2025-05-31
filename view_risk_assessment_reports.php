<?php
session_start();
if (!isset($_SESSION['username'])) {
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

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: view_risk_assessment_reports.php");
    exit();
}

$assessmentId = $_GET['id'];

// Get risk assessment details
$sql = "SELECT * FROM risk_assessments WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assessmentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Risk assessment not found.";
    exit();
}

$assessment = $result->fetch_assoc();

// Get scope of works
$sql = "SELECT * FROM risk_assessment_scope WHERE assessment_id = ? ORDER BY scope_order";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assessmentId);
$stmt->execute();
$scopeItems = $stmt->get_result();

// Get incidents
$sql = "SELECT * FROM risk_assessment_incidents WHERE assessment_id = ? ORDER BY id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assessmentId);
$stmt->execute();
$incidents = $stmt->get_result();

// Get hazards
$sql = "SELECT * FROM risk_assessment_hazards WHERE assessment_id = ? ORDER BY id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assessmentId);
$stmt->execute();
$hazards = $stmt->get_result();

// Get team members
$sql = "SELECT * FROM risk_assessment_team WHERE assessment_id = ? ORDER BY id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assessmentId);
$stmt->execute();
$teamMembers = $stmt->get_result();

// Get images
$sql = "SELECT * FROM risk_assessment_images WHERE assessment_id = ? ORDER BY id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assessmentId);
$stmt->execute();
$images = $stmt->get_result();

// Function to display risk level with appropriate color coding
function getRiskLevelBadge($riskLevel) {
    $riskLevel = strtolower(trim($riskLevel));
    
    if ($riskLevel == 'high') {
        return '<span class="risk-high">High</span>';
    } elseif ($riskLevel == 'medium') {
        return '<span class="risk-medium">Medium</span>';
    } elseif ($riskLevel == 'low') {
        return '<span class="risk-low">Low</span>';
    } else {
        return '<span class="risk-unknown">' . htmlspecialchars(ucfirst($riskLevel)) . '</span>';
    }
}

// Function to display hazard status with appropriate color coding
function getStatusBadge($status) {
    $status = strtolower(trim($status));
    
    if ($status == 'closed') {
        return '<span class="status-closed">Closed</span>';
    } elseif ($status == 'in progress') {
        return '<span class="status-progress">In Progress</span>';
    } else { // Default to 'open'
        return '<span class="status-open">Open</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Risk Assessment Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: linear-gradient(to right, #d81b60, #e91e63);
            color: #333;
            padding: 20px;
            position: relative;
        }
        
        body::before {
            content: "";
            background: url('images/jarlso.png') no-repeat center center;
            opacity: 0.1;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
            background-size: 30%;
            pointer-events: none;
        }

        .container {
            background: #ffffff;
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.1);
        }

        h1, h2, h3 {
            color: #d81b60;
            margin-bottom: 1rem;
            text-align: center;
        }

        .section {
            margin-bottom: 2rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .info-item {
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
        }

        .info-item strong {
            display: block;
            margin-bottom: 5px;
            color: #d81b60;
        }

        .details-box {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .details-box h4 {
            color: #d81b60;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .details-box p {
            line-height: 1.6;
        }

        .scope-list {
            list-style-position: inside;
            margin-bottom: 1rem;
        }

        .scope-list li {
            background: #f9f9f9;
            padding: 10px 15px;
            margin-bottom: 8px;
            border-radius: 5px;
            border-left: 4px solid #d81b60;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #d81b60;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .signature-box {
            display: inline-block;
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-style: italic;
            color: #555;
        }

        .images-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .image-card {
            border: 1px solid #eee;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .image-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            display: block;
        }

        .image-card .image-caption {
            padding: 8px;
            text-align: center;
            background: #f9f9f9;
            font-size: 0.9rem;
        }

        .no-images {
            grid-column: 1 / -1;
            text-align: center;
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 5px;
        }

        .actions {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #d81b60;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #c2185b;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 0.85rem;
        }

        .status-active {
            background-color: #4caf50;
            color: white;
        }

        .status-inactive {
            background-color: #9e9e9e;
            color: white;
        }

        .status-open {
            background-color: #f44336;
            color: white;
        }

        .status-progress {
            background-color: #ff9800;
            color: white;
        }

        .status-closed {
            background-color: #4caf50;
            color: white;
        }

        .risk-high {
            background-color: #f44336;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 0.85rem;
        }

        .risk-medium {
            background-color: #ff9800;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 0.85rem;
        }

        .risk-low {
            background-color: #4caf50;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 0.85rem;
        }

        .risk-unknown {
            background-color: #9e9e9e;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 0.85rem;
        }

        /* Media query for smaller screens */
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .images-container {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Print styles */
        @media print {
            body {
                background: white;
                color: black;
            }
            
            .container {
                max-width: 100%;
                box-shadow: none;
            }
            
            .actions, .btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container" id="reportContent">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="images/jarlso.png" alt="Jarlso Logo" style="max-width: 200px; height: auto;">
        </div>
        <h1>Risk Assessment Report</h1>
        
        <div class="section">
            <h2>Assessment Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Assessment ID</strong>
                    <?php echo htmlspecialchars($assessment['id']); ?>
                </div>
                <div class="info-item">
                    <strong>Assessment Number</strong>
                    <?php echo htmlspecialchars($assessment['assessment_no']); ?>
                </div>
                <div class="info-item">
                    <strong>Site Information</strong>
                    <?php echo htmlspecialchars($assessment['site_info']); ?>
                </div>
                <div class="info-item">
                    <strong>Assessment Date</strong>
                    <?php echo htmlspecialchars($assessment['assessment_date']); ?>
                </div>
                <div class="info-item">
                    <strong>Status</strong>
                    <span class="status-badge status-<?php echo strtolower($assessment['status']); ?>">
                        <?php echo htmlspecialchars($assessment['status']); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2>Scope of Works</h2>
            <ol class="scope-list">
                <?php if ($scopeItems->num_rows > 0): ?>
                    <?php while ($scope = $scopeItems->fetch_assoc()): ?>
                        <li><?php echo htmlspecialchars($scope['scope_item']); ?></li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No scope items found.</li>
                <?php endif; ?>
            </ol>
        </div>
        
        <?php if ($incidents->num_rows > 0): ?>
        <div class="section">
            <h2>Previous Incidents & Lessons Learned</h2>
            <table>
                <thead>
                    <tr>
                        <th>Incident</th>
                        <th>Lesson Learned</th>
                        <th>Control Measure</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($incident = $incidents->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($incident['incident']); ?></td>
                            <td><?php echo htmlspecialchars($incident['lesson_learnt']); ?></td>
                            <td><?php echo htmlspecialchars($incident['control_measure']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <h2>Hazard Identification & Risk Assessment</h2>
            <?php if ($hazards->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Hazard</th>
                            <th>Affected Parties</th>
                            <th>Risk Level</th>
                            <th>Existing Controls</th>
                            <th>Additional Controls</th>
                            <th>Action By</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($hazard = $hazards->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($hazard['hazard']); ?></td>
                                <td><?php echo htmlspecialchars($hazard['affected_parties']); ?></td>
                                <td><?php echo getRiskLevelBadge($hazard['risk_level']); ?></td>
                                <td><?php echo htmlspecialchars($hazard['existing_controls']); ?></td>
                                <td><?php echo htmlspecialchars($hazard['additional_controls']); ?></td>
                                <td><?php echo htmlspecialchars($hazard['action_by']); ?></td>
                                <td><?php echo htmlspecialchars($hazard['action_date']); ?></td>
                                <td><?php echo getStatusBadge($hazard['status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hazards identified for this assessment.</p>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>Assessment Team</h2>
            <?php if ($teamMembers->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Title</th>
                            <th>Signature</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($member = $teamMembers->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['name']); ?></td>
                                <td><?php echo htmlspecialchars($member['title']); ?></td>
                                <td>
                                    <div class="signature-box">
                                        <?php echo htmlspecialchars($member['signature']); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No team members recorded for this assessment.</p>
            <?php endif; ?>
        </div>
        
        <!-- Images -->
        <?php if ($images->num_rows > 0): ?>
        <div class="section">
            <h3>Risk Assessment Photos</h3>
            <div class="images-container">
                <?php while ($image = $images->fetch_assoc()): ?>
                    <div class="image-card">
                        <img src="<?php echo htmlspecialchars($image['file_path']); ?>" alt="Risk Assessment Photo">
                        <div class="image-caption">
                            <?php echo htmlspecialchars(basename($image['original_filename'])); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="actions">
            <a href="view_risk_assessment_reports.php" class="btn">Back to Reports</a>
            <button class="btn" onclick="printReport()">Print Report</button>
            <button class="btn" onclick="exportPDF()">Export to PDF</button>
        </div>
    </div>

    <script>
        function printReport() {
            window.print();
        }
        
        function exportPDF() {
            // In a real application, you'd integrate with a PDF library
            // For now, we'll use print as a workaround
            alert('To save as PDF, use the Print function and select "Save as PDF" as the destination.');
            window.print();
        }
    </script>
</body>
</html>

<?php
// Close all statements and connection
$stmt->close();
$conn->close();
?>