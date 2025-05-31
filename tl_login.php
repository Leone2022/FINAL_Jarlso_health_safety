<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "health_safety_db";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Check credentials
    $sql = "SELECT * FROM team_leaders WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Start the session
            session_start();
            
            // Set session variables
            $_SESSION['email'] = $row['email'];
            $_SESSION['first_name'] = $row['first_name'];
            $_SESSION['last_name'] = $row['last_name'];

            // Redirect to the dashboard
            header("Location: teamleader_dashboard.php");
            exit();
        } else {
            echo "<script>alert('Invalid password.'); window.location.href = 'login_teamleader.php';</script>";
        }
    } else {
        echo "<script>alert('No account found with that email.'); window.location.href = 'login_teamleader.php';</script>";
    }
}
?>
