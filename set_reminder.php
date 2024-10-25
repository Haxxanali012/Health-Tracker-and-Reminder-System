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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['set_reminder'])) {
    $reminder_type = $_POST['reminder_type'];
    $reminder_date = $_POST['reminder_date'];
    $reminder_time = $_POST['reminder_time'];
    $status = 'pending'; 
    $sql = "INSERT INTO reminders (user_id, reminder_type, reminder_time, reminder_date, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("issss", $_SESSION['user_id'], $reminder_type, $reminder_time, $reminder_date, $status);

    if ($stmt->execute()) {
        echo "<script>alert('Reminder set successfully.'); window.location.href='dashboard.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
if (isset($_GET['delete'])) {
    $reminder_id = $_GET['delete'];
    $sql = "DELETE FROM reminders WHERE reminder_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $reminder_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        echo "<script>alert('Reminder deleted successfully.'); window.location.href='set_reminder.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$sql = "SELECT * FROM reminders WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$reminders = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Reminder</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('https://images.unsplash.com/photo-1725992340772-47fd8f8df459?q=80&w=1374&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'); /* Set background image */
            background-size: cover; 
            background-repeat: no-repeat; 
            padding: 20px;
        }

        .container {
            width: 600px;
            margin: 0 auto;
        }
        .set-reminder-form {
            position: sticky;
            top: 0;
            background-color: rgba(255, 255, 255, 0.8); 
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
            border-radius: 10px;
            margin-bottom: 20px; 
        }

        .set-reminder-form h2 {
            text-align: center;
            color: #2c3e50; 
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #34495e; 
        }

        .form-group input,
        .form-group select {
            width: calc(100% - 20px); 
            padding: 10px;
            border: 1px solid #2980b9; 
            border-radius: 5px;
            outline: none;
            background-color: #e7f3e7; 
            transition: background-color 0.3s, border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            background-color: #c1e0c1; 
            border-color: #4CAF50; 
        }

        .form-group input[type="submit"] {
            width: 100%; 
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            border: none;
        }

        .form-group input[type="submit"]:hover {
            background-color: #45a049; 
        }

        .error {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }
        .reminders-section {
            max-height: 400px;
            overflow-y: auto;
            background-color: rgba(255, 255, 255, 0.8); 
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ccc; 
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2; 
        }

        .btn {
            background-color: #4CAF50; 
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn:hover {
            background-color: #45a049; 
        }

        .btn-delete {
            background-color: red; 
        }

        .btn-delete:hover {
            background-color: darkred; 
        }

        .icon {
            margin-right: 5px; 
        }
    </style>
    <script>
        function validateForm() {
            var selectedDate = new Date(document.getElementById("reminder_date").value);
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                document.getElementById("error-message").textContent = "Reminder date cannot be in the past.";
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<div class="container">

    <div class="set-reminder-form">
        <h2>Set Reminder</h2>
        <form action="set_reminder.php" method="post" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="reminder_type">Reminder Type:</label>
                <select id="reminder_type" name="reminder_type" required>
                    <option value="">Select here---</option>
                    <option value="Medication">Medication</option>
                    <option value="Appointment">Appointment</option>
                    <option value="Exercise">Exercise</option>
                </select>
            </div>

            <div class="form-group">
                <label for="reminder_date">Reminder Date:</label>
                <input type="date" id="reminder_date" name="reminder_date" required>
                <div class="error" id="error-message"></div> 
            </div>

            <div class="form-group">
                <label for="reminder_time">Reminder Time:</label>
                <input type="time" id="reminder_time" name="reminder_time" required>
            </div>

            <div class="form-group">
                <input type="submit" name="set_reminder" value="Set Reminder">
            </div>
        </form>
    </div>
    <div class="reminders-section">
        <h3>Your Reminders</h3>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reminders)) : ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No reminders set.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($reminders as $reminder): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reminder['reminder_type']); ?></td>
                            <td><?php echo htmlspecialchars($reminder['reminder_date']); ?></td>
                            <td><?php echo htmlspecialchars($reminder['reminder_time']); ?></td>
                            <td><?php echo htmlspecialchars($reminder['status']); ?></td>
                            <td>
                                <a href="update_reminder.php?id=<?php echo $reminder['reminder_id']; ?>" class="btn">
                                    <i class="fas fa-edit icon"></i> 
                                </a>
                                <a href="set_reminder.php?delete=<?php echo $reminder['reminder_id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this reminder?');">
                                    <i class="fas fa-trash icon"></i> 
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
