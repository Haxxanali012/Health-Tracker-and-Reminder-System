<?php
include 'db_config.php'; /

$email_error = '';
$password_error = '';
$email = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate Email
    if (empty($email)) {
        $email_error = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Invalid email format.";
    }

    // Validate Password
    if (empty($password)) {
        $password_error = "Password is required.";
    }
    if (empty($email_error) && empty($password_error)) {
        if ($stmt = $conn->prepare("SELECT user_id, password_hash FROM users WHERE email = ?")) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            // Check if the email exists
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $hashed_password);
                $stmt->fetch();

                // Verify the password
                if (password_verify($password, $hashed_password)) {
                    session_start();
                    $_SESSION['user_id'] = $id;
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $password_error = "Invalid password.";
                }
            } else {
                $email_error = "Invalid email.";
            }
            $stmt->close();
        } else {
            $email_error = "Error preparing statement: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: white;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden; /* Hide overflow for smooth animation */
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative; /* For particle animation */
        }

        /* Particle Background Animation */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #1e3c72, #2a5298, #0f2027);
            z-index: -2; /* Place it behind the content */
            overflow: hidden;
        }

        .particle {
            position: absolute;
            display: block;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            pointer-events: none;
            animation: moveParticles 20s infinite ease-in-out;
        }

        @keyframes moveParticles {
            0% {
                transform: translateY(100vh);
                opacity: 0;
            }
            25% {
                opacity: 0.6;
            }
            100% {
                transform: translateY(-100vh);
                opacity: 0;
            }
        }

        .particle:nth-child(1) {
            width: 10px;
            height: 10px;
            left: 10%;
            animation-duration: 22s;
        }

        .particle:nth-child(2) {
            width: 15px;
            height: 15px;
            left: 20%;
            animation-duration: 24s;
        }

        .particle:nth-child(3) {
            width: 7px;
            height: 7px;
            left: 30%;
            animation-duration: 18s;
        }

        .particle:nth-child(4) {
            width: 12px;
            height: 12px;
            left: 40%;
            animation-duration: 26s;
        }

        .particle:nth-child(5) {
            width: 20px;
            height: 20px;
            left: 50%;
            animation-duration: 28s;
        }

        .particle:nth-child(6) {
            width: 8px;
            height: 8px;
            left: 60%;
            animation-duration: 19s;
        }

        .particle:nth-child(7) {
            width: 14px;
            height: 14px;
            left: 70%;
            animation-duration: 21s;
        }

        .container {
            width: 400px;
            padding: 30px;
            background-color: rgba(52, 73, 94, 0.9);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            z-index: 1;
            position: relative;
        }

        h2 {
            text-align: center;
            color: white;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            position: absolute;
            left: 10px;
            top: 14px;
            color: #ccc;
            font-size: 16px;
            transition: all 0.2s ease;
            pointer-events: none;
        }

        .form-group input {
            width: 94%;
            padding: 12px 10px;
            border: 2px solid #ccc;
            border-radius: 5px;
            outline: none;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            border-color: #4CAF50;
        }

        .form-group input:focus + label,
        .form-group input:not(:placeholder-shown) + label {
            top: -10px;
            left: 10px;
            font-size: 12px;
            color: #4CAF50;
        }

        .error {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        p {
            text-align: center;
            color: white;
            margin-top: 15px;
        }

        p a {
            color: #4CAF50;
            text-decoration: none;
        }

        p a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<!-- Particle Background -->
<div class="particles">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
</div>

<div class="container">
    <h2>Login</h2>
    <form action="index.php" method="post">
        <div class="form-group">
            <input type="email" name="email" id="email" required placeholder=" " value="<?php echo htmlspecialchars($email); ?>">
            <label for="email">Email</label>
            <?php if ($email_error): ?>
                <div class="error"><?php echo $email_error; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <input type="password" name="password" id="password" required placeholder=" ">
            <label for="password">Password</label>
            <?php if ($password_error): ?>
                <div class="error"><?php echo $password_error; ?></div>
            <?php endif; ?>
        </div>

        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
</div>

</body>
</html>
