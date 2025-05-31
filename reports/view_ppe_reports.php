<?php
// Database connection
$servername = "localhost";
$username = "root"; // Update your username
$password = ""; // Update your password
$database = "health_safety_db";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$searchQuery = "";
if (isset($_POST['search'])) {
    $searchQuery = $_POST['search'];
}

// Query to get all submissions or filter by team leader name
$sql = "SELECT id, siteID, teamLeaderName, date FROM ppe_register";
if (!empty($searchQuery)) {
    $sql .= " WHERE teamLeaderName LIKE '%$searchQuery%'";
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View PPE Reports</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 80%;
            margin: 0 auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        form {
            margin-top: 20px;
            text-align: center;
        }
        input[type="text"] {
            padding: 8px;
            width: 300px;
        }
        button {
            padding: 8px 16px;
            background-color: #2a5298;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #1e3c72;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>PPE Report Submissions</h1>
        <form method="POST">
            <input type="text" name="search" placeholder="Search by Team Leader's Name" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit">Search</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Site ID</th>
                    <th>Team Leader Name</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['siteID'] . "</td>";
                        echo "<td>" . $row['teamLeaderName'] . "</td>";
                        echo "<td>" . $row['date'] . "</td>";
                        echo "<td><a href='view_ppe_report_detail.php?id=" . $row['id'] . "'>View Details</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No submissions found</td></tr>";
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
