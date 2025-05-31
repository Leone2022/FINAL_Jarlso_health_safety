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
    $requiredFields = [
        'reportedBy', 'dateReported', 'incidentNo', 'reportedByClient',
        'affectedProject', 'hseAspects', 'incidentDescription', 
        'correctiveActions', 'rootCauseAnalysis', 'carriedOutBy',
        'dateCarriedOut', 'verificationComments', 'preventiveActions',
        'verificationCommentsPreventive', 'reportName', 'reportDate'
    ];
    
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Required field missing: $field");
        }
    }
    
    // Sanitize input data
    $reportedBy = $conn->real_escape_string($_POST['reportedBy']);
    $dateReported = $conn->real_escape_string($_POST['dateReported']);
    $incidentNo = $conn->real_escape_string($_POST['incidentNo']);
    $reportedByClient = $conn->real_escape_string($_POST['reportedByClient']);
    $clientName = $conn->real_escape_string($_POST['clientName'] ?? '');
    $affectedProject = $conn->real_escape_string($_POST['affectedProject']);
    $projectName = $conn->real_escape_string($_POST['projectName'] ?? '');
    $hseAspects = $conn->real_escape_string($_POST['hseAspects']);
    $incidentDescription = $conn->real_escape_string($_POST['incidentDescription']);
    $correctiveActions = $conn->real_escape_string($_POST['correctiveActions']);
    $rootCauseAnalysis = $conn->real_escape_string($_POST['rootCauseAnalysis']);
    $carriedOutBy = $conn->real_escape_string($_POST['carriedOutBy']);
    $dateCarriedOut = $conn->real_escape_string($_POST['dateCarriedOut']);
    $verificationComments = $conn->real_escape_string($_POST['verificationComments']);
    $preventiveActions = $conn->real_escape_string($_POST['preventiveActions']);
    $verificationCommentsPreventive = $conn->real_escape_string($_POST['verificationCommentsPreventive']);
    $reportName = $conn->real_escape_string($_POST['reportName']);
    $reportDate = $conn->real_escape_string($_POST['reportDate']);
    
    // 2. Insert main incident report data
    $sql = "INSERT INTO incident_reports (
        incident_no, reported_by, date_reported, reported_by_client, client_name,
        affected_project, project_name, hse_aspects, incident_description,
        corrective_actions, root_cause_analysis, carried_out_by, date_carried_out,
        verification_comments, preventive_actions, verification_comments_preventive,
        report_name, report_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $stmt->bind_param("ssssssssssssssssss",
        $incidentNo,
        $reportedBy,
        $dateReported,
        $reportedByClient,
        $clientName,
        $affectedProject,
        $projectName,
        $hseAspects,
        $incidentDescription,
        $correctiveActions,
        $rootCauseAnalysis,
        $carriedOutBy,
        $dateCarriedOut,
        $verificationComments,
        $preventiveActions,
        $verificationCommentsPreventive,
        $reportName,
        $reportDate
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert incident report data: " . $stmt->error);
    }
    
    $reportId = $conn->insert_id;
    $stmt->close();
    
    // 3. Handle image uploads
    $images = [];
    $uploadDir = 'uploads/';
    
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception("Failed to create upload directory");
        }
    }
    
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $stmt = $conn->prepare("INSERT INTO incident_images 
            (report_id, original_filename, stored_filename, file_size, mime_type, file_path) 
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
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($fileType, $allowedTypes)) {
                    continue; // Skip non-image files
                }
                
                // Check file size (max 5MB)
                if ($fileSize > 5 * 1024 * 1024) {
                    continue; // Skip files larger than 5MB
                }
                
                // Generate unique filename
                $newFileName = 'incident_' . $reportId . '_' . uniqid() . '_' . basename($fileName);
                $filePath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($tmpName, $filePath)) {
                    $originalFilename = $conn->real_escape_string($fileName);
                    $storedFilename = $conn->real_escape_string($newFileName);
                    $mimeType = $conn->real_escape_string($fileType);
                    $filePathDb = $conn->real_escape_string($filePath);
                    
                    $stmt->bind_param("ississ", 
                        $reportId,
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
        
        // Update the incident report with image count
        if (count($images) > 0) {
            $imageCount = count($images);
            $stmt = $conn->prepare("UPDATE incident_reports SET image_count = ? WHERE id = ?");
            
            if (!$stmt) {
                throw new Exception("Prepare statement failed for image count update: " . $conn->error);
            }
            
            $stmt->bind_param("ii", $imageCount, $reportId);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update image count: " . $stmt->error);
            }
            
            $stmt->close();
        }
    }
    
    // 4. Log the activity
    $activityLog = "INSERT INTO activity_log 
        (action_type, module, record_id, user_action, action_details, ip_address) 
        VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($activityLog);
    if ($stmt) {
        $actionType = 'INSERT';
        $module = 'incident_reports';
        $userAction = 'Created new incident report';
        $actionDetails = json_encode([
            'incident_no' => $incidentNo,
            'reported_by' => $reportedBy,
            'date_reported' => $dateReported,
            'image_count' => count($images)
        ]);
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        
        $stmt->bind_param("ssisss", 
            $actionType,
            $module,
            $reportId,
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
        'message' => 'Incident report submitted successfully',
        'report_id' => $reportId,
        'incident_no' => $incidentNo,
        'image_count' => count($images)
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log error for server-side tracking
    error_log('Error in submit_incident_report.php: ' . $e->getMessage());
    
    // Return error response to client
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?>