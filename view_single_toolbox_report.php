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
    header("Location: view_all_toolbox_reports.php");
    exit();
}

$meetingId = $_GET['id'];

// Get toolbox meeting details
$sql = "SELECT * FROM toolbox_meetings WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $meetingId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Toolbox meeting report not found.";
    exit();
}

$meeting = $result->fetch_assoc();

// Get attendees
$sql = "SELECT * FROM toolbox_attendees WHERE meeting_id = ? ORDER BY id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $meetingId);
$stmt->execute();
$attendees = $stmt->get_result();

// Get images
$sql = "SELECT * FROM toolbox_images WHERE meeting_id = ? ORDER BY id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $meetingId);
$stmt->execute();
$images = $stmt->get_result();

// Function to display Yes/No/NA values
function displayStatus($value) {
    if ($value === null || $value === '') return '<span class="status-na">N/A</span>';
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
    <title>Toolbox Meeting Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: linear-gradient(to right, #0277bd, #0097a7);
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
            color: #0277bd;
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
            color: #0277bd;
        }

        .details-box {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .details-box h4 {
            color: #0277bd;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .details-box p {
            line-height: 1.6;
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
            color: #0277bd;
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
            background-color: #0277bd;
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
            background: #0277bd;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #01579b;
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
        <h1>Toolbox Meeting Report</h1>
        
        <div class="section">
            <h2>Meeting Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Meeting ID</strong>
                    <?php echo htmlspecialchars($meeting['id']); ?>
                </div>
                <div class="info-item">
                    <strong>Site ID</strong>
                    <?php echo htmlspecialchars($meeting['site_id']); ?>
                </div>
                <div class="info-item">
                    <strong>Site Name</strong>
                    <?php echo htmlspecialchars($meeting['site_name']); ?>
                </div>
                <div class="info-item">
                    <strong>Meeting Date</strong>
                    <?php echo htmlspecialchars($meeting['meeting_date']); ?>
                </div>
            </div>
            
            <div class="details-box">
                <h4>Activity Details</h4>
                <p><?php echo nl2br(htmlspecialchars($meeting['activity_details'])); ?></p>
            </div>
            
            <div class="details-box">
                <h4>Toolbox Talk Details</h4>
                <p><?php echo nl2br(htmlspecialchars($meeting['toolbox_details'])); ?></p>
            </div>
        </div>
        
        <div class="section">
            <h2>PPE Verification</h2>
            <div class="ppe-grid">
                <div class="ppe-item">
                    <strong>Safety Boot</strong>
                    <?php echo displayStatus($meeting['safety_boot']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Helmet</strong>
                    <?php echo displayStatus($meeting['helmet']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Reflective Jacket</strong>
                    <?php echo displayStatus($meeting['reflective_jacket']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Full Body Harness</strong>
                    <?php echo displayStatus($meeting['full_body_harness']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Rescue Kits</strong>
                    <?php echo displayStatus($meeting['rescue_kits']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Hand Gloves</strong>
                    <?php echo displayStatus($meeting['hand_gloves']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Nose Mask</strong>
                    <?php echo displayStatus($meeting['nose_mask']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Ear Plugs</strong>
                    <?php echo displayStatus($meeting['ear_plugs']); ?>
                </div>
                <div class="ppe-item">
                    <strong>Riggers Certified</strong>
                    <?php echo displayStatus($meeting['riggers_certified']); ?>
                </div>
                <div class="ppe-item">
                    <strong>First Aiders Certified</strong>
                    <?php echo displayStatus($meeting['first_aiders_certified']); ?>
                </div>
                <div class="ppe-item">
                    <strong>First Aid Kits</strong>
                    <?php echo displayStatus($meeting['first_aid_kits']); ?>
                </div>
            </div>
            
            <?php if (!empty($meeting['other_ppes'])): ?>
            <div class="details-box">
                <h4>Other PPE Equipment</h4>
                <p><?php echo nl2br(htmlspecialchars($meeting['other_ppes'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>Attendees</h2>
            <?php if ($attendees->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Contact</th>
                            <th>Signature</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($attendee = $attendees->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($attendee['name']); ?></td>
                                <td><?php echo htmlspecialchars($attendee['role']); ?></td>
                                <td><?php echo htmlspecialchars($attendee['contact']); ?></td>
                                <td>
                                    <div class="signature-box">
                                        <?php echo htmlspecialchars($attendee['signature']); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No attendees recorded for this meeting.</p>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>Declaration</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Declared By</strong>
                    <?php echo htmlspecialchars($meeting['declaration_name']); ?>
                </div>
                <div class="info-item">
                    <strong>Date</strong>
                    <?php echo htmlspecialchars($meeting['declaration_date']); ?>
                </div>
                <div class="info-item">
                    <strong>Signature</strong>
                    <span class="signature-box"><?php echo htmlspecialchars($meeting['declaration_signature']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Images -->
        <?php if ($images->num_rows > 0): ?>
        <div class="section">
            <h3>Meeting Photos</h3>
            <div class="images-container">
                <?php while ($image = $images->fetch_assoc()): ?>
                    <div class="image-card">
                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Meeting Photo">
                        <div class="image-caption">
                            Photo #<?php echo $image['id']; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="actions">
            <a href="view_all_toolbox_reports.php" class="btn">Back to Reports</a>
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