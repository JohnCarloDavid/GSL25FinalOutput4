<?php
// Start the session
session_start();

// Include database connection file
include('db_connection.php');
// Check if the user is logged in and if they are an employee
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'employee') {
    // Redirect to login page if not logged in or not an employee
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
            ORDER BY o.order_id DESC";
    $stmt = $conn->prepare($sql);
    $searchParam = "%" . $searchQuery . "%";
    $stmt->bind_param('ss', $selected_date, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($selected_date) {
    $sql = "SELECT o.*, i.size, i.price FROM tb_orders o 
            JOIN tb_inventory i ON o.product_name = i.name 
            WHERE o.order_date = ? ORDER BY o.order_id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $selected_date);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($searchQuery) {
    $sql = "SELECT o.*, i.size, i.price FROM tb_orders o 
            JOIN tb_inventory i ON o.product_name = i.name 
            WHERE o.customer_name LIKE ? ORDER BY o.order_id DESC";
    $stmt = $conn->prepare($sql);
    $searchParam = "%" . $searchQuery . "%";
    $stmt->bind_param('s', $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Default query if no filters are applied
    $sql = "SELECT o.*, i.size, i.price FROM tb_orders o 
            JOIN tb_inventory i ON o.product_name = i.name 
            ORDER BY o.order_id DESC";
    $result = $conn->query($sql);
}

// Initialize variables for total amount and total quantity
$total_quantity = 0;
$customerTotalAmount = 0; 
$lastCustomerName = ""; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Orders - GSL25 Inventory Management System</title>
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
 body {
    font-family: 'Poppins', sans-serif;
    display: flex;
    margin: 0;
    color: #2c3e50;
    justify-content: center;
    align-items: flex-start;
    height: 100vh;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.mainContent {
    width: 80%; /* Adjust as needed for responsiveness */
    padding: 30px;
    min-height: 100vh;
    transition: background-color 0.3s ease, color 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.mainHeader {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.search-form {
    position: fixed; 
    top: 0;
    left: 0;
    width: 100%;
    background-color: #fff;
    padding: 10px;
    z-index: 100;
    display: flex;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}


.search-input {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 250px;
    font-size: 14px;
    outline: none;
}

.search-button, .clear-button {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    margin-left: 5px;
}

.search-button:hover, .clear-button:hover {
    background-color: #45a049;
}

.ordersSection {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.ordersTable {
    width: 90%; /* Adjust to desired width */
    border-collapse: collapse;
    margin-bottom: 30px;
    margin-top: 20px;
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


.ordersTable {
    margin-bottom: 20px;
}

.back-button {
    background-color: #4CAF50; 
    color: white;
    padding: 8px 15px; 
    border-radius: 5px; 
    text-decoration: none; 
    display: inline-block; 
    margin-left: 10px; 
    font-size: 14px; 
    cursor: pointer;
    transition: background-color 0.3s ease; 
}

.back-button:hover {
    background-color: #45a049;
}

.recently-deleted-btn {
    font-size: 1rem;
    font-weight: 600;
    text-align: center;
    display: inline-flex;
    align-items: center;
}

.recently-deleted-btn i {
    font-size: 1.2rem;  /* Adjust icon size */
}

    </style>
</head>
<body>
<div class="mainContent">
<header class="mainHeader">
    <!-- Search Bar for Employee -->
    <form action="employee-orders.php" method="GET" class="search-form flex items-center space-x-4">
        <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search by customer name" class="search-input p-3 rounded border border-gray-300 w-64" />
        <button type="submit" class="search-button bg-blue-500 text-white p-3 rounded hover:bg-blue-600 transition-colors"><i class="fa fa-search"></i></button>
        
        <!-- Clear Search Button -->
        <button type="button" class="clear-button text-gray-700 p-3 rounded border border-gray-300 hover:bg-gray-100 transition-colors" onclick="clearSearch()">Clear Search</button>

        <!-- Recently Deleted Button -->
        <a href="employee-recently-delete.php" class="recently-deleted-btn bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600 transition-colors">
            <i class="fa fa-undo mr-2"></i> Recently Deleted
        </a>

        <!-- Back Button -->
        <a href="employee_landing.php" class="back-button text-blue-500 hover:text-blue-700">Back</a>
    </form>
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

                if ($row['customer_name'] != $lastCustomerName) {
                    if ($lastCustomerName != "") {
                        // Display the total amount for the previous customer
                        echo '<tr><td colspan="5"><strong>Total Amount: ' . number_format($customerTotalAmount, 2) . '</strong></td></tr>';
                        echo '<tr><td colspan="5">&nbsp;</td></tr>'; // Add space between customers
                    }

                    // Reset the customer total for the new customer
                    $customerTotalAmount = 0;
                    $lastCustomerName = $row['customer_name'];

                    // Start a new table for each customer with the class "light-blue-table"
                    echo '<table class="ordersTable light-blue-table">';
                    echo '<thead>
                            <tr>
                                <th colspan="5" class="customer-info">
                                    <strong>Customer: ' . htmlspecialchars($row['customer_name']) . '</strong><br>
                                    <strong>Order Date: ' . htmlspecialchars($row['order_date']) . '</strong>
                                </th>
                            </tr>
                            <tr>
                                <th>Product Name</th>
                                <th>Size</th>
                                <th>Quantity</th>
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
                        <td>' . htmlspecialchars(number_format($total_price, 2)) . '</td>
                        <td>
                            <a href="employee-edit-order.php?id=' . htmlspecialchars($row['order_id']) . '" class="button"><i class="fa fa-edit"></i></a>
                            <a href="employee-delete-order.php?id=' . htmlspecialchars($row['order_id']) . '" class="button" onclick="return confirm(\'Are you sure you want to delete this order?\');"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>';
            }

            // Display the total amount for the last customer
            echo '<tr><td colspan="5"><strong>Total Amount: ' . number_format($customerTotalAmount, 2) . '</strong></td></tr>';
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
