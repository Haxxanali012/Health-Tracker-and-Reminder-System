<?php
include 'db_config.php'; 

$name_error = '';
$email_error = '';
$password_error = '';
$confirm_password_error = '';
$dob_error = ''; 
$name = '';
$email = '';
$password = '';
$confirm_password = '';
$gender = '';
$dob = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    $dob = isset($_POST['dob']) ? $_POST['dob'] : '';
    if (empty($name)) {
        $name_error = "Name is required.";
    }

    // Validate Email
    if (empty($email)) {
        $email_error = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Invalid email format.";
    }

    // Validate Password
    if (empty($password)) {
        $password_error = "Password is required.";
    } elseif (strlen($password) < 8) {
        $password_error = "Password must be at least 8 characters.";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $password_error = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match("/[a-z]/", $password)) {
        $password_error = "Password must contain at least one lowercase letter.";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $password_error = "Password must contain at least one number.";
    } elseif (!preg_match("/[\W_]/", $password)) {
        $password_error = "Password must contain at least one special character.";
    }

    // Validate Confirm Password
    if (empty($confirm_password)) {
        $confirm_password_error = "Please confirm your password.";
    } elseif ($password !== $confirm_password) {
        $confirm_password_error = "Passwords do not match.";
    }

    // Validate Date of Birth
    if (empty($dob)) {
        $dob_error = "Date of birth is required.";
    }

    // Check if email already exists
    if (empty($name_error) && empty($email_error) && empty($password_error) && empty($confirm_password_error) && empty($dob_error)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        if ($stmt === false) {
            die('Error in prepare: ' . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $email_error = "Email already exists.";
        } else {
            // Proceed with registration
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, date_of_birth, gender, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            if ($stmt === false) {
                die('Error in prepare: ' . $conn->error);
            }
            $stmt->bind_param("sssss", $name, $email, $hashed_password, $dob, $gender);

            if ($stmt->execute()) {
                header("Location: index.php");
                exit();
            } else {
                $email_error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #B2E0F0; 
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative; 
            overflow: hidden; 
        }
        @keyframes moveCells {
            0% {
                transform: translate(0, 0);
            }
            50% {
                transform: translate(100px, 150px);
            }
            100% {
                transform: translate(0, 0);
            }
        }
        .moving-cell {
            position: absolute;
            border-radius: 50%;
            opacity: 0.7;
            animation: moveCells 5s ease-in-out infinite;
        }
        .cell-small {
            width: 15px;
            height: 15px;
            background-color: rgba(255, 0, 0, 0.6); 
            animation-duration: 6s;
            top: 20%;
            left: 30%;
            animation-delay: 0s;
        }

        .cell-medium {
            width: 25px;
            height: 25px;
            background-color: rgba(0, 255, 0, 0.6);
            animation-duration: 4s;
            top: 50%;
            left: 60%;
            animation-delay: 1s;
        }

        .cell-large {
            width: 35px;
            height: 35px;
            background-color: rgba(0, 0, 255, 0.6); 
            animation-duration: 8s;
            top: 70%;
            left: 80%;
            animation-delay: 2s;
        }

        .cell-yellow {
            width: 20px;
            height: 20px;
            background-color: rgba(255, 255, 0, 0.6); 
            animation-duration: 5s;
            top: 40%;
            left: 20%;
            animation-delay: 1.5s;
        }

        .cell-purple {
            width: 30px;
            height: 30px;
            background-color: rgba(128, 0, 128, 0.6); 
            animation-duration: 7s;
            top: 10%;
            left: 40%;
            animation-delay: 0.5s;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.9); 
            border-radius: 10px;
            padding: 30px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            animation: slideIn 1s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            position: relative;
        }

        input[type="text"], input[type="email"], input[type="password"], input[type="date"], select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
            background-color: #f9f9f9;
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus, input[type="date"]:focus, select:focus {
            border-color: #007bff;
            outline: none;
        }

        select {
            padding: 10px;
            appearance: none;
            background-color: #f9f9f9;
        }

        .error {
            color: #ff6b6b;
            font-size: 0.875em;
            margin-top: 5px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        @media only screen and (max-width: 600px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sign Up</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <input type="text" name="name" placeholder="Full Name" value="<?php echo htmlspecialchars($name); ?>" required>
                <div class="error"><?php echo $name_error; ?></div>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
                <div class="error"><?php echo $email_error; ?></div>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
                <div class="error"><?php echo $password_error; ?></div>
            </div>
            <div class="form-group">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <div class="error"><?php echo $confirm_password_error; ?></div>
            </div>
            <div class="form-group">
                <input type="date" name="dob" placeholder="Date of Birth" value="<?php echo htmlspecialchars($dob); ?>" required>
                <div class="error"><?php echo $dob_error; ?></div> 
            </div>
            <div class="form-group">
                <label for="gender">Gender:</label>
                <select name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male" <?php if ($gender == 'Male') echo 'selected'; ?>>Male</option>
                    <option value="Female" <?php if ($gender == 'Female') echo 'selected'; ?>>Female</option>
                    <option value="Other" <?php if ($gender == 'Other') echo 'selected'; ?>>Other</option>
                </select>
            </div>
            <button type="submit">Register</button>
        </form>
    </div>
    <div class="moving-cell cell-small"></div>
    <div class="moving-cell cell-medium"></div>
    <div class="moving-cell cell-large"></div>
    <div class="moving-cell cell-yellow"></div>
    <div class="moving-cell cell-purple"></div>
    
</body>
</html>
