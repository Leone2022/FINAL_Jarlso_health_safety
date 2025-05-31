<?php
session_start();

// Check if the session is valid
if (!isset($_SESSION['username'])) {
    // If session is not valid, redirect to the login page
    header("Location: login_admin.php");
    exit();
}

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

// Check if delete action is requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $leader_id = (int)$_GET['delete'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Simply delete the team leader without checking for related records
        // This avoids issues with unknown columns in other tables
        $delete_sql = "DELETE FROM team_leaders WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $leader_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['message'] = "Team leader deleted successfully.";
            $_SESSION['message_type'] = "success";
            $conn->commit();
        } else {
            throw new Exception("Error deleting team leader: " . $delete_stmt->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
    
    // Redirect to refresh the page
    header("Location: view_team_leaders.php");
    exit();
}

// Get all team leaders - MODIFIED to match your database structure
$sql = "SELECT id, CONCAT(first_name, ' ', last_name) AS name, email, site_name AS department, site_number AS phone_number, registration_date AS date_registered, status FROM team_leaders ORDER BY first_name, last_name ASC";
$result = $conn->query($sql);

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
    <title>View Team Leaders</title>
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
        h1 {
            color: #2a5298;
            margin-bottom: 30px;
        }
        .actions-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .add-button {
            background-color: #2a5298;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .add-button:hover {
            background-color: #1e3c72;
        }
        .search-box {
            padding: 8px 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 300px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #2a5298;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .action-buttons a {
            display: inline-block;
            margin-right: 5px;
            padding: 6px 10px;
            border-radius: 3px;
            text-decoration: none;
            color: white;
            font-size: 14px;
        }
        .view-btn {
            background-color: #3498db;
        }
        .edit-btn {
            background-color: #2ecc71;
        }
        .delete-btn {
            background-color: #e74c3c;
        }
        .view-btn:hover {
            background-color: #2980b9;
        }
        .edit-btn:hover {
            background-color: #27ae60;
        }
        .delete-btn:hover {
            background-color: #c0392b;
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
        .status-active {
            color: #27ae60;
            font-weight: bold;
        }
        .status-inactive {
            color: #e74c3c;
            font-weight: bold;
        }
        .no-leaders {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .logout-btn {
            position: fixed;
            bottom: 30px;
            right: 50px;
            background-color: #e74c3c;
            color: #fff;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.1em;
            transition: background 0.3s;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }
        .sidebar-bottom {
            margin-top: auto;
            margin-bottom: 20px;
        }
        .logout-sidebar {
            background-color: #e74c3c !important;
        }
        .logout-sidebar:hover {
            background-color: #c0392b !important;
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
        
        <!-- Added Logout to sidebar -->
        <div class="sidebar-bottom">
            <ul>
                <li><a href="logout.php" class="logout-sidebar"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <h1>Team Leaders</h1>
        
        <?php displayMessage(); ?>
        
        <div class="actions-bar">
            <a href="register_team_leader.html" class="add-button"><i class="fas fa-plus"></i> Add New Team Leader</a>
            <input type="text" id="searchInput" class="search-box" placeholder="Search team leaders...">
        </div>
        
        <?php if ($result && $result->num_rows > 0): ?>
            <table id="teamLeadersTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Site Name</th>
                        <th>Site Number</th>
                        <th>Registered Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['date_registered'])); ?></td>
                            <td class="<?php echo ($row['status'] == 'Active') ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </td>
                            <td class="action-buttons">
                                <a href="team_leader_details.php?id=<?php echo $row['id']; ?>" class="view-btn" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit_team_leader.php?id=<?php echo $row['id']; ?>" class="edit-btn" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="delete-btn" title="Delete" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-leaders">
                <h3>No team leaders found</h3>
                <p>No team leaders have been registered yet. Click the "Add New Team Leader" button to get started.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Change to use logout.php -->
    <a href="logout.php" class="logout-btn">Logout</a>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = document.getElementById('teamLeadersTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                let found = false;
                const cells = rows[i].getElementsByTagName('td');
                
                for (let j = 0; j < cells.length - 1; j++) {
                    const cellText = cells[j].textContent || cells[j].innerText;
                    
                    if (cellText.toLowerCase().indexOf(searchValue) > -1) {
                        found = true;
                        break;
                    }
                }
                
                rows[i].style.display = found ? '' : 'none';
            }
        });
        
        // Confirm delete
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