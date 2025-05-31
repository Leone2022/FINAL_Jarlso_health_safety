<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');

// Database connection details
$host = "localhost";
$user = "root"; // Change to your MySQL username
$password = ""; // Change to your MySQL password 
$database = "health_safety_db";

// Connect to MySQL with error handling
try {
    $conn = new mysqli($host, $user, $password, $database);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Set character set
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    exit(json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]));
}

// Start transaction
$conn->begin_transaction();

try {
    // 1. Get and validate required form data
    if (!isset($_POST['teamLeader']) || empty(trim($_POST['teamLeader']))) {
        throw new Exception("Team Leader name is required");
    }
    
    if (!isset($_POST['confirmName']) || empty(trim($_POST['confirmName']))) {
        throw new Exception("Confirmed by name is required");
    }
    
    if (!isset($_POST['confirmDate']) || empty(trim($_POST['confirmDate']))) {
        throw new Exception("Confirmation date is required");
    }
    
    if (!isset($_POST['rowCount']) || intval($_POST['rowCount']) < 1) {
        throw new Exception("At least one item must be added to the checklist");
    }
    
    // Sanitize input data
    $teamLeader = $conn->real_escape_string($_POST['teamLeader']);
    $confirmName = $conn->real_escape_string($_POST['confirmName']);
    $confirmDate = $conn->real_escape_string($_POST['confirmDate']);
    $rowCount = intval($_POST['rowCount']);
    
    // Generate a checklist number with date and random number
    $date = new DateTime();
    $dateStr = $date->format('Ymd');
    $randomNum = mt_rand(1000, 9999);
    $checklistNo = "FA-" . $dateStr . "-" . $randomNum;
    
    // 2. Insert main checklist data
    $sql = "INSERT INTO first_aid_kit_checklists (
        checklist_no, team_leader, confirmed_by, confirmation_date, location, status
    ) VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $location = "Main Office"; // Default location or get from form if needed
    $status = "Completed";     // Default status
    
    $stmt->bind_param("ssssss",
        $checklistNo,
        $teamLeader,
        $confirmName,
        $confirmDate,
        $location,
        $status
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert checklist data: " . $stmt->error);
    }
    
    $checklistId = $conn->insert_id;
    $stmt->close();
    
    // 3. Insert checklist items
    $stmt = $conn->prepare("INSERT INTO first_aid_kit_items 
        (checklist_id, item_name, description, quantity) 
        VALUES (?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Prepare statement failed for items: " . $conn->error);
    }
    
    // Process all rows
    for ($i = 1; $i <= $rowCount; $i++) {
        if (isset($_POST["item$i"]) && !empty(trim($_POST["item$i"]))) {
            $itemName = $conn->real_escape_string($_POST["item$i"]);
            $description = $conn->real_escape_string($_POST["description$i"] ?? '');
            $quantity = intval($_POST["quantity$i"] ?? 0);
            
            $stmt->bind_param("issi",
                $checklistId,
                $itemName,
                $description,
                $quantity
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert item record: " . $stmt->error);
            }
        }
    }
    $stmt->close();
    
    // 4. Handle image uploads
    $images = [];
    $uploadDir = 'uploads/';
    
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception("Failed to create upload directory");
        }
    }
    
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        // Increase PHP limits for large file uploads
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '22M');
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', 300); // 5 minutes
        
        $stmt = $conn->prepare("INSERT INTO first_aid_kit_images 
            (checklist_id, original_filename, stored_filename, file_size, mime_type, file_path) 
            VALUES (?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Prepare statement failed for images: " . $conn->error);
        }
        
        foreach ($_FILES['images']['name'] as $key => $fileName) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['images']['tmp_name'][$key];
                $fileSize = $_FILES['images']['size'][$key];
                $fileType = $_FILES['images']['type'][$key];
                
                // Validate file type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/tiff'];
                if (!in_array($fileType, $allowedTypes)) {
                    continue; // Skip non-image files
                }
                
                // Check file size (max 20MB)
                if ($fileSize > 20 * 1024 * 1024) {
                    continue; // Skip files larger than 20MB
                }
                
                // Generate unique filename
                $newFileName = 'firstaid_' . $checklistId . '_' . uniqid() . '_' . basename($fileName);
                $filePath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($tmpName, $filePath)) {
                    $originalFilename = $conn->real_escape_string($fileName);
                    $storedFilename = $conn->real_escape_string($newFileName);
                    $mimeType = $conn->real_escape_string($fileType);
                    $filePathDb = $conn->real_escape_string($filePath);
                    
                    $stmt->bind_param("ississ", 
                        $checklistId,
                        $originalFilename,
                        $storedFilename,
                        $fileSize,
                        $mimeType,
                        $filePathDb
                    );
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to insert image record: " . $stmt->error);
                    }
                    
                    $images[] = [
                        'id' => $conn->insert_id,
                        'path' => $filePath
                    ];
                }
            }
        }
        $stmt->close();
        
        // Update the checklist with image count
        if (count($images) > 0) {
            $imageCount = count($images);
            $stmt = $conn->prepare("UPDATE first_aid_kit_checklists SET image_count = ? WHERE id = ?");
            
            if (!$stmt) {
                throw new Exception("Prepare statement failed for image count update: " . $conn->error);
            }
            
            $stmt->bind_param("ii", $imageCount, $checklistId);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update image count: " . $stmt->error);
            }
            
            $stmt->close();
        }
    }
    
    // 5. Log the activity
    $activityLog = "INSERT INTO activity_log 
        (action_type, module, record_id, user_action, action_details, ip_address) 
        VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($activityLog);
    if ($stmt) {
        $actionType = 'INSERT';
        $module = 'first_aid_kit_checklists';
        $userAction = 'Created new first aid kit checklist';
        $actionDetails = json_encode([
            'checklist_no' => $checklistNo,
            'team_leader' => $teamLeader,
            'item_count' => $rowCount,
            'image_count' => count($images)
        ]);
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        
        $stmt->bind_param("ssisss", 
            $actionType,
            $module,
            $checklistId,
            $userAction,
            $actionDetails,
            $ipAddress
        );
        
        $stmt->execute();
        $stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'First aid kit checklist submitted successfully',
        'checklist_id' => $checklistId,
        'checklist_no' => $checklistNo,
        'item_count' => $rowCount,
        'image_count' => count($images)
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log error for server-side tracking
    error_log('Error in submit_first_aid_kit_checklist.php: ' . $e->getMessage());
    
    // Return error response to client
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?>