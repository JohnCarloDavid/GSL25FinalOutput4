<?php
// Start the session
session_start();

// Include database connection file
include('db_connection.php');

// Check if the user is logged in, if not then redirect to the login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Function to update inventory quantity
function updateInventory($conn, $productName, $quantity) {
    // Fetch the current quantity from the inventory
    $sql = "SELECT quantity FROM tb_inventory WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $productName);
    $stmt->execute();
    $stmt->bind_result($currentQuantity);
    $stmt->fetch();
    $stmt->close();

    // Deduct the ordered quantity from the current quantity
    $newQuantity = $currentQuantity - $quantity;

    // Update the inventory with the new quantity
    $sql = "UPDATE tb_inventory SET quantity = ? WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $newQuantity, $productName);
    $stmt->execute();
    $stmt->close();
}

// Process the status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = $_POST['order_id'];
    $status = $_POST['status'];

    // Fetch the product name and quantity for the order
    $sql = "SELECT product_name, quantity FROM tb_orders WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $stmt->bind_result($productName, $quantity);
    $stmt->fetch();
    $stmt->close();

    // If the status is "Completed", update the inventory
    if ($status == 'Completed') {
        updateInventory($conn, $productName, $quantity);
    }

    // Update the order status in the database
    $sql = "UPDATE tb_orders SET status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $orderId);
    $stmt->execute();
    $stmt->close();

    // Redirect to the orders page
    header("Location: orders.php");
    exit;
}
?>
