<?php
// Include your database connection
include 'db_connection.php';

// Check if the user is logged in and if they are an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header("Location: login.php");
    exit();
}
// Get product_id from the URL
if (isset($_GET['product_id'])) {
    $product_id = htmlspecialchars($_GET['product_id']);

    // Retrieve product details from the database
    $query = "SELECT * FROM tb_inventory WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Product not found!";
    }
}

// Check if the form is submitted to update the product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $size = $_POST['size'];

    // Update product details in the database
    $update_query = "UPDATE tb_inventory SET name=?, category=?, quantity=?, size=? WHERE product_id=?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssisi", $name, $category, $quantity, $size, $product_id);

    if ($stmt->execute()) {
        // Redirect to the same page with a success parameter
        header("Location: edit_product.php?product_id=$product_id&success=1");
        exit;
    } else {
        echo "Error updating product.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 50px;
        }
        
        form {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 0 auto;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
            color: #007bff;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            margin-top: 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .success-message {
            font-size: 14px;
            color: green;
            margin-top: 10px;
            font-weight: bold;
            text-align: center;
        }

        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        .dark-mode {
            background-color: #2c3e50;
            color: #ecf0f1;
        }
    </style>
</head>
<body>
    <!-- HTML form to edit the product -->
    <form method="POST" action="edit_product.php?product_id=<?php echo $product_id; ?>">
        <label for="name">Product Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

        <label for="category">Category:</label>
        <input type="text" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required>

        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>

        <label for="size">Size:</label>
        <input type="text" name="size" value="<?php echo htmlspecialchars($product['size']); ?>" required>

        <input type="submit" value="Update Product">
        <a href="inventory.php" class="back-button">Back to Inventory</a>
    </form>

    <!-- Display success message if the update was successful -->
    <?php if (isset($_GET['success'])) : ?>
        <p class="success-message">Product updated successfully!</p>
    <?php endif; ?>

    <!-- Dark mode script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.body.classList.add('dark-mode');
            }
        });

        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            if (document.body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
            } else {
                localStorage.setItem('darkMode', 'disabled');
            }
        }
    </script>
</body>
</html>
