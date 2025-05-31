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

// Query the first_aid_kit_checklists table based on the search criteria
$sql = "SELECT * FROM first_aid_kit_checklists 
        WHERE checklist_no LIKE '%$searchQuery%' 
        OR team_leader LIKE '%$searchQuery%' 
        OR confirmed_by LIKE '%$searchQuery%'
        OR location LIKE '%$searchQuery%'
        ORDER BY confirmation_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View First Aid Kit Checklists</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: linear-gradient(to right, #c62828, #e53935);
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
            color: #c62828;
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
            background-color: #c62828;
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
            background: #c62828;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-container button:hover {
            background: #b71c1c;
        }

        .no-records {
            color: #ff0000;
            font-weight: bold;
        }

        .view-btn {
            display: inline-block;
            padding: 5px 10px;
            background: #c62828;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: background 0.3s;
        }

        .view-btn:hover {
            background: #b71c1c;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #c62828;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: #b71c1c;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .badge-success {
            background-color: #4CAF50;
            color: white;
        }

        .badge-warning {
            background-color: #FFC107;
            color: black;
        }

        .badge-danger {
            background-color: #F44336;
            color: white;
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
        <h1>First Aid Kit Checklists</h1>
        <div class="search-container">
            <form method="POST" action="">
                <input type="text" name="search" placeholder="Search by Checklist No, Team Leader, or Location" value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Checklist No</th>
                    <th>Team Leader</th>
                    <th>Location</th>
                    <th>Confirmation Date</th>
                    <th>Status</th>
                    <th>View Checklist</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['checklist_no']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['team_leader']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['confirmation_date']) . "</td>";
                        
                        // Status badge
                        $statusClass = "";
                        switch(strtolower($row['status'])) {
                            case 'completed':
                                $statusClass = "badge-success";
                                break;
                            case 'pending':
                                $statusClass = "badge-warning";
                                break;
                            case 'overdue':
                                $statusClass = "badge-danger";
                                break;
                            default:
                                $statusClass = "";
                        }
                        
                        echo "<td><span class='badge " . $statusClass . "'>" . htmlspecialchars($row['status']) . "</span></td>";
                        echo "<td><a href='view_first_aid_kit_checklist.php?id=" . $row['id'] . "' class='view-btn'>View</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='no-records'>No first aid kit checklists found</td></tr>";
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