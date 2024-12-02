<?php
include('db_connection.php');

$customer_name = $_POST['customer_name'];
$total_amount = $_POST['total_amount'];
$discount = $_POST['discount'];
$final_amount = $_POST['final_amount'];
$payment_method = $_POST['payment_method'];
$cart = json_decode($_POST['cart'], true);

// Insert into tb_orders
$sql = "INSERT INTO tb_orders (customer_name, total_amount, discount, final_amount, payment_method)
        VALUES ('$customer_name', '$total_amount', '$discount', '$final_amount', '$payment_method')";
$conn->query($sql);
$order_id = $conn->insert_id;

// Insert order items
foreach ($cart as $item) {
    $product_id = $item['product_id'];
    $quantity = $item['quantity'];
    $price = $item['price'];
    $total = $price * $quantity;

    $conn->query("INSERT INTO tb_order_items (order_id, product_id, quantity, price, total)
                  VALUES ('$order_id', '$product_id', '$quantity', '$price', '$total')");

    // Update inventory
    $conn->query("UPDATE tb_inventory SET quantity = quantity - $quantity WHERE product_id = '$product_id'");
}

echo "Order successfully processed!";
?>
