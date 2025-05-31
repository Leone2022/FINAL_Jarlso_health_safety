<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "health_safety_db";

try {
    $conn = new mysqli($host, $user, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
    // Start transaction
    $conn->begin_transaction();
    
    // 1. Insert main induction record
    $stmt = $conn->prepare("INSERT INTO site_induction (
        site_name, 
        site_number, 
        induction_declaration, 
        induction_date
    ) VALUES (?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ssss",
        $_POST['siteName'],
        $_POST['siteNumber'],
        $_POST['inductionDeclaration'],
        $_POST['inductionDate']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Error inserting induction record: " . $stmt->error);
    }
    
    $inductionId = $conn->insert_id;
    $stmt->close();
    
    // 2. Insert visitor log entries
    $visitorStmt = $conn->prepare("INSERT INTO visitor_log (
        induction_id,
        visit_date,
        visit_time,
        visitor_name,
        company_name,
        signature
    ) VALUES (?, ?, ?, ?, ?, ?)");
    
    if (!$visitorStmt) {
        throw new Exception("Prepare failed for visitor log: " . $conn->error);
    }
    
    $i = 1;
    while (isset($_POST["date$i"]) && isset($_POST["time$i"]) && 
           isset($_POST["name$i"]) && isset($_POST["company$i"]) && 
           isset($_POST["signature$i"])) {
        
        $visitorStmt->bind_param("isssss",
            $inductionId,
            $_POST["date$i"],
            $_POST["time$i"],
            $_POST["name$i"],
            $_POST["company$i"],
            $_POST["signature$i"]
        );
        
        if (!$visitorStmt->execute()) {
            throw new Exception("Error inserting visitor log entry: " . $visitorStmt->error);
        }
        
        $i++;
    }
    
    $visitorStmt->close();
    
    // 3. Handle file uploads
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $uploadDir = 'uploads/induction/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $docStmt = $conn->prepare("INSERT INTO supporting_documents (
            induction_id,
            file_name,
            file_path,
            file_type,
            file_size
        ) VALUES (?, ?, ?, ?, ?)");
        
        if (!$docStmt) {
            throw new Exception("Prepare failed for documents: " . $conn->error);
        }
        
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = $_FILES['images']['name'][$key];
                $fileType = $_FILES['images']['type'][$key];
                $fileSize = $_FILES['images']['size'][$key];
                
                // Validate file type
                if (!in_array($fileType, ['image/jpeg', 'image/png', 'image/gif'])) {
                    continue;
                }
                
                // Generate unique filename
                $newFileName = 'induction_' . $inductionId . '_' . uniqid() . '_' . $fileName;
                $filePath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($tmpName, $filePath)) {
                    $docStmt->bind_param("isssi",
                        $inductionId,
                        $fileName,
                        $filePath,
                        $fileType,
                        $fileSize
                    );
                    
                    if (!$docStmt->execute()) {
                        throw new Exception("Error inserting document record: " . $docStmt->error);
                    }
                }
            }
        }
        
        $docStmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Site induction form submitted successfully',
        'induction_id' => $inductionId
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn)) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Close connection
if (isset($conn)) {
    $conn->close();
}