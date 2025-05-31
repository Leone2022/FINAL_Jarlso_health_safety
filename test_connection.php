<?php
// Enable error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Test</h1>";

// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "health_safety_db";

echo "<h2>Connection Parameters</h2>";
echo "Host: $host <br>";
echo "Username: $username <br>";
echo "Password: " . ($password ? "[SET]" : "[EMPTY]") . "<br>";
echo "Database: $database <br>";

// Test database connection
echo "<h2>Connection Test</h2>";
try {
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        echo "<div style='color: red;'>Connection failed: " . $conn->connect_error . "</div>";
    } else {
        echo "<div style='color: green;'>Connection successful!</div>";
        
        // Check tables
        echo "<h2>Table Check</h2>";
        $tables = ["toolbox_meetings", "toolbox_attendees", "toolbox_images"];
        
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows > 0) {
                echo "<div style='color: green;'>Table '$table' exists.</div>";
                
                // Show table structure
                $structure = $conn->query("DESCRIBE $table");
                if ($structure) {
                    echo "<h3>Structure of '$table'</h3>";
                    echo "<table border='1' cellpadding='5'>";
                    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
                    
                    while ($row = $structure->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['Field'] . "</td>";
                        echo "<td>" . $row['Type'] . "</td>";
                        echo "<td>" . $row['Null'] . "</td>";
                        echo "<td>" . $row['Key'] . "</td>";
                        echo "<td>" . $row['Default'] . "</td>";
                        echo "<td>" . $row['Extra'] . "</td>";
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                }
            } else {
                echo "<div style='color: red;'>Table '$table' does not exist!</div>";
            }
        }
        
        // Check upload directory
        echo "<h2>Upload Directory Check</h2>";
        $uploadDir = 'uploads/toolbox/';
        
        if (file_exists($uploadDir)) {
            echo "<div style='color: green;'>Upload directory exists.</div>";
            
            // Check if writable
            if (is_writable($uploadDir)) {
                echo "<div style='color: green;'>Upload directory is writable.</div>";
            } else {
                echo "<div style='color: red;'>Upload directory is not writable!</div>";
            }
        } else {
            echo "<div style='color: orange;'>Upload directory does not exist.</div>";
            
            // Try to create it
            if (mkdir($uploadDir, 0777, true)) {
                echo "<div style='color: green;'>Successfully created upload directory.</div>";
            } else {
                echo "<div style='color: red;'>Failed to create upload directory!</div>";
            }
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>Exception: " . $e->getMessage() . "</div>";
}

echo "<h2>PHP Information</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . " seconds<br>";

echo "<h2>Test Form</h2>";
?>

<form action="submit_toolbox.php" method="post" enctype="multipart/form-data">
    <input type="text" name="test_field" value="This is a test">
    <input type="submit" value="Test Submit">
</form>