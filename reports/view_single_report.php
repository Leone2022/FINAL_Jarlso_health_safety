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

// Get the checklist ID from the URL
$checklistId = $_GET['id'];

// Fetch the checklist details
$sql = "SELECT * FROM first_aid_kit_checklists WHERE id = '$checklistId'";
$checklistResult = $conn->query($sql);

// Fetch the items associated with this checklist
$sqlItems = "SELECT * FROM first_aid_kit_items WHERE checklist_id = '$checklistId'";
$itemsResult = $conn->query($sqlItems);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>First Aid Kit Checklist Report</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(to right, #1e3c72, #2a5298);
            color: #333;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: #fff;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            max-width: 1200px;
            margin: auto;
        }
        h1, h2, h3 {
            color: #2a5298;
            margin-bottom: 20px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        table, th, td {
            border: 2px solid #ddd;
        }
        th, td {
            padding: 15px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        button {
            background-color: #2a5298;
            color: #fff;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }
        button:hover {
            background-color: #1e3c72;
            transform: scale(1.05);
        }

        /* Animation for smooth transitions */
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        .container {
            animation: fadeIn 1s ease-in;
        }

        /* Media queries for print */
        @media print {
            body {
                background: none;
                color: black;
            }
            .container {
                max-width: none;
                box-shadow: none;
                padding: 0;
                margin: 0;
            }
            .buttons {
                display: none;
            }
            table, th, td {
                border: 1px solid black;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>First Aid Kit Checklist Report</h1>
        
        <?php
        if ($checklistResult->num_rows > 0) {
            $checklist = $checklistResult->fetch_assoc();
            echo "<h2>Team Leader: " . $checklist['confirm_name'] . "</h2>";
            echo "<h3>Date: " . $checklist['confirm_date'] . "</h3>";
        }
        ?>

        <h3>Checklist Items</h3>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($itemsResult->num_rows > 0) {
                    while ($item = $itemsResult->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $item['item'] . "</td>";
                        echo "<td>" . $item['description'] . "</td>";
                        echo "<td>" . $item['quantity'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No items found</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="buttons">
            <button onclick="window.print()">Print as PDF</button>
            <button onclick="savePDF()">Save as PDF</button>
            <button onclick="sendEmail()">Send via Email</button>
        </div>
    </div>

    <script>
        function savePDF() {
            window.print(); // Simulate PDF save through print function
        }

        function sendEmail() {
            const subject = "First Aid Kit Checklist Report";
            const body = "Please find attached the First Aid Kit Checklist Report.";
            const mailTo = "mailto:?subject=" + encodeURIComponent(subject) + "&body=" + encodeURIComponent(body);
            window.location.href = mailTo;
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
