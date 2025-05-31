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
    header("Location: view_waste_management_reports.php");
    exit();
}

$reportId = $_GET['id'];

// Get report details
$sql = "SELECT * FROM waste_management WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $reportId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Report not found.";
    exit();
}

$report = $result->fetch_assoc();

// Get waste items
$sql = "SELECT * FROM waste_management_items WHERE report_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $reportId);
$stmt->execute();
$wasteItems = $stmt->get_result();

// Get images
$sql = "SELECT * FROM waste_management_images WHERE report_id = ? ORDER BY category, id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $reportId);
$stmt->execute();
$images = $stmt->get_result();

// Organize images by category
$imagesByCategory = [
    'before' => [],
    'after' => [],
    'collection' => [],
    'disposal' => []
];

while ($image = $images->fetch_assoc()) {
    $imagesByCategory[$image['category']][] = $image;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waste Management Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: linear-gradient(to right, #2e7d32, #4caf50);
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
            color: #2e7d32;
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
            color: #2e7d32;
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
            background-color: #2e7d32;
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
            background: #2e7d32;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #1b5e20;
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
        <h1>Waste Management Report</h1>
        
        <div class="section">
            <h2>Report Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Report ID</strong>
                    <?php echo htmlspecialchars($report['id']); ?>
                </div>
                <div class="info-item">
                    <strong>Site Location</strong>
                    <?php echo htmlspecialchars($report['site_location']); ?>
                </div>
                <div class="info-item">
                    <strong>Collection Date</strong>
                    <?php echo htmlspecialchars($report['collection_date']); ?>
                </div>
                <div class="info-item">
                    <strong>Team Leader</strong>
                    <?php echo htmlspecialchars($report['team_lead']); ?>
                </div>
                <div class="info-item">
                    <strong>Team Size</strong>
                    <?php echo htmlspecialchars($report['team_size']); ?>
                </div>
                <div class="info-item">
                    <strong>Confirmed By</strong>
                    <?php echo htmlspecialchars($report['confirmed_by']); ?>
                </div>
                <div class="info-item">
                    <strong>Confirmation Date</strong>
                    <?php echo htmlspecialchars($report['confirmed_date']); ?>
                </div>
                <div class="info-item">
                    <strong>Submission Time</strong>
                    <?php echo htmlspecialchars($report['submission_time']); ?>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2>Waste Items</h2>
            <?php if ($wasteItems->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Waste Type</th>
                            <th>Quantity (kg)</th>
                            <th>Disposal Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $wasteItems->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['waste_type']); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($item['disposal_method']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No waste items recorded.</p>
            <?php endif; ?>
        </div>
        
        <!-- Images - Before Collection -->
        <div class="section">
            <h3>Photos Before Waste Collection</h3>
            <div class="images-container">
                <?php if (!empty($imagesByCategory['before'])): ?>
                    <?php foreach ($imagesByCategory['before'] as $image): ?>
                        <div class="image-card">
                            <img src="<?php echo htmlspecialchars($image['file_path']); ?>" alt="Before collection">
                            <div class="image-caption">Before Collection</div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-images">No before collection photos available</div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Images - After Collection -->
        <div class="section">
            <h3>Photos After Waste Collection</h3>
            <div class="images-container">
                <?php if (!empty($imagesByCategory['after'])): ?>
                    <?php foreach ($imagesByCategory['after'] as $image): ?>
                        <div class="image-card">
                            <img src="<?php echo htmlspecialchars($image['file_path']); ?>" alt="After collection">
                            <div class="image-caption">After Collection</div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-images">No after collection photos available</div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Images - Collection Area -->
        <div class="section">
            <h3>Collection Area Photos</h3>
            <div class="images-container">
                <?php if (!empty($imagesByCategory['collection'])): ?>
                    <?php foreach ($imagesByCategory['collection'] as $image): ?>
                        <div class="image-card">
                            <img src="<?php echo htmlspecialchars($image['file_path']); ?>" alt="Collection area">
                            <div class="image-caption">Collection Area</div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-images">No collection area photos available</div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Images - Disposal Method -->
        <div class="section">
            <h3>Disposal Method Photos</h3>
            <div class="images-container">
                <?php if (!empty($imagesByCategory['disposal'])): ?>
                    <?php foreach ($imagesByCategory['disposal'] as $image): ?>
                        <div class="image-card">
                            <img src="<?php echo htmlspecialchars($image['file_path']); ?>" alt="Disposal method">
                            <div class="image-caption">Disposal Method</div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-images">No disposal method photos available</div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="actions">
            <a href="view_waste_management_reports.php" class="btn">Back to Reports</a>
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