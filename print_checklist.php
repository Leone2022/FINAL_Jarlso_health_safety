<?php
session_start();

// Check if the session is valid
if (!isset($_SESSION['username'])) {
    // If session is not valid, redirect to the login page
    header("Location: login_admin.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Invalid checklist ID.";
    $_SESSION['message_type'] = "error";
    header("Location: view_team_leaders.php");
    exit();
}

$checklist_id = (int)$_GET['id'];

// Database connection
$host = "localhost";
$user = "root"; // Change to your MySQL username
$password = ""; // Change to your MySQL password 
$database = "health_safety_db";

// Connect to MySQL
try {
    $conn = new mysqli($host, $user, $password, $database);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Set character set
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get checklist details
$sql = "SELECT c.*, 
        IFNULL(tl.name, c.team_leader) as team_leader_name
        FROM first_aid_kit_checklists c
        LEFT JOIN team_leaders tl ON c.team_leader = tl.name OR c.team_leader_id = tl.id
        WHERE c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $checklist_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Checklist not found.");
}

$checklist = $result->fetch_assoc();

// Get checklist items
$items_sql = "SELECT * FROM first_aid_kit_items WHERE checklist_id = ? ORDER BY id ASC";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $checklist_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Get checklist images
$images_sql = "SELECT * FROM first_aid_kit_images WHERE checklist_id = ? ORDER BY id ASC LIMIT 4";
$images_stmt = $conn->prepare($images_sql);
$images_stmt->bind_param("i", $checklist_id);
$images_stmt->execute();
$images_result = $images_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print First Aid Kit Checklist #<?php echo $checklist['checklist_no']; ?></title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 1cm;
            }
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                font-size: 12pt;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            color: #333;
            background: #fff;
            padding: 20px;
            margin: 0;
        }
        
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }
        
        .print-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #1e3c72;
            margin-bottom: 20px;
            position: relative;
        }
        
        .company-name {
            font-size: 24pt;
            font-weight: bold;
            color: #1e3c72;
            margin-bottom: 5px;
        }
        
        .logo-container {
            position: absolute;
            top: 0;
            right: 0;
        }
        
        .logo {
            height: 60px;
            width: auto;
        }
        
        h1 {
            margin: 0;
            font-size: 18pt;
            color: #1e3c72;
        }
        
        .document-number {
            font-size: 10pt;
            color: #666;
            margin-top: 5px;
        }
        
        .checklist-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .info-group {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #1e3c72;
        }
        
        .info-value {
            margin: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #1e3c72;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .images-section {
            margin-top: 20px;
        }
        
        .images-title {
            font-size: 14pt;
            color: #1e3c72;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        
        .images-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .image-container {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        
        .image-container img {
            max-width: 100%;
            height: auto;
            max-height: 150px;
        }
        
        .signatures {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 45%;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            height: 40px;
            margin-bottom: 5px;
        }
        
        .signature-name {
            font-weight: bold;
        }
        
        .no-print {
            text-align: center;
            margin: 20px 0;
        }
        
        .print-button {
            background: #1e3c72;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        
        .print-button:hover {
            background: #2a5298;
        }
        
        .no-items, .no-images {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            color: #777;
        }
        
        footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10pt;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="print-header">
            <div class="company-name">Jarlso Health and Safety</div>
            <h1>FIRST AID KIT CHECKLIST</h1>
            <div class="document-number">JTS/FA/F046</div>
            <div class="logo-container">
                <img src="images/jarlso.png" alt="Jarlso Logo" class="logo">
            </div>
        </div>
        
        <div class="checklist-info">
            <div>
                <div class="info-group">
                    <div class="info-label">Checklist Number:</div>
                    <p class="info-value"><?php echo htmlspecialchars($checklist['checklist_no']); ?></p>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Team Leader:</div>
                    <p class="info-value"><?php echo htmlspecialchars($checklist['team_leader_name']); ?></p>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Location:</div>
                    <p class="info-value"><?php echo htmlspecialchars($checklist['location']); ?></p>
                </div>
            </div>
            
            <div>
                <div class="info-group">
                    <div class="info-label">Date:</div>
                    <p class="info-value"><?php echo date('d/m/Y', strtotime($checklist['confirmation_date'])); ?></p>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Status:</div>
                    <p class="info-value"><?php echo htmlspecialchars($checklist['status']); ?></p>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Confirmed By:</div>
                    <p class="info-value"><?php echo htmlspecialchars($checklist['confirmed_by']); ?></p>
                </div>
            </div>
        </div>
        
        <?php if ($items_result && $items_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 30%;">Item</th>
                        <th style="width: 45%;">Description</th>
                        <th style="width: 20%;">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1; while($item = $items_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['description']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-items">No items were added to this checklist.</div>
        <?php endif; ?>
        
        <?php if ($images_result && $images_result->num_rows > 0): ?>
            <div class="images-section">
                <h2 class="images-title">Attached Images</h2>
                <div class="images-grid">
                    <?php while($image = $images_result->fetch_assoc()): ?>
                        <div class="image-container">
                            <img src="<?php echo htmlspecialchars($image['file_path']); ?>" alt="First Aid Kit Image">
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-name">Team Leader</div>
                <div><?php echo htmlspecialchars($checklist['team_leader_name']); ?></div>
            </div>
            
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-name">Confirmed By</div>
                <div><?php echo htmlspecialchars($checklist['confirmed_by']); ?></div>
            </div>
        </div>
        
        <footer>
            <p>Jarlso Health and Safety | Printed on: <?php echo date('d/m/Y H:i'); ?></p>
        </footer>
    </div>
    
    <div class="no-print">
        <button class="print-button" onclick="window.print()">Print Checklist</button>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>