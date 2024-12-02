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


// Initialize the search term
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Query to select all rows from the tb_inventory table and group by category
$sql = "SELECT category, GROUP_CONCAT(product_id, '::', name, '::', quantity SEPARATOR ';;') AS products, 
               SUM(quantity) AS total_quantity
        FROM tb_inventory";
if (!empty($search)) {
    $sql .= " WHERE name LIKE '%$search%' OR category LIKE '%$search%' OR product_id LIKE '%$search%'";
}
$sql .= " GROUP BY category";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - GSL25 Inventory Management System</title>
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            color: #2c3e50;
            background-color: #ecf0f1;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dark-mode {
            background-color: #2c3e50;
            color: #ecf0f1;
        }

        .sidebar {
            width: 260px;
            background: linear-gradient(145deg, #34495e, #2c3e50);
            color: #ecf0f1;
            padding: 30px 20px;
            height: 100vh;
            position: fixed;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: background 0.3s ease;
        }

        .sidebarHeader h2 {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .sidebarNav ul {
            list-style: none;
            padding: 0;
        }

        .sidebarNav ul li {
            margin: 1.2rem 0;
        }

        .sidebarNav ul li a {
            text-decoration: none;
            color: #ecf0f1;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .sidebarNav ul li a:hover {
            background-color: #2980b9;
        }

        .sidebarNav ul li a i {
            margin-right: 15px;
        }

        .mainContent {
            margin-left: 280px;
            padding: 30px;
            width: calc(100% - 280px);
            min-height: 100vh;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .mainHeader {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .mainHeader h1 {
            font-size: 2rem;
            margin: 0;
        }

        .searchForm {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .searchInput {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
            width: 250px;
            color: #2c3e50;
        }

        .searchButton, .clearButton {
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            outline: none;
            font-size: 1rem;
            transition: background-color 0.3s ease;
            margin-left: 5px;
        }

        .searchButton {
            background-color: #3498db;
            color: #ffffff;
        }

        .searchButton:hover {
            background-color: #2980b9;
        }

        .clearButton {
            background-color: #e74c3c;
            color: #ffffff;
        }

        .clearButton:hover {
            background-color: #c0392b;
        }

        .categoryButton {
            width: 100%;
            background-color: #2980b9;
            color: #ffffff;
            padding: 15px;
            margin-top: 10px;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            font-size: 1.2rem;
            border: none;
            outline: none;
            transition: background-color 0.3s ease;
            display: block;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .categoryButton:hover {
            background-color: #3498db;
        }

        .categoryContainer {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .low-stock {
            background-color: red;
            color: white;
        }
        /* Position logout button inside the sidebar */
        .logout-form {
            margin-top: auto; 
        }

        .logout-button {
            background-color: #e74c3c; 
            color: #ffffff; 
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            transition: background-color 0.3s;
            margin-top: 10px; 
        }

        .logout-button i {
            margin-right: 8px; 
            font-size: 1.2rem; 
        }

        .logout-button:hover {
            background-color: #c0392b; 
        }
    </style>
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
    <!-- Main Content -->
    <div class="mainContent">
        <div class="mainHeader">
            <h1></h1>
            <div class="flex space-x-4">
                <a href="add-product.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg shadow-lg hover:bg-blue-600 transition-colors duration-300">Add New Product</a>
                <a href="pos.php" class="bg-green-500 text-white py-2 px-4 rounded-lg shadow-lg hover:bg-green-600 transition-colors duration-300">Add Supply</a> <!-- POS Button -->
            </div>
        </div>

        <form method="GET" action="inventory.php" class="searchForm">
            <input type="text" name="search" class="searchInput" placeholder="Search Category..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="searchButton">Search</button>
            <?php if (!empty($search)): ?>
                <button type="button" onclick="clearSearch()" class="clearButton">Clear</button>
            <?php endif; ?>
        </form>

        <div class="flex space-x-4 my-4">
    <!-- Steel Button with Dropdown -->
    <div class="relative">
        <button id="steelButton" class="bg-gray-800 text-white py-2 px-6 rounded-lg shadow-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors duration-300 transform hover:scale-105">
            Steel
        </button>
        <div id="steelDropdown" class="absolute left-0 mt-2 w-56 bg-white shadow-lg rounded-lg hidden z-10">
            <?php
            // Assuming you have a connection to the database and a query to fetch products for the steel category
            $sql = "SELECT DISTINCT category FROM tb_inventory WHERE main_category = 'CONSTRUCTION'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $category = $row['category'];
                    echo "<a href='category.php?category=" . urlencode($category) . "' class='block py-2 px-4 text-gray-800 hover:bg-gray-200 transition-colors duration-300 rounded-lg'>" . htmlspecialchars($category) . "</a>";
                }
            } else {
                echo "<p class='text-gray-800 py-2 px-4'>No products found in Steel.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Lumber Button with Dropdown -->
    <div class="relative">
        <button id="lumberButton" class="bg-gray-800 text-white py-2 px-6 rounded-lg shadow-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors duration-300 transform hover:scale-105">
            Lumber
        </button>
        <div id="lumberDropdown" class="absolute left-0 mt-2 w-56 bg-white shadow-lg rounded-lg hidden z-10">
            <?php
            // Assuming you have a connection to the database and a query to fetch products for the lumber category
            $sql = "SELECT DISTINCT category FROM tb_inventory WHERE main_category = 'ELECTRICAL'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $category = $row['category'];
                    echo "<a href='category.php?category=" . urlencode($category) . "' class='block py-2 px-4 text-gray-800 hover:bg-gray-200 transition-colors duration-300 rounded-lg'>" . htmlspecialchars($category) . "</a>";
                }
            } else {
                echo "<p class='text-gray-800 py-2 px-4'>No products found in Lumber.</p>";
            }
            ?>
        </div>
    </div>
</div>



<script>
    // Toggle the visibility of the Steel and Lumber dropdowns
    document.getElementById('steelButton').addEventListener('click', function() {
        var steelDropdown = document.getElementById('steelDropdown');
        var lumberDropdown = document.getElementById('lumberDropdown');
        
        // Toggle Steel dropdown visibility
        steelDropdown.classList.toggle('hidden');
        
        // Close Lumber dropdown if it's open
        if (!lumberDropdown.classList.contains('hidden')) {
            lumberDropdown.classList.add('hidden');
        }
    });

    document.getElementById('lumberButton').addEventListener('click', function() {
        var lumberDropdown = document.getElementById('lumberDropdown');
        var steelDropdown = document.getElementById('steelDropdown');
        
        // Toggle Lumber dropdown visibility
        lumberDropdown.classList.toggle('hidden');
        
        // Close Steel dropdown if it's open
        if (!steelDropdown.classList.contains('hidden')) {
            steelDropdown.classList.add('hidden');
        }
    });

        function clearSearch() {
            window.location.href = 'inventory.php';
        }
    </script>
</body>
</html>