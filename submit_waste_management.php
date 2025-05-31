<?php
// Set headers for JSON response
header('Content-Type: application/json');

// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed']);
    exit;
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection settings - using the main health_safety_db
$dbHost = 'localhost';
$dbName = 'health_safety_db';
$dbUser = 'root';  // Update with your actual username if different
$dbPass = '';      // Update with your actual password if different

try {
    // Connect to database
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Create folder for uploads
    $baseUploadDir = 'uploads/waste_management/' . date('Y-m-d_H-i-s') . '_' . uniqid();
    if (!file_exists($baseUploadDir)) {
        if (!mkdir($baseUploadDir, 0777, true)) {
            throw new Exception("Failed to create upload directory");
        }
    }
    
    // Insert main report data
    $stmt = $conn->prepare("
        INSERT INTO waste_management (
            site_location, 
            collection_date, 
            team_lead, 
            team_size, 
            confirmed_by, 
            confirmed_date,
            submission_time, 
            ip_address
        ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $stmt->bind_param("sssssss",
        $_POST['siteLocation'],
        $_POST['collectionDate'],
        $_POST['teamLead'],
        $_POST['teamSize'],
        $_POST['confirmName'],
        $_POST['confirmDate'],
        $_SERVER['REMOTE_ADDR']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert waste management report: " . $stmt->error);
    }
    
    $reportId = $conn->insert_id;
    $stmt->close();
    
    // Insert waste items
    $index = 1;
    $wasteItemCount = 0;
    
    while (isset($_POST['wasteType' . $index])) {
        if (!empty($_POST['wasteType' . $index])) {
            $stmt = $conn->prepare("
                INSERT INTO waste_management_items (
                    report_id,
                    waste_type,
                    quantity,
                    disposal_method
                ) VALUES (?, ?, ?, ?)
            ");
            
            if (!$stmt) {
                throw new Exception("Prepare statement failed for waste items: " . $conn->error);
            }
            
            $quantity = $_POST['quantity' . $index];
            
            $stmt->bind_param("isds",
                $reportId,
                $_POST['wasteType' . $index],
                $quantity,
                $_POST['disposalMethod' . $index]
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert waste item: " . $stmt->error);
            }
            
            $wasteItemCount++;
            $stmt->close();
        }
        
        $index++;
    }
    
    // Process and save images
    $photoCategories = ['before', 'after', 'collection', 'disposal'];
    $totalImages = 0;
    
    foreach ($photoCategories as $category) {
        $categoryDir = $baseUploadDir . '/' . $category;
        if (!file_exists($categoryDir)) {
            mkdir($categoryDir, 0777, true);
        }
        
        // Check if files are uploaded for this category
        if (isset($_FILES[$category . 'Images']) && is_array($_FILES[$category . 'Images']['name'])) {
            $files = $_FILES[$category . 'Images'];
            $fileCount = count($files['name']);
            
            for ($i = 0; $i < $fileCount; $i++) {
                // Skip if there was an upload error
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }
                
                // Validate file type (only allow images)
                $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($fileInfo, $files['tmp_name'][$i]);
                finfo_close($fileInfo);
                
                if (!preg_match('/^image\//', $mimeType)) {
                    continue; // Skip non-image files
                }
                
                // Generate safe filename
                $originalName = $files['name'][$i];
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $safeFilename = $category . '_' . date('Ymd') . '_' . uniqid() . '.' . $extension;
                $destination = $categoryDir . '/' . $safeFilename;
                
                // Move uploaded file
                if (move_uploaded_file($files['tmp_name'][$i], $destination)) {
                    $stmt = $conn->prepare("
                        INSERT INTO waste_management_images (
                            report_id,
                            category,
                            original_filename,
                            saved_filename,
                            file_path,
                            file_size
                        ) VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    
                    if (!$stmt) {
                        throw new Exception("Prepare statement failed for images: " . $conn->error);
                    }
                    
                    $fileSize = $files['size'][$i];
                    
                    $stmt->bind_param("issssi",
                        $reportId,
                        $category,
                        $originalName,
                        $safeFilename,
                        $destination,
                        $fileSize
                    );
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to insert image record: " . $stmt->error);
                    }
                    
                    $totalImages++;
                    $stmt->close();
                }
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Waste management report submitted successfully!',
        'report_id' => $reportId,
        'waste_items' => $wasteItemCount,
        'image_count' => $totalImages
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    
    // Send error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    
    // Log error
    error_log('Error in waste_management submission: ' . $e->getMessage());
} finally {
    // Close connection
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
}