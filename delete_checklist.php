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

// Start transaction
$conn->begin_transaction();

try {
    // First, get checklist info for reference
    $info_sql = "SELECT checklist_no, team_leader, team_leader_id FROM first_aid_kit_checklists WHERE id = ?";
    $info_stmt = $conn->prepare($info_sql);
    $info_stmt->bind_param("i", $checklist_id);
    $info_stmt->execute();
    $info_result = $info_stmt->get_result();
    
    if ($info_result->num_rows == 0) {
        throw new Exception("Checklist not found.");
    }
    
    $checklist_info = $info_result->fetch_assoc();
    $team_leader_id = $checklist_info['team_leader_id'];
    
    // Get images to delete files
    $images_sql = "SELECT file_path FROM first_aid_kit_images WHERE checklist_id = ?";
    $images_stmt = $conn->prepare($images_sql);
    $images_stmt->bind_param("i", $checklist_id);
    $images_stmt->execute();
    $images_result = $images_stmt->get_result();
    
    $image_files = [];
    while ($image = $images_result->fetch_assoc()) {
        $image_files[] = $image['file_path'];
    }
    
    // 1. Delete images from database
    $delete_images_sql = "DELETE FROM first_aid_kit_images WHERE checklist_id = ?";
    $delete_images_stmt = $conn->prepare($delete_images_sql);
    $delete_images_stmt->bind_param("i", $checklist_id);
    $delete_images_stmt->execute();
    
    // 2. Delete items from database
    $delete_items_sql = "DELETE FROM first_aid_kit_items WHERE checklist_id = ?";
    $delete_items_stmt = $conn->prepare($delete_items_sql);
    $delete_items_stmt->bind_param("i", $checklist_id);
    $delete_items_stmt->execute();
    
    // 3. Delete checklist from database
    $delete_checklist_sql = "DELETE FROM first_aid_kit_checklists WHERE id = ?";
    $delete_checklist_stmt = $conn->prepare($delete_checklist_sql);
    $delete_checklist_stmt->bind_param("i", $checklist_id);
    
    if (!$delete_checklist_stmt->execute()) {
        throw new Exception("Failed to delete checklist: " . $delete_checklist_stmt->error);
    }
    
    // 4. Log the activity
    $activityLog = "INSERT INTO activity_log 
        (action_type, module, record_id, user_action, action_details, ip_address) 
        VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($activityLog);
    if ($stmt) {
        $actionType = 'DELETE';
        $module = 'first_aid_kit_checklists';
        $userAction = 'Deleted first aid kit checklist';
        $actionDetails = json_encode([
            'checklist_no' => $checklist_info['checklist_no'],
            'team_leader' => $checklist_info['team_leader']
        ]);
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        
        $stmt->bind_param("ssisss", 
            $actionType,
            $module,
            $checklist_id,
            $userAction,
            $actionDetails,
            $ipAddress
        );
        
        $stmt->execute();
    }
    
    // Commit the transaction
    $conn->commit();
    
    // Delete image files from server
    foreach ($image_files as $file_path) {
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    $_SESSION['message'] = "Checklist deleted successfully.";
    $_SESSION['message_type'] = "success";
    
    // Redirect back to team leader details if we have a team leader ID
    if ($team_leader_id > 0) {
        header("Location: team_leader_details.php?id=" . $team_leader_id);
    } else {
        header("Location: view_team_leaders.php");
    }
    exit();
    
} catch (Exception $e) {
    // Rollback the transaction
    $conn->rollback();
    
    $_SESSION['message'] = "Error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    header("Location: view_team_leaders.php");
    exit();
}

// Close the database connection
$conn->close();
?>