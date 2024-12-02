<?php
// Include database connection file
include('db_connection.php');

// Check if the user is logged in, if not then redirect to the login page
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Check if 'id' parameter is set
if (isset($_GET['id'])) {
    $order_id = $_GET['id'];

    // Prepare and execute query to fetch the order details
    $sql = "SELECT * FROM tb_orders WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();

        // Insert the order into the deleted orders table, including customer name
        $sql = "INSERT INTO tb_deleted_orders (order_id, customer_name, product_name, size, quantity, order_date, deleted_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isssis', $order['order_id'], $order['customer_name'], $order['product_name'], $order['size'], $order['quantity'], $order['order_date']);
        $stmt->execute();

        // Delete the order from the original table
        $sql = "DELETE FROM tb_orders WHERE order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $order_id);
        $stmt->execute();
    }

    header("Location: orders.php");
    exit;
}
?>
