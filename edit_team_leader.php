<?php
session_start();

// Check if the session is valid
if (!isset($_SESSION['username'])) {
    // If session is not valid, redirect to the login page
    header("Location: login_admin.php");
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Log form submission for debugging
        error_log("Form submitted for team leader ID: " . $leader_id);
        error_log("POST data: " . print_r($_POST, true));
        
        // Validate input
        if (empty($_POST['first_name']) || empty($_POST['last_name'])) {
            throw new Exception("First and last name are required.");
        }
        
        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Valid email is required.");
        }
        
        // Sanitize input data
        $first_name = $conn->real_escape_string($_POST['first_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $site_name = $conn->real_escape_string($_POST['site_name']);
        $site_number = $conn->real_escape_string($_POST['site_number']);
        $status = $conn->real_escape_string($_POST['status']);
        
        error_log("Sanitized data - First Name: $first_name, Last Name: $last_name, Email: $email");
        
        // Check if email already exists (excluding the current user)
        $email_check = "SELECT id FROM team_leaders WHERE email = ? AND id != ? LIMIT 1";
        $check_stmt = $conn->prepare($email_check);
        
        if (!$check_stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        $check_stmt->bind_param("si", $email, $leader_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            throw new Exception("Email already in use by another team leader.");
        }
        
        // Update team leader
        $update_sql = "UPDATE team_leaders SET 
                      first_name = ?, 
                      last_name = ?, 
                      email = ?, 
                      site_name = ?, 
                      site_number = ?, 
                      status = ? 
                      WHERE id = ?";
        
        error_log("Update SQL: $update_sql");
        
        $update_stmt = $conn->prepare($update_sql);
        
        if (!$update_stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        $update_stmt->bind_param("ssssssi", 
            $first_name, 
            $last_name, 
            $email, 
            $site_name, 
            $site_number, 
            $status, 
            $leader_id
        );
        
        $result = $update_stmt->execute();
        error_log("Update execute result: " . ($result ? "Success" : "Failed - " . $update_stmt->error));
        
        if ($result) {
            // Check if first_aid_kit_checklists table exists and has team_leader column
            $check_table = $conn->query("SHOW TABLES LIKE 'first_aid_kit_checklists'");
            if ($check_table->num_rows > 0) {
                $check_column = $conn->query("SHOW COLUMNS FROM first_aid_kit_checklists LIKE 'team_leader'");
                if ($check_column->num_rows > 0) {
                    // Also update team leader name in checklists
                    $full_name = $first_name . ' ' . $last_name;
                    
                    // Check if team_leader_id column exists
                    $check_id_column = $conn->query("SHOW COLUMNS FROM first_aid_kit_checklists LIKE 'team_leader_id'");
                    if ($check_id_column->num_rows > 0) {
                        $update_checklists = "UPDATE first_aid_kit_checklists SET team_leader = ? WHERE team_leader_id = ?";
                        $update_checklists_stmt = $conn->prepare($update_checklists);
                        $update_checklists_stmt->bind_param("si", $full_name, $leader_id);
                        $update_checklists_result = $update_checklists_stmt->execute();
                        error_log("Update checklists result: " . ($update_checklists_result ? "Success" : "Failed - " . $update_checklists_stmt->error));
                    }
                }
            }
            
            $_SESSION['message'] = "Team leader updated successfully.";
            $_SESSION['message_type'] = "success";
            header("Location: team_leader_details.php?id=" . $leader_id);
            exit();
        } else {
            throw new Exception("Error updating team leader: " . $update_stmt->error);
        }
        
    } catch (Exception $e) {
        error_log("Error in form processing: " . $e->getMessage());
        $_SESSION['form_error'] = $e->getMessage();
        $_SESSION['form_data'] = $_POST;
    }
}

// Get team leader data
$sql = "SELECT * FROM team_leaders WHERE id = ?";
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

// If there's form data from a failed submission, use it instead
if (isset($_SESSION['form_data'])) {
    $form_data = $_SESSION['form_data'];
    unset($_SESSION['form_data']);
} else {
    $form_data = $leader;
}

// Function to display message
function displayMessage() {
    if (isset($_SESSION['form_error'])) {
        echo '<div class="response-message error-message">' . $_SESSION['form_error'] . '</div>';
        unset($_SESSION['form_error']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Team Leader</title>
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
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .card-header h2 {
            color: #2a5298;
            font-size: 1.5em;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 0 2px rgba(42, 82, 152, 0.1);
        }
        input.error, select.error {
            border-color: #e74c3c;
        }
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .submit-button {
            background: #2a5298;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            display: inline-block;
            margin-top: 10px;
        }
        .submit-button:hover {
            background: #1e3c72;
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
        .debug-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
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
            <h1>Edit Team Leader</h1>
            <a href="team_leader_details.php?id=<?php echo $leader_id; ?>" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Details
            </a>
        </div>
        
        <?php displayMessage(); ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Edit Information</h2>
            </div>
            
            <form action="edit_team_leader.php?id=<?php echo $leader_id; ?>" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($form_data['first_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($form_data['last_name']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="site_name">Site Name</label>
                        <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($form_data['site_name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="site_number">Site Number</label>
                        <input type="text" id="site_number" name="site_number" value="<?php echo htmlspecialchars($form_data['site_number']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="Active" <?php echo ($form_data['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo ($form_data['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="submit-button">Update Team Leader</button>
                </div>
            </form>
            
            <!-- Debug information - remove in production -->
            <div class="debug-info">
                <p><strong>Debug Info:</strong></p>
                <p>Leader ID: <?php echo $leader_id; ?></p>
                <p>Form Method: <?php echo $_SERVER['REQUEST_METHOD']; ?></p>
                <p>Form Action: <?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $leader_id); ?></p>
                <p>Form Data: <?php echo isset($_POST) ? htmlspecialchars(print_r($_POST, true)) : 'No POST data'; ?></p>
                <?php if (isset($_SESSION['form_data'])): ?>
                    <p>Session Form Data: <?php echo htmlspecialchars(print_r($_SESSION['form_data'], true)); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>