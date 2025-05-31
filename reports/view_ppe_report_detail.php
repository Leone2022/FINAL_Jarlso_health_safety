<?php
// Include the database connection
$servername = "localhost";
$username = "root"; // Update with your actual username
$password = ""; // Update with your actual password
$database = "health_safety_db";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the submission ID is provided
if (isset($_GET['id'])) {
    $submission_id = $_GET['id'];

    // Retrieve the main PPE register details
    $sql = "SELECT * FROM ppe_register WHERE id = '$submission_id'";
    $result = $conn->query($sql);
    $submission = $result->fetch_assoc();

    // Retrieve the PPE checklist details
    $sql = "SELECT * FROM ppe_checklist WHERE ppe_register_id = '$submission_id'";
    $checklist_result = $conn->query($sql);

} else {
    echo "No submission ID provided.";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPE Report Details</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="forms.css">
    <style>
        /* Basic styling */
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
        h1, h2 {
            text-align: center;
            color: #1e3c72;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        .back-button, .print-button, .save-button, .send-button {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            background: #2a5298;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .back-button:hover, .print-button:hover, .save-button:hover, .send-button:hover {
            background: #1e3c72;
        }
    </style>
</head>
<body>
    <div class="container">
        <center>
            <h1>Personal Protective Equipment and Tools Register</h1>
        </center>

        <!-- PPE Register details -->
        <h2>Submission Details</h2>
        <div class="form-group">
            <label for="siteID">Site ID:</label>
            <input type="text" id="siteID" value="<?php echo $submission['siteID']; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="siteName">Site Name:</label>
            <input type="text" id="siteName" value="<?php echo $submission['siteName']; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="projectTitle">Project Title:</label>
            <input type="text" id="projectTitle" value="<?php echo $submission['projectTitle']; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="date">Date:</label>
            <input type="date" id="date" value="<?php echo $submission['date']; ?>" readonly>
        </div>

        <!-- PPE Checklist details -->
        <h2>Verification of Critical PPEs</h2>
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>PPEs</th>
                    <th>Yes</th>
                    <th>No</th>
                    <th>N/A</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter = 1;
                while ($checklist = $checklist_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $counter . "</td>";
                    echo "<td>" . ucfirst($checklist['ppe']) . "</td>";
                    echo "<td>" . ($checklist['status'] == 'Yes' ? '✔️' : '') . "</td>";
                    echo "<td>" . ($checklist['status'] == 'No' ? '❌' : '') . "</td>";
                    echo "<td>" . ($checklist['status'] == 'N/A' ? 'N/A' : '') . "</td>";
                    echo "</tr>";
                    $counter++;
                }
                ?>
            </tbody>
        </table>

        <!-- Team leader, HSE representative, and Project Manager details -->
        <div class="signature-section">
            <div>
                <label for="teamLeaderName">Team Leader's Name:</label>
                <input type="text" id="teamLeaderName" value="<?php echo $submission['teamLeaderName']; ?>" readonly>
                <label for="teamLeaderDate">Date:</label>
                <input type="date" id="teamLeaderDate" value="<?php echo $submission['teamLeaderDate']; ?>" readonly>
            </div>
            <div>
                <label for="hseName">HSE Representative's Name:</label>
                <input type="text" id="hseName" value="<?php echo $submission['hseName']; ?>" readonly>    
            </div>
            <div>
                <label for="projectManagerName">Project Manager's Name:</label>
                <input type="text" id="projectManagerName" value="<?php echo $submission['projectManagerName']; ?>" readonly>
            </div>
        </div>

        <!-- Buttons for Print, Save, Send, and Back -->
        <div class="form-group">
            <button class="back-button" onclick="window.history.back();">← Back</button>
            <button class="print-button" onclick="window.print();">🖨️ Print</button>
            <button class="save-button" onclick="saveReport();">💾 Save</button>
            <button class="send-button" onclick="sendReport();">✉️ Send</button>
        </div>
    </div>

    <script>
        function saveReport() {
            alert("Report saved successfully! (This function can be expanded to implement actual saving functionality.)");
        }

        function sendReport() {
            alert("Report sent successfully! (This function can be expanded to implement actual email functionality.)");
        }
    </script>
</body>
</html>
