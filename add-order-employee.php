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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $_POST['customer_name'];
    $order_date = $_POST['order_date'];

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Iterate over products to process each item in the order
        foreach ($_POST['product_name'] as $index => $product_name) {
            $size = $_POST['size'][$index];
            $quantity = $_POST['quantity'][$index];
            $status = isset($_POST['status'][$index]) ? $_POST['status'][$index] : 'Pending';

            // Fetch the product price from the inventory table
            $price_sql = "SELECT price FROM tb_inventory WHERE name = ? AND size = ?";
            $price_stmt = $conn->prepare($price_sql);
            $price_stmt->bind_param('ss', $product_name, $size);
            $price_stmt->execute();
            $price_result = $price_stmt->get_result();

            if ($price_result->num_rows === 0) {
                throw new Exception("Product price not found for $product_name ($size).");
            }

            $price_row = $price_result->fetch_assoc();
            $price = $price_row['price']; // Price of the selected product
            
            // Insert each product into the orders table
            $sql = "INSERT INTO tb_orders (customer_name, product_name, size, quantity, order_date, status) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssiss', $customer_name, $product_name, $size, $quantity, $order_date, $status);

            if (!$stmt->execute()) {
                throw new Exception("Error adding order.");
            }

            // Retrieve and update inventory quantity
            $inventory_sql = "SELECT quantity FROM tb_inventory WHERE name = ? AND size = ?";
            $inventory_stmt = $conn->prepare($inventory_sql);
            $inventory_stmt->bind_param('ss', $product_name, $size);
            $inventory_stmt->execute();
            $inventory_result = $inventory_stmt->get_result();

            if ($inventory_result->num_rows === 0) {
                throw new Exception("Product not found in inventory.");
            }

            $inventory_row = $inventory_result->fetch_assoc();
            $current_quantity = $inventory_row['quantity'];

            if ($current_quantity < $quantity) {
                throw new Exception("Not enough stock for $product_name ($size).");
            }

            $new_quantity = $current_quantity - $quantity;
            $update_inventory_sql = "UPDATE tb_inventory SET quantity = ? WHERE name = ? AND size = ?";
            $update_inventory_stmt = $conn->prepare($update_inventory_sql);
            $update_inventory_stmt->bind_param('iss', $new_quantity, $product_name, $size);

            if (!$update_inventory_stmt->execute()) {
                throw new Exception("Error updating inventory.");
            }
        }

        $conn->commit();
        header('Location: employee-orders.php');
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('" . $e->getMessage() . "'); window.location.href = 'add-order-employee.php';</script>";
    }
}

// Fetch product names and prices for the select dropdown
$product_sql = "SELECT DISTINCT name FROM tb_inventory";
$product_result = $conn->query($product_sql);

// Fetch size and price information for each product
$size_sql = "SELECT name, size, price FROM tb_inventory";
$size_result = $conn->query($size_sql);

$product_sizes = [];
while ($row = $size_result->fetch_assoc()) {
    $product_name = $row['name'];
    $size = $row['size'];
    $price = $row['price'];
    
    if (!isset($product_sizes[$product_name])) {
        $product_sizes[$product_name] = [];
    }
    $product_sizes[$product_name][] = ['size' => $size, 'price' => $price];
}
$product_sizes_json = json_encode($product_sizes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Order - GSL25 Inventory Management System</title>
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>
<body class="bg-gray-50">
<div class="container mx-auto px-4 py-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Left Column: Product List with Search Bar -->
        <div class="border p-4 bg-white rounded-md shadow-md">
            <h2 class="text-lg font-bold mb-4">LIST ITEMS</h2>
            <div class="sticky top-0 z-10 bg-white p-4 shadow-md">
                <input type="text" name="search" id="search" placeholder="Search products..." class="border p-2 rounded w-full mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" onkeyup="filterProducts()" />
            </div>

            <div id="productList" class="overflow-y-auto max-h-[400px] scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 mt-4">
                <?php 
                while ($row = $product_result->fetch_assoc()) {
                    $product_name = $row['name']; 
                    if (isset($product_sizes[$product_name])) {
                        foreach ($product_sizes[$product_name] as $product_details) {
                            $size = $product_details['size'];
                            $price = $product_details['price'];
                ?>
                <div class="product-card flex justify-between items-center border p-3 mb-3 bg-gray-100 rounded cursor-pointer hover:bg-gray-200 transition-all duration-300" data-product="<?php echo $product_name; ?>" data-size="<?php echo $size; ?>" data-price="<?php echo $price; ?>" onclick="addProductToForm('<?php echo $product_name; ?>', '<?php echo $size; ?>', <?php echo $price; ?>)">
                    <div class="flex-1 text-sm font-medium"><?php echo $product_name; ?></div>
                    <div class="flex-1 text-sm"><?php echo $size; ?></div>
                    <div class="flex-1 text-sm text-right">₱<?php echo number_format($price, 2); ?></div>
                </div>
                <?php
                        }
                    }
                }
                ?>
            </div>
        </div>

        <!-- Right Column: Customer Input Form -->
        <div class="border p-6 bg-white rounded-md shadow-md">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Customer Information</h1>
            <form action="add-order-employee.php" method="POST" id="customer-form">
                <label for="customer_name" class="text-sm font-medium text-gray-700">Customer Name:</label>
                <input type="text" id="customer_name" name="customer_name" required pattern="[A-Za-z\s\-']+" title="Customer name should only contain letters, spaces, hyphens, or apostrophes." class="border border-gray-300 text-gray-800 rounded p-3 w-full mt-2 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter customer name">

                <label for="order_date" class="text-sm font-medium text-gray-700">Order Date:</label>
                <input type="date" id="order_date" name="order_date" required class="border border-gray-300 text-gray-800 rounded p-3 w-full mt-2 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Select order date">

                <div id="selectedProducts" class="flex flex-wrap justify-between mt-2 space-y-2 overflow-auto p-4 bg-white shadow-md rounded-lg border border-gray-300">
                </div>

                <div class="total-amount mt-4">
                    <label for="total_amount" class="text-sm font-medium text-gray-700">Total Amount:</label>
                    <input type="text" id="total_amount" name="total_amount" readonly class="border border-gray-300 text-gray-800 rounded p-3 w-full mt-2 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="₱0.00">

                    <label for="amount_paid" class="text-sm font-medium text-gray-700">Amount Paid:</label>
                    <input type="number" id="amount_paid" name="amount_paid" required onchange="calculateChange()" class="border border-gray-300 text-gray-800 rounded p-3 w-full mt-2 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter amount paid">

                    <!-- Change Display -->
                    <label for="change" class="text-sm font-medium text-gray-700">Change:</label>
                    <input type="text" id="change" name="change" readonly class="border border-gray-300 text-gray-800 rounded p-3 w-full mt-2 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="₱0.00">

                    <!-- Insufficient Balance Message -->
                    <p id="insufficient-message" class="text-red-500 hidden mt-2">Insufficient Balance. Please enter a higher amount.</p>
                <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 rounded-md font-semibold shadow-lg hover:from-blue-600 hover:to-blue-500 transform transition-all duration-300 mt-6 mb-6">
                    Submit Order
                </button>

                <a href="employee_landing.php" class="w-full bg-gradient-to-r from-gray-500 to-gray-600 text-white py-3 rounded-md font-semibold text-center shadow-lg hover:from-gray-600 hover:to-gray-500 transform transition-all duration-300 mt-2 mb-2 block">
                    Back
                </a>
            </form>
        </div>
    </div>
</div>


<script>
        // Get the product list container and the scroll guide
        const productList = document.getElementById('productList');
        const scrollGuide = document.createElement('div');
        scrollGuide.classList.add('scroll-guide');
        scrollGuide.innerText = 'Scroll down for more products';
        document.body.appendChild(scrollGuide);

        // Show scroll guide when the user scrolls down
        productList.addEventListener('scroll', function() {
            if (productList.scrollTop > 100) {
                scrollGuide.classList.add('visible');
            } else {
                scrollGuide.classList.remove('visible');
            }
        });

        // Set the order date field to today's date
        document.getElementById('order_date').value = new Date().toISOString().split('T')[0];

        function addProductToForm(productName, size, price) {
    var selectedProductsContainer = document.getElementById('selectedProducts');
    
    // Check if the product already exists in the selected products container
    var existingProduct = Array.from(selectedProductsContainer.getElementsByClassName('product-entry')).find(function(entry) {
        return entry.querySelector('input[name="product_name[]"]').value === productName &&
               entry.querySelector('input[name="size[]"]').value === size;
    });

    // If product exists, update the quantity
    if (existingProduct) {
        var quantityInput = existingProduct.querySelector('input[name="quantity[]"]');
        quantityInput.value = parseInt(quantityInput.value) + 1; // Increase quantity by 1
    } else {
        // If product does not exist, create a new entry
        var productEntry = document.createElement('div');
        productEntry.classList.add('product-entry', 'mb-3', 'flex', 'items-center', 'border', 'border-gray-300', 'p-3', 'bg-gray-200', 'rounded-lg', 'shadow-sm');

        // Product Name
        var productNameInput = document.createElement('input');
        productNameInput.type = 'text';
        productNameInput.name = 'product_name[]';
        productNameInput.value = productName;
        productNameInput.readOnly = true;
        productEntry.appendChild(productNameInput);

        // Product Size
        var sizeInput = document.createElement('input');
        sizeInput.type = 'text';
        sizeInput.name = 'size[]';
        sizeInput.value = size;
        sizeInput.readOnly = true;
        productEntry.appendChild(sizeInput);

        // Product Price
        var priceInput = document.createElement('input');
        priceInput.type = 'text';
        priceInput.name = 'price[]';
        priceInput.value = '₱' + price.toFixed(2);
        priceInput.readOnly = true;
        productEntry.appendChild(priceInput);

        // Quantity Field
        var quantityInput = document.createElement('input');
        quantityInput.type = 'number';
        quantityInput.name = 'quantity[]';
        quantityInput.min = 1;
        quantityInput.value = 1; // Default quantity
        quantityInput.required = true;
        quantityInput.addEventListener('input', updateTotalAmount); // Update total when quantity changes
        productEntry.appendChild(quantityInput);

        // Remove Button
        var removeButton = document.createElement('button');
        removeButton.type = 'button'; // Prevent form submission
        removeButton.classList.add('ml-4', 'bg-red-500', 'text-white', 'px-2', 'py-1', 'rounded', 'hover:bg-red-600');
        removeButton.innerText = 'Remove';
        removeButton.addEventListener('click', function() {
            selectedProductsContainer.removeChild(productEntry); // Remove the product entry
            updateTotalAmount(); // Update total amount after removal
        });
        productEntry.appendChild(removeButton);

        // Append product entry to selected products container
        selectedProductsContainer.appendChild(productEntry);
    }

    // Update total amount
    updateTotalAmount();
}
        // Function to calculate the total amount of selected products
        function updateTotalAmount() {
            var totalAmount = 0;
            var prices = document.querySelectorAll('input[name="price[]"]');
            var quantities = document.querySelectorAll('input[name="quantity[]"]');

            for (var i = 0; i < prices.length; i++) {
                var price = parseFloat(prices[i].value.replace('₱', '').replace(',', ''));
                var quantity = parseInt(quantities[i].value);
                totalAmount += price * quantity;
            }

            // Update the total amount input field
            document.getElementById('total_amount').value = '₱' + totalAmount.toFixed(2);
            calculateChange();  // Recalculate change whenever total amount changes
        }

        // Function to calculate change and handle insufficient balance
function calculateChange() {
    var totalAmount = parseFloat(document.getElementById('total_amount').value.replace('₱', '').replace(',', ''));
    var amountPaid = parseFloat(document.getElementById('amount_paid').value);
    var change = amountPaid - totalAmount;

    // Check if the amount paid is less than the total amount
    if (amountPaid < totalAmount) {
        document.getElementById('change').value = 'Insufficient Balance'; // Show error message
        document.getElementById('change').style.color = 'red';  // Change text color to red
        document.getElementById('saveOrderButton').disabled = true; // Disable Save Order button
        document.getElementById('insufficient-message').classList.remove('hidden'); // Show insufficient balance message
    } else if (!isNaN(change)) {
        document.getElementById('change').value = '₱' + change.toFixed(2); // Display the change
        document.getElementById('change').style.color = 'black';  // Reset text color to black
        document.getElementById('saveOrderButton').disabled = false; // Enable Save Order button if payment is sufficient
        document.getElementById('insufficient-message').classList.add('hidden'); // Hide insufficient balance message
    } else {
        document.getElementById('change').value = '₱0.00'; // Default value if no amount is paid
        document.getElementById('insufficient-message').classList.add('hidden'); // Hide insufficient balance message
    }
}

        // Event listener to calculate change whenever the amount paid changes
        document.getElementById('amount_paid').addEventListener('input', function() {
            calculateChange();
        });
    </script>
</body>
</html>
