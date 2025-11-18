<?php

require_once '../includes/_base.php'; // Ensure this file contains the database connection logic

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['scope'])) { 
    $scope = $_POST['scope'];

    $currentSessionId = session_id(); // Dynamically capture the current session ID

    switch ($scope) {
        case "update":
            // Handle updating an item in the cart
            if (isset($_POST['prod_id']) && isset($_POST['prod_qty'])) {
                $prod_id = $_POST['prod_id'];
                $prod_qty = $_POST['prod_qty'];

                // Prepare the update query with the dynamic session ID
                $stmt = $_db->prepare("UPDATE temp_cart SET quantity = ? WHERE product_id = ? AND session_id = ?");
                $stmt->execute([$prod_qty, $prod_id, $currentSessionId]);

               
            } else {
                echo "Product ID or Quantity missing"; // Missing POST parameters
            }
            break;

        default:
            echo "Invalid scope"; // If the scope doesn't match any case
            break;
    }
} else {
    echo "No scope provided"; // If the scope is not set in the POST request
}

?>
