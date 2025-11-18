<?php
require_once '../includes/_base.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cartItems = [];
$totalAmount = 0;
$shippingFee = 5; // Fixed shipping fee
$discountAmount = 0; // Default discount amount
$memberId = $_SESSION['member'] ?? 0;

 
if (is_post()) {
    if (isset($_POST['product_id'])) {
        // Buy Now scenario via form submission
        $productId = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        
        // Fetch product details
        $stmt = $_db->prepare('SELECT name, oriPrice FROM product WHERE id = ?');
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            $subtotal = $product['oriPrice'] * $quantity;
            $cartItems[] = [
                'product_id' => $productId,
                'name' => htmlspecialchars($product['name']),
                'quantity' => $quantity,
                'oriPrice' => $product['oriPrice'],
                'subtotal' => $subtotal,
            ];
            $totalAmount = $subtotal;
            $_SESSION['totalAmount'] = $totalAmount;
            $_SESSION['shippingFee'] = $shippingFee;
            $_SESSION['discountAmount'] = $discountAmount;
        }
    } elseif (isset($_POST['product_ids'])) {
        // Cart checkout scenario via form submission
        $cartIds = $_POST['cart_ids'] ?? [];
        $productIds = $_POST['product_ids'] ?? [];
        $productNames = $_POST['product_names'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $prices = $_POST['prices'] ?? [];
        $subtotals = $_POST['subtotals'] ?? [];

        for ($i = 0; $i < count($productIds); $i++) {
            $cartItems[] = [
                'cart_id' => htmlspecialchars($cartIds[$i]),
                'product_id' => htmlspecialchars($productIds[$i]),
                'name' => htmlspecialchars($productNames[$i]),
                'quantity' => htmlspecialchars($quantities[$i]),
                'oriPrice' => htmlspecialchars($prices[$i]),
                'subtotal' => htmlspecialchars($subtotals[$i]),
            ];
        }
        $totalAmount = array_sum($subtotals);
        $_SESSION['totalAmount'] = $totalAmount;
        $_SESSION['shippingFee'] = $shippingFee;
        $_SESSION['discountAmount'] = $discountAmount;
    }
} elseif (isset($_GET['product_id']) && isset($_GET['quantity'])) {
    // Buy Now scenario via URL (when "Buy Now" was clicked)
    $productId = htmlspecialchars($_GET['product_id'], ENT_QUOTES, 'UTF-8');
    $quantity = intval($_GET['quantity']);

    // Fetch product details
    $stmt = $_db->prepare('SELECT name, oriPrice FROM product WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        $subtotal = $product['oriPrice'] * $quantity;
        $productName = htmlspecialchars($product['name']);
        $productPrice = $product['oriPrice'];
        $totalAmount = $subtotal;
        $_SESSION['totalAmount'] = $totalAmount;
        $_SESSION['shippingFee'] = $shippingFee;
        $_SESSION['discountAmount'] = $discountAmount;
    }
} else {
    $promoMessage = '';
}
?>

    <head>
   <title>CheckOut Page</title><

    <link href="../CSS/checkout.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    </head>

    <!-- Checkout Page Content -->
    <body>
        <div class="checkout-container">
        <button class="button-49" role="button" onclick="window.location.href = 'cart.php'">Back</button>
            <h1>Checkout</h1>
            <br>
            <div class="checkout-summary">
                <?php if (!empty($cartItems)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td><?= $item['name'] ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>RM<?= number_format($item['oriPrice'], 2) ?></td>
                                    <td>RM<?= number_format($item['subtotal'], 2) ?></td>
                                

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                
        <div class="total-amount">
        <strong>Subtotal: RM<span id="totalAmount"><?= number_format($totalAmount, 2) ?></span></strong>
        <div>
        <strong>Shipping Fee: RM<span id="shippingFee">5.00</span></strong>
    </div>
        <div id="dis-message"></div> <!-- Promo code application message -->
        <strong>Total Amount: RM<span id="totalAmountP"><?= number_format($totalAmount + 5, 2) ?></span></strong>
        <!-- <strong>Total Amount: RM<span id="totalAmountP"><?= number_format($totalAmount, 2) ?></span></strong> -->
    </div>  
        <div class="promo-code-section">
                    <h3>Have a Promo Code?</h3>
                    <input type="text" name="promo_code" id="promo_code" placeholder="Enter Promo Code">
                    <button id="applyPromo" class="button-33">Apply Promo</button>
                    <button id="cancelPromo"  class="button-33" style="display:none;">X</button>
        <div id="promo-message"></div> <!-- Promo code application message -->
    </div>

                <form method="post" action="payment.php">
                    <!-- Shipping Details Section -->
                    <div class="shipping-details-section">
                        <h3>Shipping Details</h3>
                        <div class="form-section">
                            <label for="recipient_name">Recipient Name:</label>
                            <input type="text" id="recipient_name" name="recipient_name" required pattern="^[A-Za-z\s]+$" title="Recipient name cannot include numbers.">

                            <label for="address">Address:</label>
                            <textarea id="address" name="address" rows="3" required></textarea>

                            <label for="postal_code">Postal Code:</label>
                            <input type="text" id="postal_code" name="postal_code" required pattern="^\d{5}$" title="Post code must be exactly 5 digits.">

                            <label for="state">State/Province:</label>
                            <input type="text" id="state" name="state" required pattern="^[A-Za-z\s]+$">

                    
                            <label for="contact_number">Contact Number:</label>
                            <input type="tel" id="contact_number" name="contact_number" required pattern="^(601|01)\d{8,10}$" 
                            title="Contact number must start with 601 or 01, and be between 11 to 12 digits.">
                        </div>
                    </div>  

                
                
                        <!-- Send cart details to the process_payment.php page -->
                        <?php foreach ($cartItems as $item): ?>
                
                            <input type="hidden" name="cart_ids[]"value="<?= $item['cart_id'] ?>">
                            <input type="hidden" name="product_ids[]" value="<?= $item['product_id'] ?>">
                            <input type="hidden" name="product_names[]" value="<?= $item['name'] ?>">
                            <input type="hidden" name="quantities[]" value="<?= $item['quantity'] ?>">
                            <input type="hidden" name="prices[]" value="<?= $item['oriPrice'] ?>">
                            <input type="hidden" name="subtotals[]" value="<?= $item['subtotal'] ?>">
                        <?php endforeach; ?>
                        <button type="submit" class="pay-now-btn">Proceed to Payment</button>
                    </form>
                    


                <?php elseif (isset($_GET['product_id']) && isset($_GET['quantity'])): ?>
                    <!-- Buy Now from URL query params -->
                
                    <table>
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                        
                                <td><?= $productName ?></td>
                                <td><?= $quantity ?></td>
                                <td>RM<?= number_format($productPrice, 2) ?></td>
                                <td>RM<?= number_format($totalAmount, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="total-amount">
        <strong>Subtotal: RM<span id="totalAmount"><?= number_format($totalAmount, 2) ?></span></strong>
        <div>
        <strong>Shipping Fee: RM<span id="shippingFee"></span></strong>
    </div>
        <div id="dis-message"></div> <!-- Promo code application message -->
        <strong>Total Amount: RM<span id="totalAmountP"><?= number_format($totalAmount + 5, 2) ?></span></strong>
        <!-- <strong>Total Amount: RM<span id="totalAmountP"><?= number_format($totalAmount, 2) ?></span></strong> -->
    </div>  
        <div class="promo-code-section">
                    <h3>Have a Promo Code?</h3>
                    <input type="text" name="promo_code" id="promo_code" placeholder="Enter Promo Code">
                    <button id="applyPromo">Apply Promo</button>
                    <button id="cancelPromo" style="display:none;">X</button>
        <div id="promo-message"></div> <!-- Promo code application message -->
    </div>

    <form method="post" action="buyNowPayment.php">
        <!-- Shipping Details Section -->
                    <div class="shipping-details-section">
                        <h3>Shipping Details</h3>
                        <div class="form-section">
                            <label for="recipient_name">Recipient Name:</label>
                            <input type="text" id="recipient_name" name="recipient_name" required>

                            <label for="address">Address:</label>
                            <textarea id="address" name="address" rows="3" required></textarea>

                            <label for="postal_code">Postal Code:</label>
                            <input type="text" id="postal_code" name="postal_code" required>

                            <label for="state">State/Province:</label>
                            <input type="text" id="state" name="state" required>


                            <label for="contact_number">Contact Number:</label>
                            <input type="tel" id="contact_number" name="contact_number" required>
                        </div>
                    </div>

                    <!-- Buy Now form submission (single product) -->
                
                        <input type="hidden" name="product_id" value="<?= $productId ?>">
                        <input type="hidden" name="product_name" value="<?= $productName ?>">
                        <input type="hidden" name="quantity" value="<?= $quantity ?>">
                        <input type="hidden" name="price" value="<?= $productPrice ?>">
                        <input type="hidden" name="subtotal" value="<?= $totalAmount ?>">
                        <button type="submit" class="pay-now-btn">Proceed to Payment</button>
                    </form>
                <?php else: ?>
                    <p>Your selected cart is empty. <a href="cart.php">Return to cart</a>.</p>
                <?php endif; ?>
            </div>
        </div>
    </body>
    </html>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#postal_code').on('input', function() {
        const postalCode = $(this).val();
        
        if (postalCode.length === 5) { // Ensure postal code is exactly 5 digits
            let state = '';

            if (postalCode >= 10000 && postalCode <= 19999) state = 'Penang';
            else if (postalCode >= 20000 && postalCode <= 29999) state = 'Kelantan';
            else if (postalCode >= 30000 && postalCode <= 34999) state = 'Perak';
            else if (postalCode >= 35000 && postalCode <= 39999) state = 'Perak';
            else if (postalCode >= 40000 && postalCode <= 49999) state = 'Selangor';
            else if (postalCode >= 50000 && postalCode <= 60000) state = 'Kuala Lumpur';
            else if (postalCode >= 60000 && postalCode <= 64999) state = 'Selangor';
            else if (postalCode >= 70000 && postalCode <= 72999) state = 'Negeri Sembilan';
            else if (postalCode >= 75000 && postalCode <= 75999) state = 'Melaka';
            else if (postalCode >= 80000 && postalCode <= 86999) state = 'Johor';
            else if (postalCode >= 88000 && postalCode <= 89999) state = 'Sabah';
            else if (postalCode >= 93000 && postalCode <= 98859) state = 'Sarawak';
            else if (postalCode >= 10000 && postalCode <= 10999) state = 'Labuan';
            else if (postalCode >= 95000 && postalCode <= 98999) state = 'Sarawak';
            else state = 'Unknown'; 

            $('#state').val(state);
        } else {
            $('#state').val('');
        }
    });
});

    $(document).ready(function () {
        // When the postal code input changes, auto-fill the state/province field
        $('#postal_code').on('keyup', function () {
            const postalCode = $(this).val().trim();
            
            // Check if the entered postal code matches any in the mapping
            if (postalCodeToState[postalCode]) {
                $('#state').val(postalCodeToState[postalCode]);
            } else {
                $('#state').val(''); // Clear the state if the postal code is not found
            }
        });
    });
</script>

    <script>
        $(document).ready(function() {
            // Apply promo code
            $('#applyPromo').click(function(e) {
                e.preventDefault(); // Prevent form submission

                const promoCode = $('#promo_code').val();

                $.ajax({
                    url: 'apply_promo.php', // Separate handler for applying promo
                    method: 'POST',
                    data: { promo_code: promoCode },
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            $('#promo-message').text(result.message);
                            $('#dis-message').text(result.disMessage);
                            $('#totalAmountP').text(result.newTotal.toFixed(2)); // Update total amount
                            $('#shippingFee').text(result.shippingFee.toFixed(2)); // Update shipping fee
                            
                            // Disable the promo code input and show cancel button
                            $('#promo_code').prop('disabled', true);
                            $('#applyPromo').prop('disabled', true);
                            $('#cancelPromo').show();  // Show cancel button
                        } else {
                            $('#promo-message').text(result.message);
                        }
                    }
                });
            });

            // Cancel promo code
            $('#cancelPromo').click(function(e) {
                e.preventDefault(); // Prevent form submission

                $.ajax({
                    url: 'apply_promo.php', // Use the same handler
                    method: 'POST',
                    data: { clear_promo: true },
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            $('#promo-message').text(result.message);
                            $('#dis-message').text(''); // Clear discount message
                            $('#totalAmountP').text(result.newTotal.toFixed(2)); // Restore original total
                            $('#shippingFee').text(result.shippingFee.toFixed(2)); // Restore original shipping fee
                            
                            // Re-enable promo code input and apply button
                            $('#promo_code').prop('disabled', false);
                            $('#applyPromo').prop('disabled', false);
                            $('#cancelPromo').hide();  // Hide cancel button
                            $('#promo_code').val('');  // Clear promo code input
                        }
                    }
                });
            });
        });
    </script>


    <?php
    include 'footer.php';
    ?>
