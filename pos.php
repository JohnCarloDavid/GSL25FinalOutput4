<?php
// Start session and include database connection
session_start();
include('db_connection.php');

// Check if the user is logged in and if they are an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header("Location: login.php");
    exit();
}


// Check if the user is logged in and is an admin
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Initialize search query
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare the SQL query to fetch products, applying search filter if provided
$query = "SELECT * FROM tb_inventory WHERE quantity > 0";
if ($search) {
    $search = $conn->real_escape_string($search); // Prevent SQL injection
    $query .= " AND name LIKE '%$search%'";
}

// Get all products for the POS system
$products = $conn->query($query);
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - GSL25 Inventory Management System</title>
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
        /* Additional styles for improved receipt and product images */
        .receipt-table th,
        .receipt-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .receipt-table th {
            background-color: #f2f2f2;
        }
        .product-image {
            width: 50px; /* Adjust the size as needed */
            height: 50px; /* Adjust the size as needed */
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900">
<div class="container mx-auto py-12 px-6">
    <div class="mb-6 flex justify-between items-center">
        <button onclick="window.location.href='inventory.php'" class="flex items-center bg-gray-800 hover:bg-gray-700 text-white py-2 px-4 rounded-lg shadow-md transition duration-300">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </button>
        <h1 class="text-4xl font-bold text-gray-800">Add Supply</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <!-- Product List Section -->
    <div class="bg-white p-6 rounded-lg shadow-lg h-96 overflow-y-auto relative">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Product List</h2>

        <!-- Sticky Search Bar -->
        <form method="GET" class="sticky top-0 mb-6 flex bg-white p-4 shadow-md z-10">
            <input type="text" name="search" id="searchInput" class="w-full px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search product..." oninput="filterProducts()">
        </form>
            <ul class="product-list space-y-4">
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/pr5.jpg" alt="Gi-pipes1" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Gi-pipes1 (Size: ½)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10060, 'Gi-pipes1', 260, '½', 'uploads/pr5.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/pr5.jpg" alt="Gi-pipes2" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Gi-pipes2 (Size: ¾)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10061, 'Gi-pipes2', 360, '¾', 'uploads/pr5.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/pr5.jpg" alt="Gi-pipes3" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Gi-pipes3 (Size: 1)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10062, 'Gi-pipes3', 480, '1', 'uploads/pr5.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/pr5.jpg" alt="Gi-pipes4" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Gi-pipes4 (Size: 1 1⁄4)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10063, 'Gi-pipes4', 540, '1 1⁄4', 'uploads/pr5.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/pr5.jpg" alt="Gi-pipes5" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Gi-pipes5 (Size: 1 ½)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10064, 'Gi-pipes5', 780, '1 ½', 'uploads/pr5.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/pr5.jpg" alt="Gi-pipes6" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Gi-pipes6 (Size: 2)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10065, 'Gi-pipes6', 1150, '2', 'uploads/pr5.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/flat4.jpg" alt="Flat Bar1" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Flat Bar1 (Size: 1)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10066, 'Flat Bar1', 250, '1', 'uploads/flat4.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/flat4.jpg" alt="Flat Bar2" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Flat Bar2 (Size: 1 ½)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10067, 'Flat Bar2', 390, '1 ½', 'uploads/flat4.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/flat4.jpg" alt="Flat Bar3" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Flat Bar3 (Size: 2)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10068, 'Flat Bar3', 460, '2', 'uploads/flat4.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/23.-SS-ANGLE-BAR.jpg" alt="Angle Bar1" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Angle Bar1 (Size: 1x1)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10069, 'Angle Bar1', 350, '1x1', 'uploads/23.-SS-ANGLE-BAR.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/23.-SS-ANGLE-BAR.jpg" alt="Angle Bar2" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Angle Bar2 (Size: 1½ x 1½)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10070, 'Angle Bar2', 480, '1½ x 1½', 'uploads/23.-SS-ANGLE-BAR.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/23.-SS-ANGLE-BAR.jpg" alt="Angle Bar3" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Angle Bar3 (Size: 2x2)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10071, 'Angle Bar3', 590, '2x2', 'uploads/23.-SS-ANGLE-BAR.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/23.-SS-ANGLE-BAR.jpg" alt="Angle Bar4 (Green)" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Angle Bar4 (Green) (Size: 2x2)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10072, 'Angle Bar4 (Green)', 700, '2x2', 'uploads/images.jfif')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/23.-SS-ANGLE-BAR.jpg" alt="Angle Bar5 (Green)" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Angle Bar5 (Green) (Size: 1x1)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10073, 'Angle Bar5 (Green)', 420, '1x1', 'uploads/images.jfif')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/23.-SS-ANGLE-BAR.jpg" alt="Angle Bar6 (Green)" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Angle Bar6 (Green) (Size: 1½ x 1½ )</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10074, 'Angle Bar6 (Green)', 580, '1½ x 1½ ', 'uploads/images.jfif')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/steel-purlins-min.jpg" alt="Purlins1 (1.2)" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Purlins1 (1.2) (Size: 2X3)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10075, 'Purlins1 (1.2)', 360, '2X3', 'uploads/steel-purlins-min.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/steel-purlins-min.jpg" alt="Purlins2 (1.5)" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Purlins2 (1.5) (Size: 2x3)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10076, 'Purlins2 (1.5)', 460, '2x3', 'uploads/steel-purlins-min.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/steel-purlins-min.jpg" alt="Purlins3 (1.2)" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Purlins3 (1.2) (Size: 2x4)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10077, 'Purlins3 (1.2)', 420, '2x4', 'uploads/steel-purlins-min.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/steel-purlins-min.jpg" alt="Purlins4 (1.5)" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Purlins4 (1.5) (Size: 2x4)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10078, 'Purlins4 (1.5)', 520, '2x4', 'uploads/steel-purlins-min.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/steel-purlins-min.jpg" alt="Purlins5 (1.2)" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Purlins5 (1.2) (Size: 2x6)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10079, 'Purlins5 (1.2)', 560, '2x6', 'uploads/steel-purlins-min.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/steel-purlins-min.jpg" alt="Purlins6 (1.5)" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Purlins6 (1.5) (Size: 2x6)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10080, 'Purlins6 (1.5)', 640, '2x6', 'uploads/steel-purlins-min.jpg')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/steelmatting.png" alt="Steel Matting1" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Steel Matting1 (Size: 6)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10081, 'Steel Matting1', 650, '6', 'uploads/images (1).jfif')">
                            Add
                        </button>
                                            </li>
                                    <li class="flex items-center bg-gray-50 p-4 rounded-lg shadow hover:bg-gray-100 transition duration-300">
                        <img src="uploads/steelmatting.png" alt="Steel Matting2" class="product-image rounded-lg mr-4">
                        <div class="flex-grow">
                            <p class="font-medium text-gray-800">Steel Matting2 (Size: 4)</p>
                        </div>
                        <button class="ml-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="openModal(10082, 'Steel Matting2', 420, '4', 'uploads/images (1).jfif')">
                            Add
                        </button>
                        </li>
        </ul>
        </div>
        
        <!-- Cart Summary Section -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <!-- Customer Name -->
            <div class="mb-4">
                <label for="customerName" class="block font-semibold text-gray-600 mb-1">Name:</label>
                <input type="text" id="customerName" placeholder="Enter customer name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Date -->
            <div class="mb-4">
                <label for="transactionDate" class="block font-semibold text-gray-600 mb-1">Date:</label>
                <input type="date" id="transactionDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>


            <table class="w-full border border-gray-300">
                <thead>
                    <tr>
                        <th class="border px-2 py-2 bg-gray-200">ProductName</th>
                        <th class="border px-2 py-2 bg-gray-200">Size</th>
                        <th class="border px-2 py-2 bg-gray-200">Quantity</th>
                        <th class="border px-2 py-2 bg-gray-200">Actions</th>
                        <th class="border px-2 py-2 bg-gray-200"></th>
                    </tr>
                </thead>
                <tbody id="cartItems"></tbody>
            </table>
            <button class="mt-4 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="showReceipt()">Print Quote</button>
        </div>
    </div>

    <!-- Product Modal -->
    <div id="productModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center h-full">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-semibold mb-4" id="modalProductName"></h2>
                <img id="modalProductImage" class="w-32 h-32 mb-4" alt="Product Image">
                <p id="modalProductSize" class="mb-2"></p>
                <label class="block mb-2">Quantity:</label>
                <input id="modalProductQuantity" type="number" min="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button class="mt-4 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-300" id="addToCartButton">Add to Cart</button>
                <button class="mt-4 bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div id="receiptModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center h-full">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <p id="receiptCompanyName" class="font-semibold">GSL25 Construction Supplies</p>
                <p id="receiptCustomerName" class="mb-2"></p>
                <p id="receiptDate" class="mb-4"></p>
                <table class="w-full receipt-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Size</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody id="receiptItems"></tbody>
                </table>
                <button class="mt-4 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="printReceipt()">Print Quote</button>
                <button class="mt-4 bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg transition duration-300" onclick="closeReceipt()">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];

function openModal(productId, productName, price, size, imageUrl) {
    document.getElementById('modalProductName').innerText = productName;
    document.getElementById('modalProductSize').innerText = `Size: ${size}`;
    document.getElementById('modalProductImage').src = imageUrl;
    document.getElementById('modalProductQuantity').value = 1; // Reset quantity
    document.getElementById('productModal').classList.remove('hidden');
    document.getElementById('addToCartButton').onclick = function () {
        addToCart(productId, productName, size, price);
        closeModal();
    };
}

function closeModal() {
    document.getElementById('productModal').classList.add('hidden');
}

function addToCart(productId, productName, size, price) {
    const quantity = parseInt(document.getElementById('modalProductQuantity').value, 10);
    const existingProductIndex = cart.findIndex(item => item.id === productId);

    if (existingProductIndex > -1) {
        cart[existingProductIndex].quantity += quantity; // Update quantity if already in cart
    } else {
        cart.push({ id: productId, name: productName, size: size, quantity: quantity, price: price });
    }
    renderCart();
}

function renderCart() {
    const cartItemsContainer = document.getElementById('cartItems');
    cartItemsContainer.innerHTML = ''; // Clear existing items

    cart.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="border px-2 py-2">${item.name}</td>
            <td class="border px-2 py-2">${item.size}</td>
            <td class="border px-2 py-2">${item.quantity}</td>
            <td class="border px-2 py-2">
                <button class="text-red-500" onclick="removeFromCart('${item.id}')">Remove</button>
            </td>
        `;
        cartItemsContainer.appendChild(row);
    });
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId); // Remove item from cart
    renderCart();
}

function showReceipt() {
    const customerName = document.getElementById('customerName').value.trim();
    const transactionDate = document.getElementById('transactionDate').value.trim();

    if (!customerName || !transactionDate) {
        alert('Please fill out all required fields for the receipt.');
        return;
    }

    const receiptItemsContainer = document.getElementById('receiptItems');
    const receiptCompanyName = 'GSL25 Construction Supplies'; // Company Name

    document.getElementById('receiptCompanyName').innerText = receiptCompanyName;
    document.getElementById('receiptCustomerName').innerText = customerName;
    document.getElementById('receiptDate').innerText = transactionDate;

    receiptItemsContainer.innerHTML = ''; // Clear existing items

    cart.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="border px-2 py-2">${item.name}</td>
            <td class="border px-2 py-2">${item.size}</td>
            <td class="border px-2 py-2">${item.quantity}</td>
        `;
        receiptItemsContainer.appendChild(row);
    });

    document.getElementById('receiptModal').classList.remove('hidden');
}

function printReceipt() {
    const receiptContent = `
        <html>
            <head>
                <title>Receipt</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                    }
                    .receipt-header {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 20px;
                    }
                    th, td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #f2f2f2;
                    }
                </style>
            </head>
            <body>
                <div class="receipt-header">
                    <h2>GSL25 Construction Supplies</h2>
                    <p>Name: ${document.getElementById('receiptCustomerName').innerText}</p>
                    <p>Date: ${document.getElementById('receiptDate').innerText}</p>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Size</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${Array.from(document.getElementById('receiptItems').children)
                            .map(row => row.outerHTML)
                            .join('')}
                    </tbody>
                </table>
            </body>
        </html>
    `;

    const newWindow = window.open('', '', 'height=600,width=800');
    newWindow.document.write(receiptContent);
    newWindow.document.close();
    newWindow.print();
}


function closeReceipt() {
    document.getElementById('receiptModal').classList.add('hidden');
}

function filterProducts() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const productList = document.querySelectorAll(".product-list li");

    productList.forEach(product => {
        const productName = product.textContent || product.innerText;
        product.style.display = productName.toLowerCase().includes(input) ? "" : "none";
    });
}

// Set today's date as the default value for the date input field
function setTodayDate() {
        const today = new Date();
        const formattedDate = today.toISOString().split('T')[0]; // Format: YYYY-MM-DD
        document.getElementById('transactionDate').value = formattedDate;
    }

    // Call the function on page load
    window.onload = setTodayDate;
</script>

</body>
</html>