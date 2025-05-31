<?php
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "health_safety_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve all incident reports
$sql = "SELECT id, reported_by, date_reported, incident_no FROM incident_reports ORDER BY date_reported DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Incident Reports</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="forms.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        
        body {
            background: linear-gradient(to right, #1e3c72, #2a5298);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            background: #ffffff;
            width: 90%;
            max-width: 1200px;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            margin-bottom: 1.5rem;
            color: #1e3c72;
            text-align: center;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ccc;
        }
        
        .buttons {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        button {
            background-color: #2a5298;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
            margin-left: 10px;
        }

        button:hover {
            background-color: #1e3c72;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Incident Reports</h1>
        <table>
            <thead>
                <tr>
                    <th>Team Leader</th>
                    <th>Date Reported</th>
                    <th>Incident No</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['reported_by']}</td>
                                <td>{$row['date_reported']}</td>
                                <td>{$row['incident_no']}</td>
                                <td><a href='view_incident_report.php?id={$row['id']}'><button>View</button></a></td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No incident reports found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="buttons">
            <a href="all_reports.php"><button>Back to all Form</button></a>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
