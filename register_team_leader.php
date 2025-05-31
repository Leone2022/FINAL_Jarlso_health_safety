<?php
// Database connection
$host = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is blank
$database = "health_safety_db";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $conn->real_escape_string($_POST['fName']);
    $lastName = $conn->real_escape_string($_POST['lName']);
    $email = $conn->real_escape_string($_POST['email']);
    $siteName = $conn->real_escape_string($_POST['siteName']);
    $siteNumber = $conn->real_escape_string($_POST['siteNumber']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password for security

    // Insert data into the database
    $sql = "INSERT INTO team_leaders (first_name, last_name, email, site_name, site_number, password) 
            VALUES ('$firstName', '$lastName', '$email', '$siteName', '$siteNumber', '$password')";

    if ($conn->query($sql) === TRUE) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
