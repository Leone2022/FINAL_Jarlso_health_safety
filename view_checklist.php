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
        IFNULL(tl.id, 0) as team_leader_id,
        IFNULL(tl.name, c.team_leader) as team_leader_name,
        IFNULL(tl.email, '') as team_leader_email
        FROM first_aid_kit_checklists c
        LEFT JOIN team_leaders tl ON c.team_leader = tl.name OR c.team_leader_id = tl.id
        WHERE c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $checklist_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['message'] = "Checklist not found.";
    $_SESSION['message_type'] = "error";
    header("Location: view_team_leaders.php");
    exit();
}

$checklist = $result->fetch_assoc();

// Get checklist items
$items_sql = "SELECT * FROM first_aid_kit_items WHERE checklist_id = ? ORDER BY id ASC";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $checklist_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Get checklist images
$images_sql = "SELECT * FROM first_aid_kit_images WHERE checklist_id = ? ORDER BY id ASC";
$images_stmt = $conn->prepare($images_sql);
$images_stmt->bind_param("i", $checklist_id);
$images_stmt->execute();
$images_result = $images_stmt->get_result();

// Function to display message
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $messageType = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
        $alertClass = ($messageType == 'success') ? 'success-message' : 'error-message';
        
        echo '<div class="response-message ' . $alertClass . '">' . $_SESSION['message'] . '</div>';
        
        // Clear the message from session
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checklist Details</title>
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
        .main-content {
            margin-left: 220px;
            padding: 40px;
            width: calc(100% - 220px);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        h1 {
            color: #2a5298;
        }
        .back-button {
            background: #1e3c72;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
        }
        .back-button i {
            margin-right: 5px;
        }
        .back-button:hover {
            background-color: #2a5298;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .card-header h2 {
            color: #2a5298;
            font-size: 1.5em;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .action-buttons a {
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-weight: bold;
        }
        .print-btn {
            background-color: #3498db;
        }
        .print-btn:hover {
            background-color: #2980b9;
        }
        .delete-btn {
            background-color: #e74c3c;
        }
        .delete-btn:hover {
            background-color: #c0392b;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .info-item {
            margin-bottom: 15px;
        }
        .info-item h3 {
            font-size: 0.9em;
            color: #777;
            margin-bottom: 5px;
        }
        .info-item p {
            font-size: 1.1em;
            color: #333;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f1f5f9;
            color: #2a5298;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .response-message {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            font-weight: 500;
            text-align: center;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .images-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }
        .image-card {
            position: relative;
            width: 150px;
            height: 150px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .image-card:hover {
            transform: scale(1.05);
        }
        .image-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .no-items, .no-images {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
            color: #777;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="view_team_leaders.php"><i class="fas fa-users"></i> Team Leaders</a></li>
            <li><a href="register_team_leader.html"><i class="fas fa-user-plus"></i> Add Leader</a></li>
            <li><a href="Reports/all_reports.php"><i class="fas fa-file-alt"></i> Reports</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>First Aid Kit Checklist Details</h1>
            <?php if ($checklist['team_leader_id'] > 0): ?>
                <a href="team_leader_details.php?id=<?php echo $checklist['team_leader_id']; ?>" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Team Leader
                </a>
            <?php else: ?>
                <a href="view_team_leaders.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Team Leaders
                </a>
            <?php endif; ?>
        </div>
        
        <?php displayMessage(); ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Checklist #<?php echo htmlspecialchars($checklist['checklist_no']); ?></h2>
                <div class="action-buttons">
                    <a href="print_checklist.php?id=<?php echo $checklist_id; ?>" class="print-btn" target="_blank">
                        <i class="fas fa-print"></i> Print
                    </a>
                    <a href="#" class="delete-btn" onclick="confirmDelete(<?php echo $checklist_id; ?>)">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <h3>TEAM LEADER</h3>
                    <p><?php echo htmlspecialchars($checklist['team_leader_name']); ?></p>
                </div>
                
                <?php if (!empty($checklist['team_leader_email'])): ?>
                <div class="info-item">
                    <h3>EMAIL</h3>
                    <p><?php echo htmlspecialchars($checklist['team_leader_email']); ?></p>
                </div>
                <?php endif; ?>
                
                <div class="info-item">
                    <h3>LOCATION</h3>
                    <p><?php echo htmlspecialchars($checklist['location']); ?></p>
                </div>
                
                <div class="info-item">
                    <h3>STATUS</h3>
                    <p>
                        <span class="status-badge status-<?php echo strtolower($checklist['status']); ?>">
                            <?php echo htmlspecialchars($checklist['status']); ?>
                        </span>
                    </p>
                </div>
                
                <div class="info-item">
                    <h3>CONFIRMED BY</h3>
                    <p><?php echo htmlspecialchars($checklist['confirmed_by']); ?></p>
                </div>
                
                <div class="info-item">
                    <h3>CONFIRMATION DATE</h3>
                    <p><?php echo date('d M Y', strtotime($checklist['confirmation_date'])); ?></p>
                </div>
                
                <div class="info-item">
                    <h3>SUBMISSION DATE</h3>
                    <p><?php echo date('d M Y H:i', strtotime($checklist['created_at'])); ?></p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>Checklist Items</h2>
            </div>
            
            <?php if ($items_result && $items_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Description</th>
                            <th>Quantity</th>
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
                <div class="no-items">
                    <i class="fas fa-box-open" style="font-size: 2em; margin-bottom: 10px;"></i>
                    <p>No items were added to this checklist.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>Uploaded Images</h2>
            </div>
            
            <?php if ($images_result && $images_result->num_rows > 0): ?>
                <div class="images-container">
                    <?php while($image = $images_result->fetch_assoc()): ?>
                        <div class="image-card">
                            <a href="<?php echo htmlspecialchars($image['file_path']); ?>" target="_blank">
                                <img src="<?php echo htmlspecialchars($image['file_path']); ?>" alt="First Aid Kit Image">
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-images">
                    <i class="fas fa-images" style="font-size: 2em; margin-bottom: 10px;"></i>
                    <p>No images were uploaded with this checklist.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this checklist? This action cannot be undone.")) {
                window.location.href = `delete_checklist.php?id=${id}`;
            }
        }
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>