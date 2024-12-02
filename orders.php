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


// Initialize variables
$selected_date = '';
$searchQuery = ''; // Variable for the search query

// Check if a date filter has been applied
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selected_date'])) {
    $selected_date = $_POST['selected_date'];
}

// Check if a search query has been entered
if (isset($_GET['search'])) {
    $searchQuery = $_GET['search'];
}

// Fetch orders from the database based on the selected date or search query
if ($selected_date && $searchQuery) {
    $sql = "SELECT o.*, i.size, i.price FROM tb_orders o 
            JOIN tb_inventory i ON o.product_name = i.name 
            WHERE o.order_date = ? AND o.customer_name LIKE ? 
            ORDER BY o.order_id DESC";  // Changed ASC to DESC
    $stmt = $conn->prepare($sql);
    $searchParam = "%" . $searchQuery . "%";
    $stmt->bind_param('ss', $selected_date, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($selected_date) {
    $sql = "SELECT o.*, i.size, i.price FROM tb_orders o 
            JOIN tb_inventory i ON o.product_name = i.name 
            WHERE o.order_date = ? ORDER BY o.order_id DESC";  // Changed ASC to DESC
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $selected_date);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($searchQuery) {
    $sql = "SELECT o.*, i.size, i.price FROM tb_orders o 
            JOIN tb_inventory i ON o.product_name = i.name 
            WHERE o.customer_name LIKE ? ORDER BY o.order_id DESC";  // Changed ASC to DESC
    $stmt = $conn->prepare($sql);
    $searchParam = "%" . $searchQuery . "%";
    $stmt->bind_param('s', $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Default query if no filters are applied
    $sql = "SELECT o.*, i.size, i.price FROM tb_orders o 
            JOIN tb_inventory i ON o.product_name = i.name 
            ORDER BY o.order_id DESC";  // Changed ASC to DESC
    $result = $conn->query($sql);
}
// Calculate the total number of orders
$total_orders = 0;
if ($result) {
    $total_orders = $result->num_rows;
}

// Initialize variables for total amount and total quantity
$total_amount = 0;
$total_quantity = 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - GSL25 Inventory Management System</title>
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    
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
        <header class="mainHeader">
            <div class="headerActions">
                <a href="add-order.php" class="button"><i class="fa fa-plus"></i> Add New Order</a>
                <a href="recently-deleted.php" class="button"><i class="fa fa-undo"></i> Recently Deleted</a>
                <!-- Search Bar -->
                <form action="orders.php" method="GET" class="search-form">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search by customer name" class="search-input" />
                    <button type="submit" class="search-button"><i class="fa fa-search"></i></button>
                    <!-- Clear Search Button -->
                    <button type="button" class="clear-button" onclick="clearSearch()">Clear Search</button>
                </form>
            </div>
        </header>

<!-- Orders Section -->
<section class="ordersSection">
    <?php 
    // Initialize a variable to store the last customer name
    $lastCustomerName = "";
    $customerTotalAmount = 0; // Variable to store total amount per customer

    // Display the fetched orders
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) { 
            $total_quantity += $row['quantity'];
            $total_price = $row['quantity'] * $row['price']; // Calculate total price for the order
            $total_amount += $total_price; // Add to the total amount

            if ($row['customer_name'] != $lastCustomerName) {
                if ($lastCustomerName != "") {
                    // Display the total amount for the previous customer
                    echo '<tr><td colspan="6"><strong>Total Amount: ' . number_format($customerTotalAmount, 2) . '</strong></td></tr>';
                    echo '<tr><td colspan="6">&nbsp;</td></tr>'; // Add space between customers
                }

                // Reset the customer total for the new customer
                $customerTotalAmount = 0;
                $lastCustomerName = $row['customer_name'];

                // Start a new table for each customer with the class "light-blue-table"
                echo '<table class="ordersTable light-blue-table">';
                echo '<thead>
                        <tr>
                            <th colspan="6" class="customer-info">
                                <strong>Customer: ' . htmlspecialchars($row['customer_name']) . '</strong><br>
                                <strong>Order Date: ' . htmlspecialchars($row['order_date']) . '</strong>
                            </th>
                        </tr>
                        <tr>
                            <th>Product Name</th>
                            <th>Size</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>';
                echo '<tbody>';
            
            }

            // Add to the customer's total amount
            $customerTotalAmount += $total_price;

            // Display Order Details Row
            echo '<tr class="order-row">
                    <td>' . htmlspecialchars($row['product_name']) . '</td>
                    <td>' . htmlspecialchars($row['size']) . '</td>
                    <td>' . htmlspecialchars($row['quantity']) . '</td>
                    <td>' . htmlspecialchars(number_format($row['price'], 2)) . '</td>
                    <td>' . htmlspecialchars(number_format($total_price, 2)) . '</td>
                    <td>
                        <a href="edit-order.php?id=' . htmlspecialchars($row['order_id']) . '" class="button"><i class="fa fa-edit"></i></a>
                        <a href="delete-order.php?id=' . htmlspecialchars($row['order_id']) . '" class="button" onclick="return confirm(\'Are you sure you want to delete this order?\');"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>';
        }

        // Display the total amount for the last customer
        echo '<tr><td colspan="6"><strong>Total Amount: ' . number_format($customerTotalAmount, 2) . '</strong></td></tr>';
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No orders found.</p>';
    }
    ?>
</section>
    </div>

    <script>
        // Function to clear search input
        function clearSearch() {
            document.querySelector('.search-input').value = '';
            document.querySelector('.search-form').submit();
        }
    </script>
</body>
</html>
