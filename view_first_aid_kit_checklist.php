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
    header("Location: view_first_aid_kit_checklists.php");
    exit();
}

$checklistId = $_GET['id'];

// Get checklist details
$sql = "SELECT * FROM first_aid_kit_checklists WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $checklistId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Checklist not found.";
    exit();
}

$checklist = $result->fetch_assoc();

// Get checklist items
$sql = "SELECT * FROM first_aid_kit_items WHERE checklist_id = ? ORDER BY id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $checklistId);
$stmt->execute();
$items = $stmt->get_result();

// Get images
$sql = "SELECT * FROM first_aid_kit_images WHERE checklist_id = ? ORDER BY id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $checklistId);
$stmt->execute();
$images = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>First Aid Kit Checklist</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: linear-gradient(to right, #c62828, #e53935);
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
            color: #c62828;
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
            color: #c62828;
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
            background-color: #c62828;
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
            background: #c62828;
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
        <h1>First Aid Kit Checklist</h1>
        
        <div class="section">
            <h2>Checklist Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Checklist ID</strong>
                    <?php echo htmlspecialchars($checklist['id']); ?>
                </div>
                <div class="info-item">
                    <strong>Checklist Number</strong>
                    <?php echo htmlspecialchars($checklist['checklist_no']); ?>
                </div>
                <div class="info-item">
                    <strong>Team Leader</strong>
                    <?php echo htmlspecialchars($checklist['team_leader']); ?>
                </div>
                <div class="info-item">
                    <strong>Location</strong>
                    <?php echo htmlspecialchars($checklist['location']); ?>
                </div>
                <div class="info-item">
                    <strong>Confirmed By</strong>
                    <?php echo htmlspecialchars($checklist['confirmed_by']); ?>
                </div>
                <div class="info-item">
                    <strong>Confirmation Date</strong>
                    <?php echo htmlspecialchars($checklist['confirmation_date']); ?>
                </div>
                <div class="info-item">
                    <strong>Status</strong>
                    <?php echo htmlspecialchars($checklist['status']); ?>
                </div>
                <div class="info-item">
                    <strong>Created At</strong>
                    <?php echo isset($checklist['created_at']) ? htmlspecialchars($checklist['created_at']) : 'N/A'; ?>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2>First Aid Kit Items</h2>
            <?php if ($items->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Description</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['description']); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No items recorded.</p>
            <?php endif; ?>
        </div>
        
        <!-- Images -->
        <div class="section">
            <h3>Checklist Photos</h3>
            <div class="images-container">
                <?php if ($images->num_rows > 0): ?>
                    <?php while ($image = $images->fetch_assoc()): ?>
                        <div class="image-card">
                            <img src="<?php echo htmlspecialchars($image['file_path']); ?>" alt="First Aid Kit">
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
            <a href="view_first_aid_kit_checklists.php" class="btn">Back to Checklists</a>
            <button class="btn" onclick="printReport()">Print Checklist</button>
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