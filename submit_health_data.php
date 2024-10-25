<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_config.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$date = $_POST['date'];
$blood_sugar = $_POST['blood_sugar'];
$blood_pressure = $_POST['blood_pressure'];
$medication_taken = ($_POST['medication_intake'] === 'Yes') ? 1 : 0; 
$sql = "INSERT INTO healthmetrics (user_id, date, blood_sugar, blood_pressure, medication_taken, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("isssi", $_SESSION['user_id'], $date, $blood_sugar, $blood_pressure, $medication_taken);
if ($stmt->execute()) {
    echo "<script>alert('Health data saved successfully.'); window.location.href='dashboard.php';</script>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
