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

// Get the report ID from the query string
$id = $_GET['id'];

// Query to retrieve the specific toolbox meeting report
$sql = "SELECT * FROM toolbox_meeting WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$report = $result->fetch_assoc();

if (!$report) {
    die("Report not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toolbox Meeting Report</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: linear-gradient(to right, #1e3c72, #2a5298);
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background: #ffffff;
            width: 100%;
            max-width: 800px;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            color: #1e3c72;
            margin-bottom: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: white;
            color: black;
        }

        .button-container {
            margin-top: 20px;
        }

        .button {
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            background: #093561;
            color: white;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }

        .button:hover {
            background: #1e3c72;
        }

        @media print {
            .button-container {
                display: none; /* Hide buttons when printing */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Toolbox Meeting Report</h1>
        <table>
            <tr>
                <th>ID</th>
                <td><?php echo htmlspecialchars($report['id']); ?></td>
            </tr>
            <tr>
                <th>Site Name</th>
                <td><?php echo htmlspecialchars($report['site_name']); ?></td>
            </tr>
            <tr>
                <th>Date</th>
                <td><?php echo htmlspecialchars($report['date']); ?></td>
            </tr>
            <tr>
                <th>Activity Details</th>
                <td><?php echo nl2br(htmlspecialchars($report['activity_details'])); ?></td>
            </tr>
            <tr>
                <th>Declaration</th>
                <td><?php echo nl2br(htmlspecialchars($report['declaration'])); ?></td>
            </tr>
            <tr>
                <th>Signature</th>
                <td><?php echo htmlspecialchars($report['declaration_signature']); ?></td>
            </tr>
            <tr>
                <th>Declaration Date</th>
                <td><?php echo htmlspecialchars($report['declaration_date']); ?></td>
            </tr>
        </table>

        <div class="button-container">
            <a href="view_all_toolbox_reports.php" class="button">Back to Reports</a>
            <button class="button" onclick="window.print()">Print Report</button>
        </div>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
