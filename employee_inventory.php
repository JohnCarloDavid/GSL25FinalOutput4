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
    <title>Employee View - GSL25 Inventory Management System</title>
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
    justify-content: flex-end;
    width: 100%;
}

.searchInput {
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    margin-right: 10px;
    width: 300px;
    color: #2c3e50;
}

.searchButton, .clearButton {
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    border: none;
    outline: none;
    font-size: 1rem;
    transition: background-color 0.3s ease;
    margin-left: 10px;
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
    padding: 12px; 
    margin-top: 15px;
    border-radius: 8px;
    cursor: pointer;
    text-align: center;
    font-size: 1rem; 
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
    gap: 20px;
}

.low-stock {
    background-color: red;
    color: white;
}

/* Back Button Styling */
.backButton {
    background-color: #e74c3c;
    color: #ffffff;
    padding: 10px 20px; 
    border-radius: 8px;
    text-decoration: none;
    font-size: 1.2rem; 
    display: inline-block;
    transition: background-color 0.3s ease;
    margin-top: -23px; 
}

.backButton:hover {
    background-color: #c0392b;
}

.dropdown {
    position: relative;
}

.dropdown-menu {
    display: none;
    position: absolute;
    left: 0;
    top: 100%;
    background-color: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    width: 200px;
    z-index: 10;
}

.dropdown:hover .dropdown-menu {
    display: block;
}

.dropdown-menu a {
    padding: 10px;
    text-decoration: none;
    color: #333;
    display: block;
}

.dropdown-menu a:hover {
    background-color: #2980b9;
    color: white;
}

.logo-container {
    display: flex;
    align-items: center;
    margin-left: 10px; 
}

.logo-container img {
    width: 150px; 
    height: auto;
    margin-right: 15px; 
}

.title {
    font-size: 2rem; 
    font-weight: bold;
    color: #2980b9;
    margin-bottom: 0; 
    letter-spacing: 1px; 
    padding-left: 10px; 
}

    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="mainContent">
        <div class="mainHeader">
            <div class="logo-container">
                <img src="img/GSL25_transparent 2.png" alt="GSL25 Logo">
                <span class="title">GSL25 INVENTORY</span>
            </div>
            <div class="flex items-center justify-between mb-4">
    <!-- Search Form -->
    <form method="GET" action="employee_inventory.php" class="searchForm flex items-center space-x-2">
        <input type="text" name="search" class="searchInput p-2 border rounded-md" placeholder="Search Category..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="searchButton p-2 bg-blue-500 text-white rounded-md">Search</button>
        <?php if (!empty($search)): ?>
            <button type="button" onclick="clearSearch()" class="clearButton p-2 bg-gray-300 text-gray-700 rounded-md">Clear</button>
        <?php endif; ?>
    </form>
    
    <!-- Back Button -->
    <a href="employee_landing.php" class="backButton">Back</a>
</div>

        </div>

        <div class="flex space-x-4 my-4">
            <!-- Steel Button with Dropdown -->
            <div class="relative">
                <button id="steelButton" class="bg-gray-800 text-white py-2 px-6 rounded-lg shadow-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors duration-300 transform hover:scale-105">
                    Steel
                </button>
                <div id="steelDropdown" class="absolute left-0 mt-2 w-56 bg-white shadow-lg rounded-lg hidden z-10">
                    <?php
                    $sql = "SELECT DISTINCT category FROM tb_inventory WHERE main_category = 'CONSTRUCTION'";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $category = $row['category'];
                            echo "<a href='employee-category.php?category=" . urlencode($category) . "' class='block py-2 px-4 text-gray-800 hover:bg-gray-200 transition-colors duration-300 rounded-lg'>" . htmlspecialchars($category) . "</a>";
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
                    $sql = "SELECT DISTINCT category FROM tb_inventory WHERE main_category = 'LUMBER'";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $category = $row['category'];
                            echo "<a href='employee-category.php?category=" . urlencode($category) . "' class='block py-2 px-4 text-gray-800 hover:bg-gray-200 transition-colors duration-300 rounded-lg'>" . htmlspecialchars($category) . "</a>";
                        }
                    } else {
                        echo "<p class='text-gray-800 py-2 px-4'>No products found in Lumber.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<script>
    function clearSearch() {
        window.location.href = "employee_inventory.php";
    }
        // Steel Button Dropdown Toggle
        document.getElementById('steelButton').addEventListener('click', function() {
            document.getElementById('steelDropdown').classList.toggle('hidden');
        });

        // Lumber Button Dropdown Toggle
        document.getElementById('lumberButton').addEventListener('click', function() {
            document.getElementById('lumberDropdown').classList.toggle('hidden');
        });
    </script>
</body>
</html>
