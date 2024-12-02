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

// Check if an order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Order ID is required.";
    exit;
}

$order_id = $_GET['id'];

// Query to select the specific order from the tb_orders table
$sql = "SELECT * FROM tb_orders WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Order not found.";
    exit;
}

$order = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order - GSL25 Inventory Management System</title>
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            color: #2c3e50;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 2rem;
            margin: 0;
        }

        .back-button {
            background-color: #3498db;
            color: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 1rem;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
        }

        .back-button i {
            margin-right: 8px;
        }

        .back-button:hover {
            background-color: #2980b9;
        }

        .order-details {
            margin-bottom: 20px;
        }

        .order-details p {
            margin: 0;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .order-details p:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Details</h1>
            <a href="reports.php" class="back-button"><i class="fa fa-arrow-left"></i> Back to Orders</a>
        </div>
        
        <div class="order-details">
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
            <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
            <p><strong>Product Name:</strong> <?php echo htmlspecialchars($order['product_name']); ?></p>
            <p><strong>Quantity:</strong> <?php echo htmlspecialchars($order['quantity']); ?></p>
            <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
        </div>
    </div>
</body>
</html>
