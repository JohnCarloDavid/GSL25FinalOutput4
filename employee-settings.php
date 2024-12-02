<?php
session_start();
include 'db_connection.php'; // Ensure you have a file to handle your DB connection

$message = ""; // Initialize a message variable

// Check if the user is logged in and if they are an employee
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'employee') {
    // Redirect to login page if not logged in or not an employee
    header("Location: login.php");
    exit();
}

// Fetch logged-in employee's username from session
$username = $_SESSION['username'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Fetch the employee's current hashed password from the database
    $stmt = $conn->prepare("SELECT password FROM tb_admin WHERE user_name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    // Check if a user was found and verify current password
    if ($hashed_password !== null) {
        // Verify the current password against the hashed password
        if (password_verify($current_password, $hashed_password)) {
            // Validate new password length
            if (strlen($new_password) < 8) {
                $message = "<div class='bg-red-500 text-white p-3 mb-4 rounded'>New password must be at least 8 characters long.</div>";
            } else {
                // Hash the new password
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the password in the database
                $update_stmt = $conn->prepare("UPDATE tb_admin SET password = ? WHERE user_name = ?");
                $update_stmt->bind_param("ss", $new_hashed_password, $username);
                if ($update_stmt->execute()) {
                    $message = "<div class='bg-green-500 text-white p-3 mb-4 rounded'>Password changed successfully!</div>";
                } else {
                    $message = "<div class='bg-red-500 text-white p-3 mb-4 rounded'>Error updating password: " . htmlspecialchars($conn->error) . "</div>";
                }
                $update_stmt->close();
            }
        } else {
            $message = "<div class='bg-red-500 text-white p-3 mb-4 rounded'>Current password is incorrect!</div>";
        }
    } else {
        $message = "<div class='bg-red-500 text-white p-3 mb-4 rounded'>No employee found with that username.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Employee Settings</title>
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
        body {
            background-color: #f7fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .mainContent {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-container {
            padding: 20px;
        }
        .form-group label {
            font-size: 1rem;
            font-weight: 500;
            color: #4a4a4a;
        }
        .form-group input {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s ease-in-out;
        }
        .form-group input:focus {
            border-color: #4C9BFE;
            outline: none;
        }
        .submit-button {
            background-color: #4C9BFE;
            color: white;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
        }
        .submit-button:hover {
            background-color: #3874c4;
        }
        .back-button {
            display: inline-block;
            margin-top: 16px;
            padding: 12px 24px;
            background-color: #E2E8F0;
            color: #3182CE;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #CBD5E0;
        }
    </style>
</head>
<body>

    <div class="mainContent">
        <div class="form-container">
            <h2 class="text-2xl font-semibold text-center text-gray-800 mb-6">Change Password</h2>
            
            <?php if ($message): ?>
                <?php echo $message; ?>
            <?php endif; ?>
            
            <form action="employee-settings.php" method="POST" class="space-y-4">
                <div class="form-group">
                    <label for="username" class="text-blue-400">Username:</label>
                    <input type="text" id="username" name="username" required class="border p-2 w-full text-black">
                </div>

                <div class="form-group">
                    <label for="current_password">Current Password:</label>
                    <input type="password" id="current_password" name="current_password" required class="border p-2 w-full text-black">
                </div>

                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required class="border p-2 w-full text-black">
                </div>

                <button type="submit" class="submit-button">Change Password</button>
            </form>

            <!-- Back Button -->
            <a href="employee_landing.php" class="back-button">Back</a>
        </div>
    </div>

</body>
</html>
