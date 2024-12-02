<?php
// Database configuration
$servername = "localhost";  // Change if your server is different
$username = "root";         // Your MySQL username
$password = "";             // Your MySQL password
$dbname = "db_inventory_system1"; // The database you created

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
