<?php
// Database connection
$host = "localhost";
$dbname = "health_safety_db";
$username = "root"; // Use your MySQL username
$password = ""; // Use your MySQL password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];
    $inputRole = $_POST['role'];

    // Query the database for the user
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = :username AND role = :role");
    $stmt->bindParam(':username', $inputUsername);
    $stmt->bindParam(':role', $inputRole);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['password'] === $inputPassword) {
        // Login successful
        session_start();
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on role
        switch ($user['role']) {
            case 'CEO':
                header("Location:dashboard_ceo.php");
                break;
            case 'HOD':
                header("Location:dashboard_hod.php");
                break;
            case 'Health & Safety Manager':
                header("Location:dashboard_hsm.php");
                break;
            case 'Manager':
                header("Location:dashboard_manager.php");
                break;
            default:
                // Redirect to a generic page if the role is unknown
                header("Location: login_admin.php");
                break;
        }
        exit();
    } else {
        // Invalid credentials
        $error_message = "Invalid username, password, or role.";
        echo "<script>alert('$error_message'); window.location.href = 'login_admin.php';</script>";
    }
}
?>
