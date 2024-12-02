<?php
session_start();

if (isset($_POST['submit'])) {
    include('db_connection.php');

    // Capture the form data and convert to uppercase where needed
    $main_category = strtoupper($_POST['main_category']);
    $name = strtoupper($_POST['name']);
    $category = strtoupper($_POST['category']);
    $quantity = $_POST['quantity'];
    $size = $_POST['size'];
    $price = $_POST['price'];

    // Handle the uploaded image file
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $uploadOk = true;

   // Check if the user is logged in and if they are an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header("Location: login.php");
    exit();
}

    // Validate the uploaded file type
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        $message = "<p class='message error'>File is not an image.</p>";
        $uploadOk = false;
    }

    // Check file extension
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        $message = "<p class='message error'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</p>";
        $uploadOk = false;
    }

    // Upload the file if validation passes
    if ($uploadOk && move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Prepare the SQL query to insert the data into the database
        $sql = "INSERT INTO tb_inventory (main_category, name, category, quantity, size, price, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssiss', $main_category, $name, $category, $quantity, $size, $price, $target_file);

        // Execute the query and handle success/error
        if ($stmt->execute()) {
            header("Location: inventory.php?message=success");
            exit;
        } else {
            $message = "<p class='message error'>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } else {
        $message = "<p class='message error'>Error uploading image.</p>";
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - GSL25 Inventory Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #ffffff;
            margin: 0;
            padding: 0;
            color: #333; 
            transition: background-color 0.3s, color 0.3s;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff; 
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: background 0.3s;
        }
        .container.dark-mode {
            background: #34495e; 
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
            color: #000; 
            background-color: #ffffff; 
        }
        .button-container {
            display: flex;
            justify-content: flex-start; 
            margin-top: 20px;
        }
        .button-container a,
        .button-container input[type="submit"] {
            margin: 0;
            margin-right: 10px; 
        }
        .button-container input[type="submit"] {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .button-container input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .button-container a {
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
            margin-left: 465px;
        }
        .button-container a:hover {
            color: #ffffff;
            border: 1px solid  #007bff;
        }
    </style>
    <script>
        // Automatically convert inputs to uppercase
        function toUpperCaseInput(event) {
            event.target.value = event.target.value.toUpperCase();
        }

        // JavaScript function to update the category options based on main category
        function updateCategoryOptions() {
            const mainCategory = document.getElementById('main_category').value;
            const categorySelect = document.getElementById('category');
            categorySelect.innerHTML = '';  // Clear existing options

            let options = [];

            if (mainCategory === 'CONSTRUCTION') {
                options = ['STEEL', 'GALVANIZED', 'ROOFING']; // Example categories for Steel
            } else if (mainCategory === 'ELECTRICAL') {
                options = ['WIRING', 'LIGHTING', 'CABLE']; // Example categories for Lumber
            }

            options.forEach(function(option) {
                const optElement = document.createElement('option');
                optElement.value = option;
                optElement.textContent = option;
                categorySelect.appendChild(optElement);
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <h1 class="text-2xl font-bold mb-4">Add New Product</h1>
        <?php
        if (isset($message)) {
            echo $message;
        }
        ?>
        <form action="add-product.php" method="post" enctype="multipart/form-data">
            <label for="main_category">Main Category:</label>
            <select id="main_category" name="main_category" required onchange="updateCategoryOptions()">
                <option value="">Select Main Category</option>
                <option value="CONSTRUCTION">STEEL</option>
                <option value="ELECTRICAL">LUMBER</option>
            </select>
            
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required oninput="toUpperCaseInput(event)">

            <label for="category">Category:</label>
            <input type="text" id="category" name="category" required oninput="toUpperCaseInput(event)">

            <label for="size">Size:</label>
            <input type="text" id="size" name="size" required>

            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" required>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" required>

            <label for="image">Image:</label>
            <input type="file" id="image" name="image" accept="image/*" required>

            <div class="button-container">
                <input type="submit" name="submit" value="Add Product">
                <a href="inventory.php" class="back-button">Back to Inventory</a>
            </div>
        </form>

    </div>
</body>
</html>
