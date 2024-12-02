<?php
// Start the session
session_start();
include('db_connection.php');

// Check if the logged-in user is an admin
if (isset($_SESSION['loggedin']) && $_SESSION['role'] === 'admin') {
    // Destroy the session
    session_unset();  // Clear all session variables
    session_destroy();  // Destroy the session itself

    // Redirect to login page or another page
    header("Location: login.php");  // Change 'login.php' to your desired redirection page
    exit();
} else {
    // If not an admin, redirect them to a different page (e.g., homepage or unauthorized page)
    header("Location: login.php");  // Redirect to homepage or an error page
    exit();
}
?>
