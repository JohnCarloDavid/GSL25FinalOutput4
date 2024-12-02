<?php
// Start the session
session_start();

// Include database connection file
include('db_connection.php');

// Initialize session variables if not set
if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Calculate the time difference since the last attempt
    $time_since_last_attempt = time() - $_SESSION['last_attempt_time'];

    // Check if the user has exceeded the maximum attempts
    if ($_SESSION['attempts'] >= 3) {
        // If less than 30 seconds have passed since the last attempt
        if ($time_since_last_attempt < 30) {
            $remaining_time = 30 - $time_since_last_attempt;
            $error = "Too many failed attempts. Please try again in " . $remaining_time . " seconds.";
        } else {
            // Reset the attempt counter after 30 seconds
            $_SESSION['attempts'] = 0;
        }
    }

    if ($_SESSION['attempts'] < 3) {
        // Get form inputs
        $username = isset($_POST['username']) ? $_POST['username'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        // Check if password and confirm password match
        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepare SQL query to prevent SQL injection
            $stmt = $conn->prepare("INSERT INTO tb_admin (user_name, password, role) VALUES (?, ?, 'employee')");
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param('ss', $username, $hashed_password);
            if ($stmt->execute()) {
                // Successful registration
                $_SESSION['attempts'] = 0;
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $username;

                // Redirect to login page after successful registration
                header("Location: login.php");
                exit();
            } else {
                // Registration failed
                $error = "An error occurred. Please try again.";
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - IMS</title>
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <style>
        /* SIGNUP PAGE */
        .body1 {
            background: url('img/steelbg.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        .loginHeader {
            position: absolute;
            top: 20px;
            text-align: center;
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            margin: 0 auto;
            background: transparent;
            color: #ffffff;
        }

        .loginHeader h1 {
            font-size: 4rem;
            font-family: 'Montserrat', sans-serif;
            color: #ffffff;
            letter-spacing: 2px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            position: relative;
            display: inline-block;
        }

        .loginHeader h1::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -10px;
            height: 4px;
            width: 100%;
            background: linear-gradient(to right, #be6b4a, #f3150d);
            border-radius: 2px;
        }

        .loginBody {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 300px;
            position: relative; /* For the background image */
            overflow: hidden;
        }

        .loginBody::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('img/steel1.jpg') no-repeat center center/cover; /* Update with your image */
            opacity: 1; 
            z-index: -1;
            border-radius: 10px;
        }

        .loginBody div {
            margin-bottom: 15px;
            text-align: left;
        }

        .loginBody label {
            display: block;
            margin-bottom: 5px;
            font-family: 'Montserrat', sans-serif;
            font-size: 1.2rem;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 1px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
        }

        .loginBody input {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            letter-spacing: 1px;
        }

        .loginBody button {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            letter-spacing: 1px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .loginBody button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            text-align: center;
            margin-top: 10px;
            font-family: 'Montserrat', sans-serif;
        }

        .login-link {
            text-align: center;
            margin-top: 15px;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
        }

        .login-link a {
            color:blue;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="body1">
    <div class="loginHeader">
        <h1>Inventory Management System</h1>
    </div>
    <div class="loginBody">
        <form action="signup.php" method="post">
            <div>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Sign Up</button>
            <?php if (isset($error)) : ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
        </form>
        <!-- Login Link -->
        <div class="login-link">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>
