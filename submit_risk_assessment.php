<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');

// Database connection details
$host = "localhost";
$user = "root"; 
$password = ""; 
$database = "health_safety_db";

// Connect to MySQL with error handling
try {
    $conn = new mysqli($host, $user, $password, $database);
    
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
    $requiredFields = ['siteInfo', 'date', 'scope1'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Required field missing: $field");
        }
    }

    // Get counts from form data
    $incidenceCount = isset($_POST['incidenceCount']) ? intval($_POST['incidenceCount']) : 0;
    $hazardCount = isset($_POST['hazardCount']) ? intval($_POST['hazardCount']) : 0;
    $teamCount = isset($_POST['teamCount']) ? intval($_POST['teamCount']) : 0;

    // Generate assessment number (format: RA-YYYYMMDD-XXXX)
    $dateStr = date('Ymd');
    $randomNum = mt_rand(1000, 9999);
    $assessmentNo = "RA-{$dateStr}-{$randomNum}";

    // 1. Insert main assessment data
    $sql = "INSERT INTO risk_assessments (
        assessment_no, site_info, assessment_date, status, created_at
    ) VALUES (?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }

    $siteInfo = $conn->real_escape_string($_POST['siteInfo']);
    $assessmentDate = $conn->real_escape_string($_POST['date']);
    $status = 'Active';

    $stmt->bind_param("ssss", 
        $assessmentNo,
        $siteInfo,
        $assessmentDate,
        $status
    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to insert assessment data: " . $stmt->error);
    }

    $assessmentId = $conn->insert_id;
    $stmt->close();

    // 2. Insert scope of works
    for ($i = 1; $i <= 4; $i++) {
        if (isset($_POST["scope$i"]) && !empty(trim($_POST["scope$i"]))) {
            $scope = $conn->real_escape_string($_POST["scope$i"]);
            
            $stmt = $conn->prepare("INSERT INTO risk_assessment_scope 
                (assessment_id, scope_item, scope_order) VALUES (?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception("Prepare statement failed for scope: " . $conn->error);
            }
            
            $stmt->bind_param("isi", $assessmentId, $scope, $i);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert scope item: " . $stmt->error);
            }
            
            $stmt->close();
        }
    }

    // 3. Insert incidents if any
    for ($i = 1; $i <= $incidenceCount; $i++) {
        if (isset($_POST["incidence$i"]) && !empty(trim($_POST["incidence$i"]))) {
            $stmt = $conn->prepare("INSERT INTO risk_assessment_incidents 
                (assessment_id, incident, lesson_learnt, control_measure) 
                VALUES (?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception("Prepare statement failed for incidents: " . $conn->error);
            }
            
            $incident = $conn->real_escape_string($_POST["incidence$i"]);
            $lesson = $conn->real_escape_string($_POST["lesson$i"] ?? '');
            $control = $conn->real_escape_string($_POST["control$i"] ?? '');
            
            $stmt->bind_param("isss", $assessmentId, $incident, $lesson, $control);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert incident: " . $stmt->error);
            }
            
            $stmt->close();
        }
    }

    // 4. Insert hazards
    for ($i = 1; $i <= $hazardCount; $i++) {
        if (isset($_POST["hazard$i"]) && !empty(trim($_POST["hazard$i"]))) {
            $stmt = $conn->prepare("INSERT INTO risk_assessment_hazards 
                (assessment_id, hazard, affected_parties, risk_level, 
                existing_controls, additional_controls, action_by, action_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception("Prepare statement failed for hazards: " . $conn->error);
            }
            
            $hazard = $conn->real_escape_string($_POST["hazard$i"]);
            $affected = $conn->real_escape_string($_POST["affected$i"] ?? '');
            $risk = $conn->real_escape_string($_POST["risk$i"] ?? '');
            $existingControl = $conn->real_escape_string($_POST["existingControl$i"] ?? '');
            $additionalControl = $conn->real_escape_string($_POST["additionalControl$i"] ?? '');
            $actionBy = $conn->real_escape_string($_POST["actionBy$i"] ?? '');
            $when = !empty($_POST["when$i"]) ? $conn->real_escape_string($_POST["when$i"]) : null;
            $status = $conn->real_escape_string($_POST["status$i"] ?? 'Open');
            
            $stmt->bind_param("issssssss", 
                $assessmentId, $hazard, $affected, $risk, 
                $existingControl, $additionalControl, $actionBy, $when, $status
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert hazard: " . $stmt->error);
            }
            
            $stmt->close();
        }
    }

    // 5. Insert team members
    for ($i = 1; $i <= $teamCount; $i++) {
        if (isset($_POST["teamName$i"]) && !empty(trim($_POST["teamName$i"]))) {
            $stmt = $conn->prepare("INSERT INTO risk_assessment_team 
                (assessment_id, name, title, signature) 
                VALUES (?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception("Prepare statement failed for team: " . $conn->error);
            }
            
            $name = $conn->real_escape_string($_POST["teamName$i"]);
            $title = $conn->real_escape_string($_POST["teamTitle$i"] ?? '');
            $signature = $conn->real_escape_string($_POST["teamSignature$i"] ?? '');
            
            $stmt->bind_param("isss", $assessmentId, $name, $title, $signature);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert team member: " . $stmt->error);
            }
            
            $stmt->close();
        }
    }

    // 6. Handle image uploads
    $images = [];
    $uploadDir = 'uploads/';

    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception("Failed to create upload directory");
        }
    }

    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '22M');
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', 300);

        $stmt = $conn->prepare("INSERT INTO risk_assessment_images 
            (assessment_id, original_filename, stored_filename, file_size, mime_type, file_path) 
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
                    continue;
                }

                // Check file size (max 20MB)
                if ($fileSize > 20 * 1024 * 1024) {
                    continue;
                }

                // Generate unique filename
                $newFileName = "risk_{$assessmentId}_" . uniqid() . '_' . basename($fileName);
                $filePath = $uploadDir . $newFileName;

                if (move_uploaded_file($tmpName, $filePath)) {
                    $stmt->bind_param("ississ",
                        $assessmentId,
                        $fileName,
                        $newFileName,
                        $fileSize,
                        $fileType,
                        $filePath
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

        // Update image count
        if (count($images) > 0) {
            $stmt = $conn->prepare("UPDATE risk_assessments SET image_count = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare image count update: " . $conn->error);
            }
            
            $imageCount = count($images);
            $stmt->bind_param("ii", $imageCount, $assessmentId);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update image count: " . $stmt->error);
            }
            
            $stmt->close();
        }
    }

    // Commit transaction
    $conn->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Risk assessment submitted successfully!',
        'assessment_id' => $assessmentId,
        'assessment_no' => $assessmentNo,
        'image_count' => count($images)
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    error_log('Error in submit_risk_assessment.php: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?>