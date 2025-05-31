<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "health_safety_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search query
$searchQuery = "";
if (isset($_POST['search'])) {
    $searchQuery = $_POST['search'];
}

// Query the `site_induction` table based on the search criteria
$sql = "SELECT * FROM site_induction WHERE siteName LIKE '%$searchQuery%'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Site Induction Reports</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #2a5298;
            color: white;
        }
        .search-container {
            text-align: center;
            margin: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>View Site Induction Reports</h1>
        <div class="search-container">
            <form method="POST" action="">
                <input type="text" name="search" placeholder="Search by Site Name">
                <button type="submit">Search</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Induction ID</th>
                    <th>Site Name</th>
                    <th>Site Number</th>
                    <th>Date</th>
                    <th>View Report</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Use 'inductionId' instead of 'id'
                        echo "<tr>";
                        echo "<td>" . $row['inductionId'] . "</td>";
                        echo "<td>" . $row['siteName'] . "</td>";
                        echo "<td>" . $row['siteNumber'] . "</td>";
                        echo "<td>" . $row['inductionDate'] . "</td>";
                        echo "<td><a href='view_single_site_induction.php?inductionId=" . $row['inductionId'] . "'>View</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>
