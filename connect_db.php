<?php
$servername = "localhost";
$username = "root"; // Update if necessary
$password = ""; // Update if necessary
$database = "health_safety_db";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
