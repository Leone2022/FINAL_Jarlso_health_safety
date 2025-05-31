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

// Query the waste_management table based on the search criteria
$sql = "SELECT * FROM waste_management 
        WHERE site_location LIKE '%$searchQuery%' 
        OR team_lead LIKE '%$searchQuery%' 
        OR confirmed_by LIKE '%$searchQuery%'
        ORDER BY collection_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Waste Management Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: linear-gradient(to right, #2e7d32, #4caf50);
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
            color: #2e7d32;
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
            background-color: #2e7d32;
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
            background: #2e7d32;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-container button:hover {
            background: #1b5e20;
        }

        .no-records {
            color: #ff0000;
            font-weight: bold;
        }

        .view-btn {
            display: inline-block;
            padding: 5px 10px;
            background: #2e7d32;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: background 0.3s;
        }

        .view-btn:hover {
            background: #1b5e20;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #2e7d32;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: #1b5e20;
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
        <h1>Waste Management Reports</h1>
        <div class="search-container">
            <form method="POST" action="">
                <input type="text" name="search" placeholder="Search by Site Location or Team Leader" value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Site Location</th>
                    <th>Collection Date</th>
                    <th>Team Leader</th>
                    <th>Team Size</th>
                    <th>Confirmed By</th>
                    <th>View Report</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['site_location']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['collection_date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['team_lead']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['team_size']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['confirmed_by']) . "</td>";
                        echo "<td><a href='view_waste_management.php?id=" . $row['id'] . "' class='view-btn'>View</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='no-records'>No waste management reports found</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <a href="all_reports.php" class="back-btn">Back to All Reports</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>