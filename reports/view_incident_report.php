<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "health_safety_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the report ID from the URL
$reportId = $_GET['id'];

// Retrieve the report details
$sql = "SELECT * FROM incident_reports WHERE id = $reportId";
$result = $conn->query($sql);
$report = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Report Details</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="forms.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        /* Include the provided CSS directly for simplicity */
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
        
        td {
            padding: 10px;
            vertical-align: top;
        }
        
        .buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
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
        }

        button:hover {
            background-color: #1e3c72;
        }

        .back-arrow {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            text-decoration: none;
            font-size: 18px;
            color: #2a5298;
        }

        .back-arrow:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container" id="reportContent"> <!-- Added ID for content capturing -->
        <h1>Incident Report Details</h1>
        <table border="1">
            <tbody>
                <tr>
                    <td>Team Leader:</td>
                    <td><?php echo $report['reported_by']; ?></td>
                </tr>
                <tr>
                    <td>Date Reported:</td>
                    <td><?php echo $report['date_reported']; ?></td>
                </tr>
                <tr>
                    <td>Incident No:</td>
                    <td><?php echo $report['incident_no']; ?></td>
                </tr>
                <tr>
                    <td>Reported by Client:</td>
                    <td><?php echo $report['reported_by_client']; ?></td>
                </tr>
                <tr>
                    <td>Client Name:</td>
                    <td><?php echo $report['client_name']; ?></td>
                </tr>
                <tr>
                    <td>Affected Project:</td>
                    <td><?php echo $report['affected_project']; ?></td>
                </tr>
                <tr>
                    <td>Project Name:</td>
                    <td><?php echo $report['project_name']; ?></td>
                </tr>
                <tr>
                    <td>HSE Aspects:</td>
                    <td><?php echo $report['hse_aspects']; ?></td>
                </tr>
                <tr>
                    <td>Incident Description:</td>
                    <td><?php echo $report['incident_description']; ?></td>
                </tr>
                <tr>
                    <td>Corrective Actions:</td>
                    <td><?php echo $report['corrective_actions']; ?></td>
                </tr>
                <tr>
                    <td>Root Cause Analysis:</td>
                    <td><?php echo $report['root_cause_analysis']; ?></td>
                </tr>
                <tr>
                    <td>Carried Out By:</td>
                    <td><?php echo $report['carried_out_by']; ?></td>
                </tr>
                <tr>
                    <td>Date Carried Out:</td>
                    <td><?php echo $report['date_carried_out']; ?></td>
                </tr>
                <tr>
                    <td>Verification Comments:</td>
                    <td><?php echo $report['verification_comments']; ?></td>
                </tr>
                <tr>
                    <td>Preventive Actions:</td>
                    <td><?php echo $report['preventive_actions']; ?></td>
                </tr>
                <tr>
                    <td>Verification Comments (Preventive):</td>
                    <td><?php echo $report['verification_comments_preventive']; ?></td>
                </tr>
                <tr>
                    <td>Report Name:</td>
                    <td><?php echo $report['report_name']; ?></td>
                </tr>
                <tr>
                    <td>Report Date:</td>
                    <td><?php echo $report['report_date']; ?></td>
                </tr>
            </tbody>
        </table>
        <center>
        <div class="buttons">
        <a href="view_incident_reports.php" class="back-arrow">&larr; Back to Reports List</a>
            <button onclick="printReport()">Print</button>
           
        </center>
        </div>
        <br>
       
    </div>

    <script>
        function printReport() {
            window.print(); // Print the current page
        }

        function saveAsPDF() {
            const { jsPDF } = window.jspdf; // Get jsPDF from the global object
            const pdf = new jsPDF();
            const reportContent = document.getElementById('reportContent').innerHTML; // Capture the content to save
            
            pdf.html(reportContent, {
                callback: function (pdf) {
                    pdf.save('incident_report.pdf'); // Save the generated PDF
                },
                x: 10,
                y: 10
            });
        }

        function sendEmail() {
            // This is a placeholder for the email functionality
            const reportContent = document.getElementById('reportContent').innerHTML;
            const subject = 'Incident Report';
            const emailBody = encodeURIComponent(reportContent);
            const mailtoLink = `mailto:?subject=${subject}&body=${emailBody}`;
            window.location.href = mailtoLink; // Open default mail client
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>
