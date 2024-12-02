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

// Initialize message variables
$error_message = '';
$success_message = '';

// Get the order ID from the query string
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $_POST['customer_name'];
    $product_name = $_POST['product_name'];
    $quantity = $_POST['quantity'];
    $order_date = $_POST['order_date'];
    $size = $_POST['size']; // Capture the size field value

    // Update the order in the database without status
    $sql = "UPDATE tb_orders SET customer_name = ?, product_name = ?, quantity = ?, order_date = ?, size = ? WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssissi', $customer_name, $product_name, $quantity, $order_date, $size, $order_id);

    if ($stmt->execute()) {
        $success_message = 'Order updated successfully.';
    } else {
        $error_message = 'Error updating order.';
    }
}

// Fetch product names and sizes for the dropdown
$product_sql = "SELECT name FROM tb_inventory";
$product_result = $conn->query($product_sql);

// Fetch product sizes and prices for dynamic options
$size_sql = "SELECT name, size, price FROM tb_inventory";
$size_result = $conn->query($size_sql);

$product_sizes = [];
$product_prices = [];
while ($row = $size_result->fetch_assoc()) {
    $product_name = $row['name'];
    $size = $row['size'];
    $price = $row['price'];
    
    if (!isset($product_sizes[$product_name])) {
        $product_sizes[$product_name] = [];
    }
    if (!isset($product_prices[$product_name])) {
        $product_prices[$product_name] = [];
    }
    
    $product_sizes[$product_name][] = $size;
    $product_prices[$product_name] = $price;
}

$product_sizes_json = json_encode($product_sizes);
$product_prices_json = json_encode($product_prices);

// Fetch the existing order details
$order_sql = "SELECT o.*, i.price FROM tb_orders o LEFT JOIN tb_inventory i ON o.product_name = i.name WHERE order_id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param('i', $order_id);
$order_stmt->execute();
$order_details = $order_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order - GSL25 Inventory Management System</title>
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(to right, #000000, gray, #ffffff); /* Horizontal black and white gradient */
    margin: 0;
    padding: 0;
    color: #333; /* General text color */
    transition: background-color 0.3s, color 0.3s;
}

body.dark-mode {
    background: linear-gradient(to right, #2c3e50, #34495e, #2c3e50); /* Dark gradient for dark mode */
    color: #ecf0f1; /* Light text color */
}

.container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #ffffff; /* Container background */
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: background 0.3s;
}

.container.dark-mode {
    background: #34495e; /* Dark container background for dark mode */
}

.message {
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 20px;
    font-weight: bold;
}

.message.error {
    background-color: #f8d7da;
    color: #721c24;
}

.message.success {
    background-color: #d4edda;
    color: #155724;
}

form label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

form input[type="text"],
form input[type="number"],
form input[type="date"],
form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    color: #000; /* Black text color for readability */
    background-color: #ffffff; /* White background color for input fields */
}

body.dark-mode form input[type="text"],
body.dark-mode form input[type="number"],
body.dark-mode form input[type="date"],
body.dark-mode form select {
    color: #ffffff; /* White text color for dark mode */
    background-color: #34495e; /* Dark background color for dark mode */
}

form button {
    background-color: #007bff;
    color: #ffffff;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

form button:hover {
    background-color: #0056b3;
}

.action-buttons {
    display: flex;
    justify-content: space-between;
}

.back-button {
    background-color: #007bff;
    color: #ffffff;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    text-align: center;
    display: inline-block;
    text-decoration: none;
}

.back-button:hover {
    background-color: #ff0000; /* Red background on hover */
    color: #ffffff; /* White text color on hover */
    border: 1px solid #ff0000; /* Red border on hover */
}

.dark-mode-toggle {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #3498db;
    color: #ffffff;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    z-index: 1000;
}

.dark-mode-toggle:hover {
    background-color: #2980b9;
}

    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-2xl font-bold mb-4">Edit Order</h1>

        <!-- Display error or success message if available -->
        <?php if ($error_message): ?>
            <p class="message error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php elseif ($success_message): ?>
            <p class="message success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <!-- Form for editing the order -->
        <form action="edit-order.php?id=<?php echo htmlspecialchars($order_id); ?>" method="POST">
            <label for="customer_name">Customer Name:</label>
            <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($order_details['customer_name']); ?>" required>

            <label for="product_name">Product Name:</label>
            <select id="product_name" name="product_name" required>
                <?php while ($product_row = $product_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($product_row['name']); ?>" <?php echo ($product_row['name'] === $order_details['product_name']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($product_row['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <!-- Size Field -->
            <label for="size">Size:</label>
            <select id="size" name="size" required>
                <!-- Size options will be populated dynamically -->
            </select>

            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($order_details['quantity']); ?>" required>

            <label for="order_date">Order Date:</label>
            <input type="date" id="order_date" name="order_date" value="<?php echo htmlspecialchars($order_details['order_date']); ?>" required>

            <!-- Display Product Price -->
            <label for="price">Price:</label>
            <input type="text" id="price" name="price" value="<?php echo htmlspecialchars($order_details['price']); ?>" readonly>

            <div class="action-buttons">
                <button type="submit">Update Order</button>
                <a href="orders.php" class="back-button">Back</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productSelect = document.getElementById('product_name');
            const sizeSelect = document.getElementById('size');
            const priceInput = document.getElementById('price');
    
            const productSizes = <?php echo $product_sizes_json; ?>;
            const productPrices = <?php echo $product_prices_json; ?>;

            productSelect.addEventListener('change', function() {
                const selectedProduct = productSelect.value;
                const sizes = productSizes[selectedProduct] || [];
                const selectedPrice = productPrices[selectedProduct] || 0;

                sizeSelect.innerHTML = ''; // Clear existing sizes
                sizes.forEach(size => {
                    const option = document.createElement('option');
                    option.value = size;
                    option.textContent = size;
                    sizeSelect.appendChild(option);
                });

                // Update price for the selected product
                priceInput.value = selectedPrice;
            });

            // Initialize on page load with the current order's product
            productSelect.dispatchEvent(new Event('change'));
            
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.body.classList.add('dark-mode');
                document.querySelector('.container').classList.add('dark-mode');
            }
        });

        function toggleDarkMode() {
            const body = document.body;
            const container = document.querySelector('.container');
            body.classList.toggle('dark-mode');
            container.classList.toggle('dark-mode');

            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
            } else {
                localStorage.setItem('darkMode', 'disabled');
            }
        }
    </script>
</body>
</html>
