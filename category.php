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

// Initialize the category
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Initialize the search term
$search = isset($_GET['search']) ? '%' . trim($_GET['search']) . '%' : '%';

// Query to select products for the given category and search term, including price and image URL
$sql = "SELECT i.product_id, i.name, i.price, i.size, i.quantity, i.image_url
        FROM tb_inventory i
        WHERE i.category = ? AND (i.product_id LIKE ? OR i.name LIKE ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $category, $search, $search);
$stmt->execute();
$result = $stmt->get_result();

// Query to calculate the total stock for the given category
$total_stock_sql = "SELECT SUM(quantity) AS total_stock 
                    FROM tb_inventory 
                    WHERE category = ? AND (product_id LIKE ? OR name LIKE ?)";
$total_stock_stmt = $conn->prepare($total_stock_sql);
$total_stock_stmt->bind_param('sss', $category, $search, $search);
$total_stock_stmt->execute();
$total_stock_result = $total_stock_stmt->get_result();
$total_stock = $total_stock_result->fetch_assoc()['total_stock'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category); ?> - GSL25 Inventory Management System</title>
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
        .low-stock {
            color: red;
        }
        .product-image {
        width: 50px;        
        height: 50px;       
        object-fit: cover; 
        display: block;     
        margin: 0 auto;     
        }
        td {
            vertical-align: middle; 
        }
    </style>
</head>
<body>
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="mainHeader py-6 text-center">
            <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($category); ?> Inventory</h1>
        </div>
        <div class="flex justify-between items-center mb-6">
            <a href="inventory.php" class="backButton bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Back to Inventory</a>
        </div>

        <!-- Search Bar -->
        <form method="GET" action="" class="mb-6">
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
            <div class="flex items-center">
                <input type="text" name="search" placeholder="Search products by ID or name..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" class="searchInput flex-grow p-2 border border-gray-300 rounded-l-md">
                <button type="submit" class="searchButton bg-blue-500 text-white py-2 px-4 rounded-r-md">Search</button>
            </div>
        </form>

        <!-- Inventory Table -->
        <table class="inventoryTable w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border border-gray-300 p-2">Product ID</th>
                    <th class="border border-gray-300 p-2">Name</th>
                    <th class="border border-gray-300 p-2">Price</th>
                    <th class="border border-gray-300 p-2">Size</th>
                    <th class="border border-gray-300 p-2">Quantity</th>
                    <th class="border border-gray-300 p-2">Image</th>
                    <th class="border border-gray-300 p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr data-id="<?php echo htmlspecialchars($row['product_id']); ?>">
            <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($row['product_id']); ?></td>
            <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($row['name']); ?></td>
            <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($row['price']); ?></td>
            <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($row['size']); ?></td>
            <td class="border border-gray-300 p-2 quantity-cell <?php echo $row['quantity'] < 15 ? 'low-stock' : ''; ?>">
                <?php echo htmlspecialchars($row['quantity']); ?>
            </td>
            <td class="border border-gray-300 p-2">
                <!-- Display product image -->
                <?php if ($row['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Product Image" class="product-image">
                <?php else: ?>
                    <span>No Image</span>
                <?php endif; ?>
            </td>
            <td class="border border-gray-300 p-2 flex items-center justify-center">
                <i class="fas fa-edit text-blue-500 cursor-pointer mx-2" onclick="editProduct('<?php echo htmlspecialchars($row['product_id']); ?>')"></i>
                <i class="fas fa-trash text-red-500 cursor-pointer mx-2" onclick="deleteProduct('<?php echo htmlspecialchars($row['product_id']); ?>')"></i>
                <i class="fas fa-minus-circle text-orange-500 cursor-pointer mx-2" data-action="deduct" data-id="<?php echo htmlspecialchars($row['product_id']); ?>"></i>
                <i class="fas fa-plus-circle text-green-500 cursor-pointer mx-2" data-action="add" data-id="<?php echo htmlspecialchars($row['product_id']); ?>"></i>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>
</table>

<!-- Total Stock -->
<div class="totalStockFooter text-right mt-4">
    <span class="font-bold">Total Stock:</span> <?php echo $total_stock; ?>
</div>
</div>

<script>
// Handle click events for "add" and "deduct" actions
document.querySelectorAll('i[data-action]').forEach(icon => {
    icon.addEventListener('click', function () {
        const action = this.getAttribute('data-action');
        const productId = this.getAttribute('data-id');

        // Send the request to the backend
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_quantity.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Find the row and update the quantity dynamically in real-time
                    const row = document.querySelector(`tr[data-id="${productId}"]`);
                    if (row) {
                        const quantityCell = row.querySelector('.quantity-cell');
                        if (quantityCell) {
                            // Update the quantity value
                            quantityCell.textContent = response.new_quantity;

                            // Update stock color
                            if (response.new_quantity < 15) {
                                quantityCell.classList.add('low-stock');
                            } else {
                                quantityCell.classList.remove('low-stock');
                            }
                        }

                        // Update total stock in footer if necessary
                        const totalStockFooter = document.querySelector('.totalStockFooter');
                        if (totalStockFooter) {
                            totalStockFooter.textContent = `Total Stock: ${response.total_stock}`;
                        }
                    }
                } else {
                    console.error(response.message || 'Failed to update quantity');
                }
            } else {
                console.error('Server error. Please try again later.');
            }
        };
        xhr.send(`product_id=${productId}&action=${action}`);
    });
});

function editProduct(productId) {
    window.location.href = `edit_product.php?product_id=${productId}`;
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'delete_product.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    location.reload();
                } else {
                    console.error(response.message || 'Failed to delete product');
                }
            } else {
                console.error('Server error. Please try again later.');
            }
        };
        xhr.send(`product_id=${productId}`);
    }
}
</script>
</body>
</html>
