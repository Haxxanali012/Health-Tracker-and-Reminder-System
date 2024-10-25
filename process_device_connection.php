<?php
session_start();

include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $device_id = $_POST['device_id'];
    header("Location: dashboard.php");
    exit();
}
?>
