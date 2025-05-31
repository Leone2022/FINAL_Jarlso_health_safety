<?php
// Database connection parameters
$host = "localhost";
$username = "root";  // FIXED: Changed from $user to $username to match connection code
$password = "";
$database = "health_safety_db";

// Create response array
$response = array(
    'success' => false,
    'message' => ''
);

try {
    // Connect to database
    $conn = new mysqli($host, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Get form data
    $siteID = $_POST['siteID'];
    $siteName = $_POST['siteName'];
    $meetingDate = $_POST['date'];
    $activityDetails = $_POST['activityDetails'];
    $toolboxDetails = $_POST['toolboxDetails'];
    
    // PPE verification data
    $safetyBoot = $_POST['safetyBoot'];
    $helmet = $_POST['helmet'];
    $reflectiveJacket = $_POST['reflectiveJacket'];
    $fullBodyHarness = $_POST['fullBodyHarness'];
    $rescueKits = $_POST['rescueKits'];
    $handGloves = $_POST['handGloves'];
    $noseMask = $_POST['noseMask'];
    $earPlugs = $_POST['earPlugs'];
    $otherPPEs = $_POST['otherPPEs'];
    $riggersCertified = $_POST['riggersCertified'];
    $firstAidersCertified = $_POST['firstAidersCertified'];
    $firstAidKits = $_POST['firstAidKits'];
    
    // Declaration data
    $declarationName = $_POST['declarationName'];
    $declarationSignature = $_POST['declarationSignature'];
    $declarationDate = $_POST['declarationDate'];

    // Start a transaction
    $conn->begin_transaction();

    // Insert into main toolbox_meetings table
    $stmt = $conn->prepare("INSERT INTO toolbox_meetings (
        site_id, site_name, meeting_date, activity_details, toolbox_details,
        safety_boot, helmet, reflective_jacket, full_body_harness, rescue_kits,
        hand_gloves, nose_mask, ear_plugs, other_ppes, riggers_certified,
        first_aiders_certified, first_aid_kits, declaration_name, declaration_signature,
        declaration_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // FIXED: Removed created_at from SQL as it's automatically set by DEFAULT CURRENT_TIMESTAMP

    $stmt->bind_param(
        "ssssssssssssssssssss",
        $siteID, $siteName, $meetingDate, $activityDetails, $toolboxDetails,
        $safetyBoot, $helmet, $reflectiveJacket, $fullBodyHarness, $rescueKits,
        $handGloves, $noseMask, $earPlugs, $otherPPEs, $riggersCertified,
        $firstAidersCertified, $firstAidKits, $declarationName, $declarationSignature,
        $declarationDate
    );

    if (!$stmt->execute()) {
        throw new Exception("Error saving toolbox meeting data: " . $stmt->error);
    }

    // Get the ID of the inserted meeting
    $meetingID = $conn->insert_id;
    
    // Process attendees
    $attendeeCount = 0;
    
    // Count number of attendees by looking for name fields
    foreach ($_POST as $key => $value) {
        if (preg_match('/^name(\d+)$/', $key, $matches)) {
            $index = $matches[1];
            if (!empty($_POST["name{$index}"]) && !empty($_POST["role{$index}"]) && 
                !empty($_POST["contact{$index}"]) && !empty($_POST["signature{$index}"])) {
                $attendeeCount++;
            }
        }
    }
    
    // Insert attendees
    for ($i = 1; $i <= $attendeeCount; $i++) {
        if (isset($_POST["name{$i}"]) && !empty($_POST["name{$i}"])) {
            $name = $_POST["name{$i}"];
            $role = $_POST["role{$i}"];
            $contact = $_POST["contact{$i}"];
            $signature = $_POST["signature{$i}"];
            
            $stmt = $conn->prepare("INSERT INTO toolbox_attendees (
                meeting_id, name, role, contact, signature
            ) VALUES (?, ?, ?, ?, ?)");
            
            $stmt->bind_param("issss", $meetingID, $name, $role, $contact, $signature);
            
            if (!$stmt->execute()) {
                throw new Exception("Error saving attendee data: " . $stmt->error);
            }
        }
    }
    
    // Process uploaded images
    $uploadDir = 'uploads/toolbox/';
    
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Handle file uploads if present
    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        $fileCount = count($_FILES['images']['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['images']['tmp_name'][$i];
                $fileName = $meetingID . '_' . time() . '_' . basename($_FILES['images']['name'][$i]);
                $filePath = $uploadDir . $fileName;
                
                // Move uploaded file
                if (move_uploaded_file($tmpName, $filePath)) {
                    // Save file reference in database
                    $stmt = $conn->prepare("INSERT INTO toolbox_images (
                        meeting_id, image_path
                    ) VALUES (?, ?)");
                    
                    $stmt->bind_param("is", $meetingID, $filePath);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Error saving image data: " . $stmt->error);
                    }
                } else {
                    throw new Exception("Failed to upload image: " . $_FILES['images']['name'][$i]);
                }
            } else if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                throw new Exception("File upload error: " . $_FILES['images']['error'][$i]);
            }
        }
    }
    
    // Commit the transaction
    $conn->commit();
    
    // Set success response
    $response['success'] = true;
    $response['message'] = "Toolbox meeting form submitted successfully!";
    
} catch (Exception $e) {
    // If any error occurs, roll back the transaction
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    
    // Set error response
    $response['success'] = false;
    $response['message'] = "Error: " . $e->getMessage();
    
    // Log error
    error_log("Toolbox Form Error: " . $e->getMessage());
} finally {
    // Close the database connection
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}