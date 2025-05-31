<?php
include('connect_db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $site_name = $_POST["site_name"];
    $site_number = $_POST["site_number"];

    $sql = "UPDATE team_leaders SET first_name = ?, last_name = ?, email = ?, site_name = ?, site_number = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $first_name, $last_name, $email, $site_name, $site_number, $id);

    if ($stmt->execute()) {
        header("Location: view_team_leaders.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>
