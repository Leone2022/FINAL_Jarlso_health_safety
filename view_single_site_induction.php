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
if (!isset($_GET['inductionId'])) {
    header("Location: view_site_induction_reports.php");
    exit();
}

$inductionId = $_GET['inductionId'];

// Get induction details - check for different possible column names
$sql = "SHOW COLUMNS FROM site_induction LIKE 'inductionId'";
$result = $conn->query($sql);
$idColumnName = ($result && $result->num_rows > 0) ? 'inductionId' : 'id';

$sql = "SELECT * FROM site_induction WHERE $idColumnName = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $inductionId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Induction report not found.";
    exit();
}

$induction = $result->fetch_assoc();

// Get visitor logs
$sql = "SELECT * FROM visitor_log WHERE induction_id = ? ORDER BY visit_date, visit_time";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $inductionId);
$stmt->execute();
$visitorLogs = $stmt->get_result();

// Get supporting documents
$sql = "SELECT * FROM supporting_documents WHERE induction_id = ? ORDER BY id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $inductionId);
$stmt->execute();
$documents = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Induction Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: linear-gradient(to right, #6a1b9a, #9c27b0);
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
            color: #6a1b9a;
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
            color: #6a1b9a;
        }

        .details-box {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .details-box h4 {
            color: #6a1b9a;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .details-box p {
            line-height: 1.6;
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
            background-color: #6a1b9a;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
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
            background: #6a1b9a;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #4a148c;
        }

        .signature-box {
            display: inline-block;
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-style: italic;
            color: #555;
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
        <h1>Site Induction Report</h1>
        
        <div class="section">
            <h2>Induction Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Induction ID</strong>
                    <?php echo htmlspecialchars($inductionId); ?>
                </div>
                <div class="info-item">
                    <strong>Site Name</strong>
                    <?php echo htmlspecialchars($induction['site_name']); ?>
                </div>
                <div class="info-item">
                    <strong>Site Number</strong>
                    <?php echo htmlspecialchars($induction['site_number']); ?>
                </div>
                <div class="info-item">
                    <strong>Induction Date</strong>
                    <?php echo htmlspecialchars($induction['induction_date']); ?>
                </div>
            </div>
            
            <div class="details-box">
                <h4>Induction Declaration</h4>
                <p><?php echo nl2br(htmlspecialchars($induction['induction_declaration'])); ?></p>
            </div>
        </div>
        
        <div class="section">
            <h2>Visitor Log</h2>
            <?php if ($visitorLogs->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Visitor Name</th>
                            <th>Company</th>
                            <th>Signature</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($visitor = $visitorLogs->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($visitor['visit_date']); ?></td>
                                <td><?php echo htmlspecialchars($visitor['visit_time']); ?></td>
                                <td><?php echo htmlspecialchars($visitor['visitor_name']); ?></td>
                                <td><?php echo htmlspecialchars($visitor['company_name']); ?></td>
                                <td>
                                    <div class="signature-box">
                                        <?php echo htmlspecialchars($visitor['signature']); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No visitor log entries found.</p>
            <?php endif; ?>
        </div>
        
        <!-- Supporting Documents -->
        <div class="section">
            <h3>Supporting Documents</h3>
            <div class="images-container">
                <?php if ($documents->num_rows > 0): ?>
                    <?php while ($doc = $documents->fetch_assoc()): ?>
                        <div class="image-card">
                            <img src="<?php echo htmlspecialchars($doc['file_path']); ?>" alt="Supporting Document">
                            <div class="image-caption">
                                <?php echo htmlspecialchars($doc['file_name']); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-images">No supporting documents available</div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="actions">
            <a href="view_site_induction_reports.php" class="btn">Back to Reports</a>
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