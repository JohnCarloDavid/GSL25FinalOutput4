<?php
// Include your database connection
include('db_connection.php');

// Check if the product_id and request method are valid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']); // Sanitize product_id

    // Prepare the delete query
    $delete_query = "DELETE FROM tb_inventory WHERE product_id = ?";
    $stmt = $conn->prepare($delete_query);

    if ($stmt) {
        $stmt->bind_param('i', $product_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting product.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error preparing the delete query.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

$conn->close();
?>
