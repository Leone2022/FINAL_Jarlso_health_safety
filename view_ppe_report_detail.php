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
    header("Location: view_ppe_reports.php");
    exit();
}

$reportId = $_GET['id'];

// Get report details
$sql = "SELECT * FROM ppe_register WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $reportId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "PPE report not found.";
    exit();
}

$report = $result->fetch_assoc();

// Decode images JSON
$images = [];
if (!empty($report['images'])) {
    $images = json_decode($report['images'], true);
}

// Helper function to display Yes/No/NA values
function displayStatus($value) {
    if ($value === null) return '<span class="status-na">N/A</span>';
    return $value == 'yes' ? 
        '<span class="status-yes">Yes</span>' : 
        '<span class="status-no">No</span>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPE Report Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: linear-gradient(to right, #ed6c02, #ff9800);
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
            color: #ed6c02;
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
            color: #ed6c02;
        }

        .ppe-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .ppe-item {
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
            text-align: center;
        }

        .ppe-item strong {
            display: block;
            margin-bottom: 10px;
            color: #ed6c02;
        }

        .status-yes {
            display: inline-block;
            padding: 3px 10px;
            background-color: #4caf50;
            color: white;
            border-radius: 3px;
            font-weight: bold;
        }

        .status-no {
            display: inline-block;
            padding: 3px 10px;
            background-color: #f44336;
            color: white;
            border-radius: 3px;
            font-weight: bold;
        }

        .status-na {
            display: inline-block;
            padding: 3px 10px;
            background-color: #9e9e9e;
            color: white;
            border-radius: 3px;
            font-weight: bold;
        }

        .details-box {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .details-box h4 {
            color: #ed6c02;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .details-box p {
            line-height: 1.6;
        }

        .signature-box {
            display: block;
            font-style: italic;
            color: #555;
            margin-top: 5px;
        }

        .images-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
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
            background: #ed6c02;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #e65100;
        }

        /* Media query for smaller screens */
        @media (max-width: 768px) {
            .info-grid, .ppe-grid {
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
        <h1>PPE Register Report</h1>
        
        <div class="section">
            <h2>Project Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Report ID</strong>
                    <?php echo htmlspecialchars($report['id']); ?>
                </div>
                <div class="info-item">
                    <strong>Site ID</strong>
                    <?php echo htmlspecialchars($report['siteID']); ?>
                </div>
                <div class="info-item">
                    <strong>Site Name</strong>
                    <?php echo htmlspecialchars($report['siteName']); ?>
                </div>
                <div class="info-item">
                    <strong>Project Title</strong>
                    <?php echo htmlspecialchars($report['projectTitle']); ?>
                </div>
                <div class="info-item">
                    <strong>Date</strong>
                    <?php echo htmlspecialchars($report['date']); ?>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2>PPE Equipment Status</h2>
            <div class="ppe-grid">
                <div class="ppe-item">
                    <strong>Safety Boot</strong>
                    <?php echo displayStatus($report['safetyBoot']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Helmet</strong>
                    <?php echo displayStatus($report['helmet']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Reflective Jacket</strong>
                    <?php echo displayStatus($report['reflectiveJacket']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Full Body Harness</strong>
                    <?php echo displayStatus($report['fullBodyHarness']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Rescue Kits</strong>
                    <?php echo displayStatus($report['rescueKits']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Hand Gloves</strong>
                    <?php echo displayStatus($report['handGloves']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Nose Mask</strong>
                    <?php echo displayStatus($report['noseMask']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Ear Plugs</strong>
                    <?php echo displayStatus($report['earPlugs']); ?>
                </div>
                <div class="ppe-item">
                    <strong>First Aider Certified</strong>
                    <?php echo displayStatus($report['firstAiderCertified']); ?>
                </div>
                <div class="ppe-item">
                    <strong>First Aid Kits</strong>
                    <?php echo displayStatus($report['firstAidKits']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Goggles</strong>
                    <?php echo displayStatus($report['goggles']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Fire Extinguisher</strong>
                    <?php echo displayStatus($report['fireExtinguisher']); ?>
                </div>
            </div>
            
            <?php if (!empty($report['otherPPEs'])): ?>
            <div class="details-box">
                <h4>Other PPE Equipment</h4>
                <p><?php echo nl2br(htmlspecialchars($report['otherPPEs'])); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($report['additionalDetails'])): ?>
            <div class="details-box">
                <h4>Additional Details</h4>
                <p><?php echo nl2br(htmlspecialchars($report['additionalDetails'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>Approval Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Team Leader Name</strong>
                    <?php echo htmlspecialchars($report['teamLeaderName']); ?>
                    <span class="signature-box"><?php echo htmlspecialchars($report['teamLeaderDate']); ?></span>
                </div>
                <div class="info-item">
                    <strong>HSE Representative</strong>
                    <?php echo htmlspecialchars($report['hseName']); ?>
                </div>
                <div class="info-item">
                    <strong>Declaration Signature</strong>
                    <span class="signature-box"><?php echo htmlspecialchars($report['declarationSignature']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Images -->
        <?php if (!empty($images)): ?>
        <div class="section">
            <h3>Supporting Photos</h3>
            <div class="images-container">
                <?php foreach ($images as $image): ?>
                    <div class="image-card">
                        <img src="<?php echo htmlspecialchars($image['path']); ?>" alt="PPE Photo">
                        <div class="image-caption">
                            <?php echo htmlspecialchars($image['name']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="actions">
            <a href="view_ppe_reports.php" class="btn">Back to Reports</a>
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