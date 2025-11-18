<?php
include '../includes/_base.php';
auth();

if (isset($_GET['orderId'])) {
    $orderId = $_GET['orderId'];

    // Fetch order details based on orderId
    $stm = $_db->prepare('SELECT * FROM orders WHERE orderId = ?');
    $stm->execute([$orderId]);
    $orders = $stm->fetchAll(PDO::FETCH_ASSOC);

    if (count($orders) > 0) {
        echo "<h3>Order Details for Order ID: " . htmlspecialchars($orderId) . "</h3>";
        echo "<div class='order-details-container'>";
        
        $totalAmount = 0;
        
        foreach ($orders as $order) {
            $productId = $order['productId']; // Assuming 'productId' exists in your orders table

            // Fetch the product details from the product table
            $productStm = $_db->prepare('SELECT name, photo, oriPrice FROM product WHERE id = ?');
            $productStm->execute([$productId]);
            $product = $productStm->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $productImage = htmlspecialchars($product['photo']);
                $productName = htmlspecialchars($product['name']);
                $quantity = htmlspecialchars($order['quantity']);
                $subtotal = number_format($order['subtotal'], 2);
                $originalPrice = number_format($product['oriPrice'], 2);
                $totalAmount += $order['subtotal'];

                echo "<div class='order-item'>";
                echo "<div class='product-image-container'><img src='../uploads/" . $productImage . "' alt='Product Image' class='product-image'></div>";
                echo "<div class='product-details'>";
                echo "<p class='product-name'>" . $productName . "</p>"; 
                echo "<p class='product-quantity'>x" . $quantity . "</p>";
                echo "<p class='return-policy'>15 Days Free Returns*</p>"; 
                echo "</div>";
                echo "<div class='product-price'>";
                echo "<p class='original-price'>RM" . $originalPrice . "</p>";
                echo "<p class='discounted-price'>RM" . $subtotal . "</p>";
                echo "</div>";
                echo "</div>";
            }
        }
        
        echo "</div>";
        
        // Displaying the total amount and action buttons
        echo "<div class='order-footer'>";
        echo "<div class='order-total'>Order Total: <span class='total-amount'>RM" . number_format($totalAmount, 2) . "</span></div>";  
        echo "</div>";
        
    } else {
        echo "<p>No order details found for this payment.</p>";
    }
} else {
    echo "<p>Invalid Order ID.</p>";
}
?>
