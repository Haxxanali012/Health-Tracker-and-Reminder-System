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

$user_id = $_SESSION['user_id'];
$sql_user = "SELECT username FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$stmt_user->bind_result($username);
$stmt_user->fetch();
$stmt_user->close();

$sql = "SELECT blood_sugar, blood_pressure, medication_taken, `condition`, created_at FROM healthmetrics WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$stmt->bind_result($blood_sugar, $blood_pressure, $medication_taken, $condition, $created_at);

$health_data = [];
while ($stmt->fetch()) {
    $medication_taken_display = $medication_taken == 1 ? 'Yes' : 'No';
    $health_data[] = [
        'blood_sugar' => $blood_sugar,
        'blood_pressure' => $blood_pressure,
        'medication_taken' => $medication_taken_display,
        'condition' => htmlspecialchars($condition), 
        'created_at' => $created_at,
    ];
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Data Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: white;
            padding: 20px;
            overflow: hidden;
            position: relative;
        }

        .gradient-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #1e3c72, #2a5298, #0f2027);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            z-index: -2;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .floating-shape {
            position: absolute;
            bottom: -150px;
            width: 30px;
            height: 30px;
            background-color: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            animation: floatUp 20s infinite ease-in-out;
            z-index: -1;
        }

        .floating-shape:nth-child(1) { left: 20%; animation-duration: 18s; width: 40px; height: 40px; }
        .floating-shape:nth-child(2) { left: 40%; animation-duration: 22s; width: 25px; height: 25px; }
        .floating-shape:nth-child(3) { left: 60%; animation-duration: 24s; width: 50px; height: 50px; }
        .floating-shape:nth-child(4) { left: 80%; animation-duration: 20s; width: 30px; height: 30px; }

        @keyframes floatUp {
            0% { transform: translateY(0); }
            100% { transform: translateY(-120vh); }
        }

        .container {
            width: 80%;
            margin: 0 auto;
            background-color: rgba(52, 73, 94, 0.9);
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            z-index: 1;
            position: relative;
        }

        .welcome-message {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th, .table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        .table th { background-color: #7f8c8d; }

        .button-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .button-container a {
            text-decoration: none;
            color: white;
            background-color: #4CAF50;
            padding: 10px 20px;
            border-radius: 25px;
            transition: transform 0.3s, box-shadow 0.3s, background-color 0.3s;
            text-align: center;
            width: 10%;
        }

        .button-container a:hover {
            background-color: #45a049;
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.7);
        }

        .colorful-text { display: inline-block; }
    </style>
</head>
<body>

<div class="gradient-background"></div>
<div class="floating-shape"></div>
<div class="floating-shape"></div>
<div class="floating-shape"></div>
<div class="floating-shape"></div>

<div class="container">
    <div class="welcome-message">
        Welcome, 
        <?php
        foreach (str_split(htmlspecialchars($username)) as $char) {
            $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
            echo "<span class='colorful-text' style='color: $color;'>$char</span>";
        }
        ?>!
    </div>
    

    <div class="button-container">
        <a href="health_data_form.php">Enter Data</a>
        <a href="view_insights.php">View Insights</a>
        <a href="set_reminder.php">Set Reminder</a>
        <a href="connect_device.php">Connect Device</a>
        <a href="logout.php">Logout</a> 
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Condition</th> 
                <th>Blood Sugar Level</th>
                <th>Blood Pressure</th>
                <th>Medication Taken</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($health_data as $data): ?>
                <tr>
                    <td><?php echo htmlspecialchars($data['condition']); ?></td>
                    <td><?php echo htmlspecialchars($data['blood_sugar']); ?></td>
                    <td><?php echo htmlspecialchars($data['blood_pressure']); ?></td>
                    <td><?php echo htmlspecialchars($data['medication_taken']); ?></td>
                    <td><?php echo htmlspecialchars($data['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
