<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login_admin.php");
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

// Query the ppe_register table based on the search criteria
$sql = "SELECT * FROM ppe_register 
        WHERE siteID LIKE '%$searchQuery%' 
        OR siteName LIKE '%$searchQuery%' 
        OR projectTitle LIKE '%$searchQuery%'
        OR teamLeaderName LIKE '%$searchQuery%'
        ORDER BY date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View PPE Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: linear-gradient(to right, #ed6c02, #ff9800);
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            position: relative;
        }
        
        body::before {
            content: "";
            background: url('images/jarlso.png') no-repeat center center;
            opacity: 0.1;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
            background-size: 30%;
            pointer-events: none;
        }

        .container {
            background: #ffffff;
            width: 100%;
            max-width: 1000px;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            color: #ed6c02;
            margin-bottom: 1.5rem;
        }

        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #ed6c02;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e9e9e9;
        }

        .search-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .search-container input[type="text"] {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #bdbdbd;
            width: 70%;
        }

        .search-container button {
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            background: #ed6c02;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-container button:hover {
            background: #e65100;
        }

        .no-records {
            color: #ff0000;
            font-weight: bold;
        }

        .view-btn {
            display: inline-block;
            padding: 5px 10px;
            background: #ed6c02;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: background 0.3s;
        }

        .view-btn:hover {
            background: #e65100;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #ed6c02;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: #e65100;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .status-dot {
            height: 12px;
            width: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .status-green {
            background-color: #4caf50;
        }

        .status-red {
            background-color: #f44336;
        }

        .status-gray {
            background-color: #9e9e9e;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .search-container input[type="text"] {
                width: 60%;
            }
            
            table {
                font-size: 0.9rem;
            }
            
            th, td {
                padding: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="images/jarlso.png" alt="Jarlso Logo" style="max-width: 200px; height: auto;">
        </div>
        <h1>PPE Register Reports</h1>
        <div class="search-container">
            <form method="POST" action="">
                <input type="text" name="search" placeholder="Search by Site ID, Name, Project, or Team Leader" value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Site ID</th>
                        <th>Site Name</th>
                        <th>Project Title</th>
                        <th>Date</th>
                        <th>Team Leader</th>
                        <th>PPE Status</th>
                        <th>View Report</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Determine overall PPE status
                            $ppeStatuses = [
                                $row['safetyBoot'], $row['helmet'], $row['reflectiveJacket'],
                                $row['fullBodyHarness'], $row['handGloves'], $row['noseMask'], 
                                $row['firstAidKits']
                            ];
                            
                            $missingCount = 0;
                            foreach ($ppeStatuses as $status) {
                                if ($status == 'no') {
                                    $missingCount++;
                                }
                            }
                            
                            if ($missingCount == 0) {
                                $statusClass = 'status-green';
                                $statusText = 'Complete';
                            } elseif ($missingCount <= 2) {
                                $statusClass = 'status-gray';
                                $statusText = 'Partial';
                            } else {
                                $statusClass = 'status-red';
                                $statusText = 'Incomplete';
                            }
                            
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['siteID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['siteName']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['projectTitle']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['teamLeaderName']) . "</td>";
                            echo "<td><span class='status-dot {$statusClass}'></span>{$statusText}</td>";
                            echo "<td><a href='view_ppe_report_detail.php?id=" . $row['id'] . "' class='view-btn'>View</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='no-records'>No PPE reports found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <a href="all_reports.php" class="back-btn">Back to All Reports</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>