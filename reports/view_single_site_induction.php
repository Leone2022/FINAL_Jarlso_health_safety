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

// Check if 'inductionId' is set in the URL
if (isset($_GET['inductionId'])) {
    $inductionId = $_GET['inductionId'];

    // Fetch the site induction details
    $sql = "SELECT * FROM site_induction WHERE inductionId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $inductionId); // Use 'i' for integer
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $induction = $result->fetch_assoc();
    } else {
        echo "No records found for the specified induction.";
        exit();
    }

    // Fetch the associated visitor logs
    $sqlVisitors = "SELECT * FROM visitor_log WHERE inductionId = ?";
    $stmtVisitors = $conn->prepare($sqlVisitors);
    $stmtVisitors->bind_param("i", $inductionId); // Use 'i' for integer
    $stmtVisitors->execute();
    $visitorLogs = $stmtVisitors->get_result();
} else {
    echo "No inductionId provided in the URL.";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Site Induction Report</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(to right, #1e3c72, #2a5298);
            color: #333;
            font-family: "Poppins", sans-serif;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        h1, h2 {
            color: #1e3c72;
        }

        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
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
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .btn-print {
            background-color: #2a5298;
        }

        .btn-print:hover {
            background-color: #1e3c72;
        }

        .btn-back {
            background-color: #f44336;
        }

        .btn-back:hover {
            background-color: #c62828;
        }

        .back-arrow {
            font-size: 1.5rem;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Site Induction Details</h1>
        
        <table>
            <tr>
                <th>Site Name</th>
                <td><?php echo htmlspecialchars($induction['siteName']); ?></td>
            </tr>
            <tr>
                <th>Site Number</th>
                <td><?php echo htmlspecialchars($induction['siteNumber']); ?></td>
            </tr>
            <tr>
                <th>Induction Declaration</th>
                <td><?php echo nl2br(htmlspecialchars($induction['inductionDeclaration'])); ?></td>
            </tr>
            <tr>
                <th>Visitor Signature</th>
                <td><?php echo htmlspecialchars($induction['visitorSignature']); ?></td>
            </tr>
            <tr>
                <th>Induction Date</th>
                <td><?php echo htmlspecialchars($induction['inductionDate']); ?></td>
            </tr>
        </table>

        <h2>Visitor Logs</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Signature</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($visitorLogs->num_rows > 0) {
                    while ($visitor = $visitorLogs->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($visitor['date']) . "</td>";
                        echo "<td>" . htmlspecialchars($visitor['time']) . "</td>";
                        echo "<td>" . htmlspecialchars($visitor['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($visitor['company']) . "</td>";
                        echo "<td>" . htmlspecialchars($visitor['signature']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No visitor logs available</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="button-container">
            <button class="btn btn-back" onclick="history.back();">
                <span class="back-arrow">&larr;</span> Back
            </button>
            <button class="btn btn-print" onclick="window.print();">Print Report</button>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
