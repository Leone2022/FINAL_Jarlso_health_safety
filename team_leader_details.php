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
    $_SESSION['message'] = "Invalid team leader ID.";
    $_SESSION['message_type'] = "error";
    header("Location: view_team_leaders.php");
    exit();
}

$leader_id = (int)$_GET['id'];

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

// Get team leader details - MODIFIED to match your database structure
$sql = "SELECT id, first_name, last_name, CONCAT(first_name, ' ', last_name) AS name, 
        email, site_name AS department, site_number AS phone_number, 
        registration_date AS date_registered, last_login, status 
        FROM team_leaders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $leader_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['message'] = "Team leader not found.";
    $_SESSION['message_type'] = "error";
    header("Location: view_team_leaders.php");
    exit();
}

$leader = $result->fetch_assoc();

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
    <title>Team Leader Details</title>
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
        .edit-btn {
            background-color: #2ecc71;
        }
        .edit-btn:hover {
            background-color: #27ae60;
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
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
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
            <h1>Team Leader Details</h1>
            <a href="view_team_leaders.php" class="back-button"><i class="fas fa-arrow-left"></i> Back to List</a>
        </div>
        
        <?php displayMessage(); ?>
        
        <div class="card">
            <div class="card-header">
                <h2><?php echo htmlspecialchars($leader['name']); ?></h2>
                <div class="action-buttons">
                    <a href="edit_team_leader.php?id=<?php echo $leader_id; ?>" class="edit-btn"><i class="fas fa-edit"></i> Edit</a>
                    <a href="#" class="delete-btn" onclick="confirmDelete(<?php echo $leader_id; ?>, '<?php echo addslashes($leader['name']); ?>')"><i class="fas fa-trash"></i> Delete</a>
                </div>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <h3>EMAIL</h3>
                    <p><?php echo htmlspecialchars($leader['email']); ?></p>
                </div>
                
                <div class="info-item">
                    <h3>SITE NAME</h3>
                    <p><?php echo htmlspecialchars($leader['department']); ?></p>
                </div>
                
                <div class="info-item">
                    <h3>SITE NUMBER</h3>
                    <p><?php echo htmlspecialchars($leader['phone_number']); ?></p>
                </div>
                
                <div class="info-item">
                    <h3>STATUS</h3>
                    <p>
                        <span class="status-badge <?php echo ($leader['status'] == 'Active') ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo htmlspecialchars($leader['status']); ?>
                        </span>
                    </p>
                </div>
                
                <div class="info-item">
                    <h3>REGISTERED DATE</h3>
                    <p><?php echo date('d M Y', strtotime($leader['date_registered'])); ?></p>
                </div>
                
                <?php if (isset($leader['last_login']) && !empty($leader['last_login'])): ?>
                <div class="info-item">
                    <h3>LAST LOGIN</h3>
                    <p><?php echo date('d M Y H:i', strtotime($leader['last_login'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(id, name) {
            if (confirm(`Are you sure you want to delete team leader "${name}"?`)) {
                window.location.href = `view_team_leaders.php?delete=${id}`;
            }
        }
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>