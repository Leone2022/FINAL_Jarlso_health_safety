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
    header("Location: view_incident_reports.php");
    exit();
}

$reportId = $_GET['id'];

// Get report details
$sql = "SELECT * FROM incident_reports WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $reportId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Report not found.";
    exit();
}

$report = $result->fetch_assoc();

// Get images
$sql = "SELECT * FROM incident_images WHERE report_id = ? ORDER BY id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $reportId);
$stmt->execute();
$images = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: linear-gradient(to right, #d32f2f, #f44336);
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
            color: #d32f2f;
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
            color: #d32f2f;
        }

        .details-box {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .details-box h4 {
            color: #d32f2f;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .details-box p {
            line-height: 1.6;
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
            background: #d32f2f;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #b71c1c;
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
        <h1>Incident Report</h1>
        
        <div class="section">
            <h2>Report Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Report ID</strong>
                    <?php echo htmlspecialchars($report['id']); ?>
                </div>
                <div class="info-item">
                    <strong>Incident Number</strong>
                    <?php echo htmlspecialchars($report['incident_no']); ?>
                </div>
                <div class="info-item">
                    <strong>Reported By</strong>
                    <?php echo htmlspecialchars($report['reported_by']); ?>
                </div>
                <div class="info-item">
                    <strong>Date Reported</strong>
                    <?php echo htmlspecialchars($report['date_reported']); ?>
                </div>
                <div class="info-item">
                    <strong>Reported By Client</strong>
                    <?php echo htmlspecialchars($report['reported_by_client']); ?>
                </div>
                <?php if (!empty($report['client_name'])): ?>
                <div class="info-item">
                    <strong>Client Name</strong>
                    <?php echo htmlspecialchars($report['client_name']); ?>
                </div>
                <?php endif; ?>
                <div class="info-item">
                    <strong>Affected Project</strong>
                    <?php echo htmlspecialchars($report['affected_project']); ?>
                </div>
                <?php if (!empty($report['project_name'])): ?>
                <div class="info-item">
                    <strong>Project Name</strong>
                    <?php echo htmlspecialchars($report['project_name']); ?>
                </div>
                <?php endif; ?>
                <div class="info-item">
                    <strong>HSE Aspects</strong>
                    <?php echo htmlspecialchars($report['hse_aspects']); ?>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2>Incident Details</h2>
            <div class="details-box">
                <h4>Incident Description</h4>
                <p><?php echo nl2br(htmlspecialchars($report['incident_description'])); ?></p>
            </div>
            
            <div class="details-box">
                <h4>Root Cause Analysis</h4>
                <p><?php echo nl2br(htmlspecialchars($report['root_cause_analysis'])); ?></p>
            </div>
        </div>
        
        <div class="section">
            <h2>Corrective Actions</h2>
            <div class="details-box">
                <h4>Actions Taken</h4>
                <p><?php echo nl2br(htmlspecialchars($report['corrective_actions'])); ?></p>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <strong>Carried Out By</strong>
                    <?php echo htmlspecialchars($report['carried_out_by']); ?>
                </div>
                <div class="info-item">
                    <strong>Date Carried Out</strong>
                    <?php echo htmlspecialchars($report['date_carried_out']); ?>
                </div>
            </div>
            
            <div class="details-box">
                <h4>Verification Comments</h4>
                <p><?php echo nl2br(htmlspecialchars($report['verification_comments'])); ?></p>
            </div>
        </div>
        
        <div class="section">
            <h2>Preventive Actions</h2>
            <div class="details-box">
                <h4>Actions Taken</h4>
                <p><?php echo nl2br(htmlspecialchars($report['preventive_actions'])); ?></p>
            </div>
            
            <div class="details-box">
                <h4>Verification Comments</h4>
                <p><?php echo nl2br(htmlspecialchars($report['verification_comments_preventive'])); ?></p>
            </div>
        </div>
        
        <div class="section">
            <h2>Report Approval</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Report Name</strong>
                    <?php echo htmlspecialchars($report['report_name']); ?>
                </div>
                <div class="info-item">
                    <strong>Report Date</strong>
                    <?php echo htmlspecialchars($report['report_date']); ?>
                </div>
            </div>
        </div>
        
        <!-- Images -->
        <div class="section">
            <h3>Incident Photos</h3>
            <div class="images-container">
                <?php if ($images->num_rows > 0): ?>
                    <?php while ($image = $images->fetch_assoc()): ?>
                        <div class="image-card">
                            <img src="<?php echo htmlspecialchars($image['file_path']); ?>" alt="Incident Photo">
                            <div class="image-caption">
                                <?php echo htmlspecialchars(basename($image['original_filename'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-images">No images available</div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="actions">
            <a href="view_incident_reports.php" class="btn">Back to Reports</a>
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