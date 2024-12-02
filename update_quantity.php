<?php
// Update product quantity based on action (add or deduct)
include 'db_connection.php';

if (isset($_POST['product_id']) && isset($_POST['action'])) {
    $product_id = $_POST['product_id'];
    $action = $_POST['action'];

    // Fetch the current quantity
    $query = "SELECT quantity FROM tb_inventory WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $stmt->bind_result($current_quantity);
    $stmt->fetch();
    $stmt->close();

    // Update the quantity based on the action
    if ($action == 'add') {
        $new_quantity = $current_quantity + 1;
    } elseif ($action == 'deduct') {
        $new_quantity = $current_quantity - 1;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }

    // Update the quantity in the database
    $update_query = "UPDATE tb_inventory SET quantity = ? WHERE product_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('ii', $new_quantity, $product_id);
    $stmt->execute();
    $stmt->close();

    // Fetch updated total stock
    $total_query = "SELECT SUM(quantity) AS total_stock FROM tb_inventory";
    $total_result = $conn->query($total_query);
    $total_row = $total_result->fetch_assoc();
    $total_stock = $total_row['total_stock'];

    // Return the updated data as JSON
    echo json_encode([
        'success' => true,
        'new_quantity' => $new_quantity,
        'total_stock' => $total_stock
    ]);
}
?>
