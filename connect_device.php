<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect Device</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            overflow: hidden;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            animation: fadeIn 1s ease-out;
            position: relative;
        }

        .container h2 {
            font-size: 28px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .container p {
            font-size: 16px;
            color: #666;
            text-align: center;
            margin-bottom: 20px;
        }

        .container label {
            font-size: 14px;
            color: #333;
            display: block;
            margin-bottom: 10px;
        }

        .container input[type="text"] {
            width: 95%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-bottom: 5px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .container input[type="text"]:focus {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }

        .container button {
            width: 100%;
            padding: 12px;
            background-color: #2575fc;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .container button:hover {
            background-color: #6a11cb;
            box-shadow: 0 5px 15px rgba(106, 17, 203, 0.4);
        }

        .floating-shape {
            position: absolute;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            animation: floatUp 20s infinite ease-in-out;
        }

        .floating-shape:nth-child(1) {
            width: 30px;
            height: 30px;
            left: 15%;
            bottom: 10%;
            animation-duration: 18s;
        }

        .floating-shape:nth-child(2) {
            width: 50px;
            height: 50px;
            left: 70%;
            bottom: 30%;
            animation-duration: 24s;
        }

        .floating-shape:nth-child(3) {
            width: 40px;
            height: 40px;
            right: 20%;
            bottom: 5%;
            animation-duration: 22s;
        }

        @keyframes floatUp {
            0% { transform: translateY(0); }
            100% { transform: translateY(-120vh); }
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        .connecting-message {
            text-align: center;
            color: #2575fc;
            font-size: 16px;
            margin-top: 20px;
            display: none;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>

    <script>
        function validateDeviceID() {
            var deviceID = document.getElementById('device_id').value;
            var macRegex = /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/;
            var message = document.getElementById('errorMessage');

            if (!macRegex.test(deviceID)) {
                message.textContent = 'Please enter a valid MAC address (e.g., 00:1A:2B:3C:4D:5E)';
                return false;
            } else {
                message.textContent = ''; 
                return showConnectingMessage();
            }
        }

        function showConnectingMessage() {
            var form = document.getElementById('connectForm');
            var message = document.getElementById('connectingMessage');
            
            message.style.display = 'block';

            setTimeout(function() {
                form.submit(); 
                showSuccessMessage(); 
            }, 2000);

            return false; 
        }

        function showSuccessMessage() {
            alert("The Device is connected successfully!");
        }
    </script>
</head>
<body>

<div class="floating-shape"></div>
<div class="floating-shape"></div>
<div class="floating-shape"></div>

<div class="container">
    <h2>Connect Your Device</h2>
    <p>Please follow the instructions below to connect your device.</p>

    <form id="connectForm" method="POST" action="process_device_connection.php" onsubmit="return validateDeviceID();">
        <label for="device_id">Device ID:</label>
        <input type="text" id="device_id" name="device_id" required>
        
        <!-- Error message will now appear here -->
        <div id="errorMessage" class="error-message"></div>

        <button type="submit">Connect Device</button>
    </form>

    <div id="connectingMessage" class="connecting-message">
        Connecting to device...
    </div>
</div>

</body>
</html>
