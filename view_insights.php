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
$sql = "SELECT blood_sugar, blood_pressure, medication_taken, `condition` FROM healthmetrics WHERE user_id = ? ORDER BY `condition` ASC";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("SQL prepare error: " . $conn->error); 
}

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($blood_sugar, $blood_pressure, $medication_taken, $condition);
$total_blood_sugar_taken = 0;
$total_blood_pressure_taken = 0;
$count_taken = 0;

$total_blood_sugar_not_taken = 0;
$total_blood_pressure_not_taken = 0;
$count_not_taken = 0;

$conditions = [];
$blood_sugar_data = [];
$blood_pressure_data = [];
$medication_taken_blood_sugar = ['taken' => 0, 'not_taken' => 0];
$medication_taken_blood_pressure = ['taken' => 0, 'not_taken' => 0];
$blood_sugar_taken = [];
$blood_pressure_taken = [];
$blood_sugar_not_taken = [];
$blood_pressure_not_taken = [];

while ($stmt->fetch()) {
    $conditions[] = $condition;
    $blood_sugar_data[] = $blood_sugar;
    $blood_pressure_data[] = $blood_pressure;
    if ($medication_taken == 1) {
        if ($blood_sugar > 0) {
            $medication_taken_blood_sugar['taken']++;
            $total_blood_sugar_taken += $blood_sugar;
            $blood_sugar_taken[] = $blood_sugar;  
        }
        if ($blood_pressure > 0) {
            $medication_taken_blood_pressure['taken']++;
            $total_blood_pressure_taken += $blood_pressure;
            $blood_pressure_taken[] = $blood_pressure;  
        }
        $count_taken++;
    } else {
        if ($blood_sugar > 0) {
            $medication_taken_blood_sugar['not_taken']++;
            $total_blood_sugar_not_taken += $blood_sugar;
            $blood_sugar_not_taken[] = $blood_sugar;  
        }
        if ($blood_pressure > 0) {
            $medication_taken_blood_pressure['not_taken']++;
            $total_blood_pressure_not_taken += $blood_pressure;
            $blood_pressure_not_taken[] = $blood_pressure;  
        }
        $count_not_taken++;
    }
}

$stmt->close();
$conn->close();
$avg_blood_sugar_taken = $count_taken > 0 ? $total_blood_sugar_taken / $count_taken : 0;
$avg_blood_pressure_taken = $count_taken > 0 ? $total_blood_pressure_taken / $count_taken : 0;

$avg_blood_sugar_not_taken = $count_not_taken > 0 ? $total_blood_sugar_not_taken / $count_not_taken : 0;
$avg_blood_pressure_not_taken = $count_not_taken > 0 ? $total_blood_pressure_not_taken / $count_not_taken : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Data Insights</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298, #0f2027);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite; 
            padding: 20px;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            width: 80%;
            margin: 0 auto;
            background-color: rgba(255, 255, 255, 0.9); 
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            position: relative;
            z-index: 1;
        }

        .heading {
            text-align: center;
            font-size: 28px;
            margin-bottom: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 10px;
            animation: slideDown 1s ease; 
        }

        @keyframes slideDown {
            0% {
                opacity: 0;
                transform: translateY(-30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chart-container {
            margin-top: 20px;
        }

        canvas {
            animation: chartFadeIn 1s ease; 
        }

        @keyframes chartFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="heading">
        Health Data Insights
    </div>

    <div>
        <h3>Average Blood Sugar and Blood Pressure</h3>
        <p><strong>When Medication Taken:</strong></p>
        <ul>
            <li>Average Blood Sugar: <?php echo number_format($avg_blood_sugar_taken, 2); ?> mg/dL</li>
            <li>Average Blood Sugar: <?php echo number_format($avg_blood_sugar_not_taken, 2); ?> mg/dL</li>
        </ul>
        <p><strong>When Medication Not Taken:</strong></p>
        <ul>
           
            <li>Average Blood Pressure: <?php echo number_format($avg_blood_pressure_taken, 2); ?> mmHg</li>
            <li>Average Blood Pressure: <?php echo number_format($avg_blood_pressure_not_taken, 2); ?> mmHg</li>
        </ul>
    </div>
    <div class="chart-container">
        <canvas id="lineChart"></canvas>
    </div>
    <div style="border: 2px solid black; border-radius: 10px; padding: 15px; background-color: #f9f9f9; max-width: 100%; margin: auto;">
        <canvas id="medicationChart" width="300" height="150"></canvas>
    </div>
</div>

<script>
    const conditions = <?php echo json_encode($conditions); ?>;
    const bloodSugarData = <?php echo json_encode($blood_sugar_data); ?>;
    const bloodPressureData = <?php echo json_encode($blood_pressure_data); ?>;

    const lineChartConfig = {
        type: 'line',
        data: {
            labels: conditions,
            datasets: [
                {
                    label: 'Blood Sugar (mg/dL)',
                    data: bloodSugarData,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: true,
                    tension: 0.4, 
                },
                {
                    label: 'Blood Pressure (mmHg)',
                    data: bloodPressureData,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: true,
                    tension: 0.4, 
                }
            ]
        },
        options: {
            responsive: true,
            animation: {
                duration: 1500, 
                easing: 'easeInOutQuart' 
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Health Metrics Over Time'
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Conditions'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Value'
                    }
                }
            }
        }
    };
    const medicationChartConfig = {
        type: 'bar',
        data: {
            labels: ['Medication Taken', 'Medication Not Taken'],
            datasets: [{
                label: 'Blood Sugar Sum',
                data: [<?php echo array_sum($blood_sugar_taken); ?>, <?php echo array_sum($blood_sugar_not_taken); ?>],
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            },
            {
                label: 'Blood Pressure Sum',
                data: [<?php echo array_sum($blood_pressure_taken); ?>, <?php echo array_sum($blood_pressure_not_taken); ?>],
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Sum'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Medication Adherence: Blood Sugar & Blood Pressure'
                }
            }
        }
    };

    window.onload = function() {
        const lineChart = new Chart(document.getElementById('lineChart'), lineChartConfig);
        const medicationChart = new Chart(document.getElementById('medicationChart'), medicationChartConfig);
    };
</script>
</body>
</html>
