<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process form data
    $blood_sugar = $_POST['blood_sugar'];
    $blood_pressure = $_POST['blood_pressure'];
    $medication_taken = $_POST['medication_intake'] == "1" ? 1 : 0; 
    $condition = $_POST['condition']; 

    $sql = "INSERT INTO healthmetrics (user_id, blood_sugar, blood_pressure, medication_taken, `condition`) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("SQL error: " . $conn->error); 
    }

    $stmt->bind_param("iidss", $_SESSION['user_id'], $blood_sugar, $blood_pressure, $medication_taken, $condition);

    if ($stmt->execute()) {
        echo "<script>alert('Health data entered successfully.'); window.location.href='dashboard.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Health Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e9f5ff; 
            overflow: hidden; 
        }

        .container {
            position: relative; 
            width: 50%;
            margin: 0 auto;
            background-color: rgba(255, 255, 255, 0.9); 
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            z-index: 10; 
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
        }
        .container:hover {
            transform: scale(1.02); 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); 
        }
        h2 {
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            box-sizing: border-box;
            border-radius: 4px;
            border: 1px solid #ccc;
            transition: border 0.3s ease, box-shadow 0.3s ease; 
        }
        .form-group input:focus, .form-group select:focus {
            border: 1px solid #4CAF50; 
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5); 
            outline: none; 
        }
        .form-group input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease; 
        }
        .form-group input[type="submit"]:hover {
            background-color: #45a049;
            transform: scale(1.05); 
        }
        #backgroundCanvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1; 
        }
    </style>
</head>
<body>

<canvas id="backgroundCanvas"></canvas> 

<div class="container">
    <h2>Enter Health Data</h2>
    <form action="" method="post">
        <div class="form-group">
            <label for="condition">Condition:</label>
            <select id="condition" name="condition" required>
                <option value="">Select a condition</option>
                <option value="Diabetes">Diabetes</option>
                <option value="Hypertension">Hypertension</option>
                <option value="Asthma">Asthma</option>
            </select>
        </div>
        <div class="form-group">
            <label for="blood_sugar">Blood Sugar Level:</label>
            <input type="number" id="blood_sugar" name="blood_sugar" min="0" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="blood_pressure">Blood Pressure:</label>
            <input type="number" id="blood_pressure" name="blood_pressure" min="0" required>
        </div>
        <div class="form-group">
            <label for="medication_intake">Medication Intake:</label>
            <select id="medication_intake" name="medication_intake" required>
                <option value="">Select an option</option>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>
        <div class="form-group">
            <input type="submit" value="Submit">
        </div>
    </form>
</div>

<script>
    const canvas = document.getElementById('backgroundCanvas');
    const ctx = canvas.getContext('2d');

    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    const balls = [];
    const numBalls = 50; 

    for (let i = 0; i < numBalls; i++) {
        balls.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            radius: Math.random() * 10 + 2, 
            color: 'hsl(' + Math.random() * 360 + ', 100%, 50%)', 
            dx: (Math.random() - 0.5) * 2, 
            dy: (Math.random() - 0.5) * 2 
        });
    }

    function drawBall(ball) {
        ctx.beginPath();
        ctx.arc(ball.x, ball.y, ball.radius, 0, Math.PI * 2);
        ctx.fillStyle = ball.color;
        ctx.fill();
        ctx.closePath();
    }

    function updateBalls() {
        ctx.clearRect(0, 0, canvas.width, canvas.height); 

        for (let ball of balls) {
            ball.x += ball.dx;
            ball.y += ball.dy;
            if (ball.x + ball.radius > canvas.width || ball.x - ball.radius < 0) {
                ball.dx *= -1; 
            }
            if (ball.y + ball.radius > canvas.height || ball.y - ball.radius < 0) {
                ball.dy *= -1; 
            }

            drawBall(ball); 
        }
        for (let i = 0; i < balls.length; i++) {
            for (let j = i + 1; j < balls.length; j++) {
                const distance = Math.hypot(balls[i].x - balls[j].x, balls[i].y - balls[j].y);
                if (distance < 100) { 
                    ctx.strokeStyle = 'rgba(0, 0, 0, 0.2)'; 
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.moveTo(balls[i].x, balls[i].y);
                    ctx.lineTo(balls[j].x, balls[j].y);
                    ctx.stroke();
                    ctx.closePath();
                }
            }
        }

        requestAnimationFrame(updateBalls); 
    }

    updateBalls(); 
</script>

</body>
</html>
