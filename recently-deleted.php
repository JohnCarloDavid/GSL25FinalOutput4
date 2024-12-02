<?php
// Start the session
session_start();

// Include database connection file
include('db_connection.php');

// Check if the user is logged in and if they are an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header("Location: login.php");
    exit();
}

// Handle restoration of deleted orders
if (isset($_GET['restore_id'])) {
    $restore_id = $_GET['restore_id'];

    // Fetch the deleted order details from tb_deleted_orders
    $fetch_sql = "SELECT * FROM tb_deleted_orders WHERE order_id = ?";
    $stmt = $conn->prepare($fetch_sql);
    $stmt->bind_param('i', $restore_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $deleted_order = $result->fetch_assoc();

    if ($deleted_order) {
        // Insert the deleted order back into tb_orders
        $insert_sql = "INSERT INTO tb_orders (order_id, customer_name, product_name, size, quantity, order_date) 
                       VALUES (?, ?, ?, ?, ?, ?)"; // Removed status from insert statement
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param('isssis', $deleted_order['order_id'], $deleted_order['customer_name'], 
                                 $deleted_order['product_name'], $deleted_order['size'], 
                                 $deleted_order['quantity'], $deleted_order['deleted_at']);  // Use deleted_at as order_date
        if ($insert_stmt->execute()) {
            // Delete the order from tb_deleted_orders after successful restoration
            $delete_sql = "DELETE FROM tb_deleted_orders WHERE order_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param('i', $restore_id);
            $delete_stmt->execute();

            // Redirect back to the Recently Deleted Orders page
            header("Location: recently-deleted.php");
            exit;
        } else {
            echo "Error restoring order.";
        }
    } else {
        echo "Order not found.";
    }
}

// Handle deletion of deleted orders
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM tb_deleted_orders WHERE order_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param('i', $delete_id);
    if ($stmt->execute()) {
        header("Location: recently-deleted.php");
        exit;
    } else {
        echo "Error deleting order.";
    }
}

// Handle deletion of all deleted orders
if (isset($_POST['delete_all'])) {
    $delete_all_sql = "DELETE FROM tb_deleted_orders";
    if ($conn->query($delete_all_sql)) {
        header("Location: recently-deleted.php");
        exit;
    } else {
        echo "Error deleting all orders.";
    }
}

// Fetch deleted orders from the database
$sql = "SELECT * FROM tb_deleted_orders ORDER BY deleted_at DESC";
$result = $conn->query($sql);

// Fetch deleted orders along with their price
$sql = "
    SELECT d.*, i.price 
    FROM tb_deleted_orders d
    LEFT JOIN tb_inventory i ON d.product_name = i.name 
    ORDER BY d.deleted_at DESC
";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recently Deleted Orders - GSL25 Inventory Management System</title>
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            color: #2c3e50;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .mainContent {
            padding: 30px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .mainHeader {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .mainHeader h1 {
            font-size: 2.5rem;
            margin: 0;
            text-align: center;
        }

        .backButton {
            margin-bottom: 20px;
            display: inline-block;
            background-color: #3498db;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 1rem;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .backButton:hover {
            background-color: #2980b9;
        }

        .actionsContainer {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .deleteAllButton {
            background-color: #e74c3c;
            color: #ffffff;
            padding: 10px 20px;
            position: relative;
            top: 90px;
            border-radius: 8px;
            font-size: 1rem;
            text-align: center;
            text-decoration: none;
            border: 1px solid #c0392b;
            transition: background-color 0.3s ease;
        }

        .deleteAllButton:hover {
            background-color: #c0392b;
        }

        .ordersTable {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .ordersTable th, .ordersTable td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .ordersTable th {
            background-color: #3498db;
            color: #ffffff;
        }

        .ordersTable tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .button {
            background-color: #ffffff; 
            color: #c0392b; 
            padding: 5px 10px; 
            border-radius: 8px;
            font-size: 1rem;
            text-align: center;
            text-decoration: none;
            border: 1px solid #3498db; 
            transition: background-color 0.3s ease, color 0.3s ease;
            display: inline-block; 
            cursor: pointer; 
        }

        .button:hover {
            background-color: #3498db;
            color: #ffffff; 
        }

        @media (max-width: 768px) {
            .mainContent {
                width: 100%;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="mainContent">
        <a href="orders.php" class="backButton"><i class="fa fa-arrow-left"></i> Back to Orders</a>

        <!-- Actions Container -->
        <div class="actionsContainer">
            <form method="POST" action="">
                <button type="submit" name="delete_all" class="deleteAllButton" onclick="return confirm('Are you sure you want to delete all orders?');">Delete All</button>
            </form>
        </div>

        <header class="mainHeader">
            <h1>Recently Deleted Orders</h1>
        </header>

<!-- Recently Deleted Orders Table -->
<section class="ordersSection">
        <table class="ordersTable">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Product Name</th>
                    <th>Size</th>
                    <th>Quantity</th>
                    <th>Price</th> <!-- Added Price Column -->
                    <th>Total Price</th> <!-- Added Total Price Column -->
                    <th>Deleted At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['customer_name']); ?></td>
                        <td><?= htmlspecialchars($row['product_name']); ?></td>
                        <td><?= htmlspecialchars($row['size']); ?></td>
                        <td><?= htmlspecialchars($row['quantity']); ?></td>
                        <td><?= htmlspecialchars($row['price'] ?? 0); ?></td> <!-- Displaying Price, defaults to 0 if null -->
                        <td><?= htmlspecialchars(($row['price'] ?? 0) * $row['quantity']); ?></td> <!-- Displaying Total Price, defaults to 0 if null -->
                        <td><?= htmlspecialchars($row['deleted_at']); ?></td>
                        <td>
                            <a href="recently-deleted.php?restore_id=<?= $row['order_id']; ?>" class="button">Restore</a>
                            <a href="recently-deleted.php?delete_id=<?= $row['order_id']; ?>" 
                               class="button" 
                               onclick="return confirm('Are you sure you want to delete this order?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
    <?php
    // Close the database connection
    $conn->close();
    ?>
    </div>
</body>
</html>
