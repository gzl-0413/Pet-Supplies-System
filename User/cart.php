<?php

require_once '../includes/_base.php';
cartCheckLogin();
// Start the session if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$member = null;
if (isset($_SESSION['member']) && $_SESSION['member'] !== null) {
    $member = $_SESSION['member'];
}

// Get the current session ID
$currentSessionId = session_id();

// Function to calculate the subtotal for a specific product
function calculateSubtotal($price, $quantity) {
    return $price * $quantity;
}

// Function to get cart items for the current session
function getCartItemsBySessionId($member) {
    global $_db;
    $stmt = $_db->prepare('SELECT c.product_id, c.quantity, p.name, p.oriPrice, p.photo , c.cart_id ,p.quantity as maxQty , p.hidden 
                           FROM temp_cart c 
                           JOIN product p ON c.product_id = p.id 
                           WHERE c.member_Id = ?');
    $stmt->execute([$member]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);   
}

// Fetch cart items for the current session
$cartItems = getCartItemsBySessionId($member);
//$cartItems = getCartItemsBySessionId($currentSessionId);


// Include the header
include 'header.php';
?>

<head>
<script src="../JS/cart.js "></script>
<title>Cart Page</title>

</head>

<body>
    <div class="cart-container">
        <form method="post" action="checkout.php" id="checkout-form">            
            <h1>Shopping Cart</h1>
            <br>
            <div class="cart-header">
            <div class="checkbox-wrapper-55">   
            <label class="rocker rocker-small"> 
              
                    <input type="checkbox" id="main-checkbox"  onclick="toggleAllCheckboxes(this)" style="opacity: 0.0;">
                    <span class="switch-left">Yes</span>
                    <span class="switch-right">No</span>
                    </label>
                    </div>
            <label id="main-checkbox-label">
                    Select All
                </label>
          
                <div class="cart-item-image-header">Product</div>
                <div class="cart-item-price-header">Price</div>
                <div class="cart-item-quantity-header">Quantity</div>
                <div class="cart-item-total-header">Subtotal</div>
                <div class="cart-item-action-header">Action</div>
            </div>

            <!-- Cart Items -->
            <?php if (count($cartItems) > 0): ?>
                <?php foreach ($cartItems as $item): ?>
    <div class="cart-item <?= $item['hidden'] ? 'hidden-product' : '' ?>">
        <div class="checkbox-wrapper-26">
            <input type="checkbox" id="checkbox-<?= $item['product_id'] ?>" class="cart-item-checkbox" data-product-id="<?= htmlspecialchars($item['product_id']) ?>" <?= $item['hidden'] ? 'disabled' : '' ?> onchange="updateTotal()">
            <label for="checkbox-<?= $item['product_id'] ?>">
                <div class="tick_mark"></div>
            </label>
        </div>
        <img src="../uploads/<?= htmlspecialchars($item['photo']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
        <div class="cart-item-info">
    <div class="cart-item-details">
        <p class="product-name <?= $item['hidden'] ? 'unavailable-product' : '' ?>">
            <?= htmlspecialchars($item['name']) ?> 
            <?php if ($item['hidden']): ?>
                <span class="unavailable-label">(Unavailable)</span>
            <?php endif; ?>
        </p>
    </div>
    <div class="cart-item-price">
        RM<?= number_format($item['oriPrice'], 2) ?>
    </div>
    <div class="cart-item-quantity product_data">
        <div class="input-group">
            <button type="button" class="decrement-btn updateQty button-53" <?= $item['hidden'] ? 'disabled' : '' ?>>-</button>
            <input type="text" class="prod" value="<?= htmlspecialchars($item['product_id']) ?>">
            <input type="number" readonly value="<?= $item['quantity'] ?>" min="1" max="<?= $item['maxQty'] ?>" class="quantity-input" <?= $item['hidden'] ? 'readonly' : '' ?>>
            <button type="button" class="increment-btn updateQty button-53" <?= $item['hidden'] ? 'disabled' : '' ?>>+</button>
            <?php if ($item['hidden']): ?>
                <span class="stock-message unavailable-message">Product is no longer available.</span>
            <?php else: ?>
                <span class="stock-message unavailable-message" style="color: red; display: none;">Reached the stock limit</span>
            <?php endif; ?>
        </div>
    </div>
 

            <div class="cart-item-total" data-price="<?= $item['oriPrice'] ?>" data-quantity="<?= $item['quantity'] ?>">
                RM<?= number_format(calculateSubtotal($item['oriPrice'], $item['quantity']), 2) ?>
            </div>
            <div class="cart-item-actions">
                <button data-post="deleteFromCart.php?id=<?= $item['cart_id'] ?>" onclick="deleteFromCart(this)" data-confirm="Delete this From Cart?" class="button-33" role="button" >Delete</button>
            </div>
        </div>
    </div>
<?php endforeach; ?>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
           
            <!-- Sticky Checkout Bar -->
            <div class="sticky-checkout-bar" id="checkout-bar">
                <div class="cart-summary">
                    <p>Total (<span id="item-count">0</span> item): RM<span id="total-price">0.00</span></p>
                    <button type="button" class="checkout-btn" onclick="submitCheckoutForm()">Check Out</button>
                </div>
            </div>
        </form>
    </div>
 

</body>
</html>

<?php
// Include the footer
include 'footer.php';
?>
