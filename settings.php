<?php
session_start();
include 'db_connection.php'; // Ensure you have a file to handle your DB connection

$message = ""; // Initialize a message variable
$default_password = 'admin'; // The default password for the 'admin' user

// Check if the user is logged in and if they are an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header("Location: login.php");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $username = trim($_POST['username']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Fetch the user's current hashed password from the database
    $stmt = $conn->prepare("SELECT password FROM tb_admin WHERE user_name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    // Check if a user was found and verify current password
    if ($hashed_password !== null) {
        // Verify the current password against the hashed password
        if (password_verify($current_password, $hashed_password) || $current_password === $default_password) {
            // Validate new password length
            if (strlen($new_password) < 8) {
                $message = "<div class='bg-red-500 text-white p-3 mb-4'>New password must be at least 8 characters long.</div>";
            } else {
                // Hash the new password
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the password in the database
                $update_stmt = $conn->prepare("UPDATE tb_admin SET password = ? WHERE user_name = ?");
                $update_stmt->bind_param("ss", $new_hashed_password, $username);
                if ($update_stmt->execute()) {
                    $message = "<div class='bg-green-500 text-white p-3 mb-4'>Password changed successfully!</div>";
                } else {
                    $message = "<div class='bg-red-500 text-white p-3 mb-4'>Error updating password: " . htmlspecialchars($conn->error) . "</div>";
                }
                $update_stmt->close();
            }
        } else {
            $message = "<div class='bg-red-500 text-white p-3 mb-4'>Current password is incorrect!</div>";
        }
    } else {
        $message = "<div class='bg-red-500 text-white p-3 mb-4'>No user found with that username.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - GSL25 Inventory Management System</title>
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            margin: 0;
            color: #2c3e50;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dark-mode {
            background-color: #2c3e50;
            color: #ecf0f1;
        }

        .sidebar {
            width: 260px;
            background: linear-gradient(145deg, #34495e, #2c3e50);
            color: #ecf0f1;
            padding: 30px 20px;
            height: 100vh;
            position: fixed;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: background 0.3s ease;
        }

        .sidebarHeader h2 {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .sidebarNav ul {
            list-style: none;
            padding: 0;
            margin-bottom: 20px;
        }

        .sidebarNav ul li {
            margin: 1.2rem 0;
        }

        .sidebarNav ul li a {
            text-decoration: none;
            color: #ecf0f1;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .sidebarNav ul li a:hover {
            background-color: #2980b9;
        }

        .sidebarNav ul li a i {
            margin-right: 15px;
        }

        .mainContent {
            margin-left: 280px;
            padding: 30px;
            width: calc(100% - 280px);
            min-height: 100vh;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .form-container {
            max-width: 600px;
            margin: 0 auto; /* Center the form */
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #ced4da;
        }

        .form-container h2 {
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 2rem;
            color: #3498db;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            border-color: #3498db;
            outline: none;
        }

        .submit-button {
            background-color: #3498db;
            color: #ffffff;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background-color: #2980b9;
        }

        @media (max-width: 768px) {
            .mainContent {
                margin-left: 0;
                width: 100%;
            }
        }

        .logout-form {
            margin-top: auto; 
        }

        .logout-button {
            background-color: #e74c3c;
            color: #ffffff; 
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            transition: background-color 0.3s;
            margin-top: 10px; 
        }

        .logout-button i {
            margin-right: 8px; 
            font-size: 1.2rem; 
        }

        .logout-button:hover {
            background-color: #c0392b; 
        }
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebarHeader">
        <h2>GSL25 Dashboard</h2>
    </div>
    <nav class="sidebarNav">
        <ul>
            <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="inventory.php"><i class="fa fa-box"></i> Inventory</a></li>
            <li><a href="orders.php"><i class="fas fa-cash-register"></i> Point of Sale (POS)</a></li>
            <li><a href="reports.php"><i class="fa fa-chart-line"></i> Reports</a></li>
            <li><a href="settings.php"><i class="fa fa-cog"></i> Settings</a></li>
        </ul>
        <!-- Logout Button -->
        <form action="logout.php" method="POST" class="logout-form">
            <button type="submit" class="logout-button">
                <i class="fa fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </nav>
</aside>
    <div class="mainContent">
        <div class="form-container">
            <h2>Change Password</h2>
            <?php if ($message): ?>
                <?php echo $message; ?>
            <?php endif; ?>
            <form action="settings.php" method="POST" class="space-y-4">
                <div class="form-group">
                    <label for="username"class="text-blue-400">Username:</label>
                    <input type="text" id="username" name="username" value="admin" readonly class="border p-2 w-full text-black " readonly>
                </div>
                <div class="form-group">
                    <label for="current_password" class="text-blue-400" >Current Password:</label>
                    <input type="password" id="current_password" name="current_password" required class="border p-2 w-full text-black">
                </div>
                <div class="form-group">
                    <label for="new_password" class="text-blue-400">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required class="border p-2 w-full text-black">
                </div>
                <button type="submit" class="submit-button">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>
