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
    // Validate required fields
    $requiredFields = [
        'siteID', 'siteName', 'projectTitle', 'date', 
        'teamLeaderName', 'teamLeaderDate', 'hseName', 'declarationSignature'
    ];
    
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Required field missing: $field");
        }
    }
    
    // Get and sanitize form data
    $siteID = $conn->real_escape_string($_POST['siteID']);
    $siteName = $conn->real_escape_string($_POST['siteName']);
    $projectTitle = $conn->real_escape_string($_POST['projectTitle']);
    $date = $conn->real_escape_string($_POST['date']);
    
    // PPE responses (with NULL handling)
    $ppeItems = [
        'safetyBoot', 'helmet', 'reflectiveJacket', 'fullBodyHarness',
        'rescueKits', 'handGloves', 'noseMask', 'earPlugs',
        'firstAiderCertified', 'firstAidKits', 'goggles',
        'fireExtinguisher', 'otherPPEs'
    ];
    
    $ppeData = [];
    foreach ($ppeItems as $item) {
        $ppeData[$item] = isset($_POST[$item]) ? $conn->real_escape_string($_POST[$item]) : NULL;
    }
    
    // Additional details
    $additionalDetails = isset($_POST['additionalDetails']) ? 
                          $conn->real_escape_string($_POST['additionalDetails']) : NULL;
    
    // Team Leader & HSE Representative Info
    $teamLeaderName = $conn->real_escape_string($_POST['teamLeaderName']);
    $teamLeaderDate = $conn->real_escape_string($_POST['teamLeaderDate']);
    $hseName = $conn->real_escape_string($_POST['hseName']);
    $declarationSignature = $conn->real_escape_string($_POST['declarationSignature']);
    
    // Handle uploaded images
    $images = [];
    $uploadDir = 'uploads/';
    
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception("Failed to create upload directory");
        }
    }
    
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $key => $fileName) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['images']['tmp_name'][$key];
                $fileSize = $_FILES['images']['size'][$key];
                $fileType = $_FILES['images']['type'][$key];
                
                // Validate file type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($fileType, $allowedTypes)) {
                    continue; // Skip non-image files
                }
                
                // Check file size (max 5MB)
                if ($fileSize > 5 * 1024 * 1024) {
                    continue; // Skip files larger than 5MB
                }
                
                // Generate unique filename
                $newFileName = uniqid('ppe_') . '_' . basename($fileName);
                $filePath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($tmpName, $filePath)) {
                    $images[] = [
                        'path' => $filePath,
                        'name' => $fileName,
                        'size' => $fileSize,
                        'type' => $fileType
                    ];
                }
            }
        }
    }
    
    // Convert image info to JSON
    $imagesJson = !empty($images) ? json_encode($images) : null;
    
    // Prepare SQL statement with all fields
    $sql = "INSERT INTO ppe_register (
        siteID, siteName, projectTitle, date, 
        safetyBoot, helmet, reflectiveJacket, fullBodyHarness,
        rescueKits, handGloves, noseMask, earPlugs,
        firstAiderCertified, firstAidKits, goggles, fireExtinguisher, 
        otherPPEs, additionalDetails, teamLeaderName, teamLeaderDate, 
        hseName, declarationSignature, images
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    // Bind parameters
    $stmt->bind_param(
        'sssssssssssssssssssssss',
        $siteID, $siteName, $projectTitle, $date,
        $ppeData['safetyBoot'], $ppeData['helmet'], $ppeData['reflectiveJacket'], $ppeData['fullBodyHarness'],
        $ppeData['rescueKits'], $ppeData['handGloves'], $ppeData['noseMask'], $ppeData['earPlugs'],
        $ppeData['firstAiderCertified'], $ppeData['firstAidKits'], $ppeData['goggles'], $ppeData['fireExtinguisher'],
        $ppeData['otherPPEs'], $additionalDetails, $teamLeaderName, $teamLeaderDate,
        $hseName, $declarationSignature, $imagesJson
    );
    
    // Execute statement
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $recordId = $conn->insert_id;
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'PPE register form submitted successfully',
        'record_id' => $recordId,
        'image_count' => count($images)
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log error for server-side tracking
    error_log('Error in submit_ppe.php: ' . $e->getMessage());
    
    // Return error response to client
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?>