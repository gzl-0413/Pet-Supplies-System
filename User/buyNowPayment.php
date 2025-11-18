<?php
require "../vendor/autoload.php";
require_once '../includes/_base.php';

// Stripe secret key
$stripe_secret_key = "sk_test_51PskWWL87wghZ5cHrZb8HKu91RO2FBUep3zJMZfxqlSmVjBepJx6vqCTzoZjIodPVC1CWuci3cRPpyFmHmUW61pv00Vq4LHQOL";
\Stripe\Stripe::setApiKey($stripe_secret_key);

// Start the session if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['member']) && $_SESSION['member'] !== null) {
    $member = $_SESSION['member'];
}
$status="Pending";

$shippingFee = $_SESSION['shippingFee'] ?? 5; // Use session value if available, otherwise default to 5
 

$query = $_db->prepare('SELECT orderId FROM orders ORDER BY orderId DESC LIMIT 1');
$query->execute();
$lastOrder = $query->fetch(PDO::FETCH_ASSOC);

    // Prepare the line items for Stripe
    if ($lastOrder) {
        // Extract the numeric part (assuming it starts after 'ODR')
        $lastOrderNumber = intval(substr($lastOrder['orderId'], 3));
        // Increment the number
        $nextOrderNumber = $lastOrderNumber + 1;
    } else {
        // If no orders exist, start from 1
        $nextOrderNumber = 1;
    }
    
    // Pad the number with leading zeros (e.g., ODR0001)
    $orderId = 'ODR' . str_pad($nextOrderNumber, 4, '0', STR_PAD_LEFT);



// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $productId = $_POST['product_id'] ?? 'p??';
    $productName = $_POST['product_name'] ?? 'Unknown Product';
    $quantity = (int)($_POST['quantity'] ?? 1);
    $price = (float)($_POST['price'] ?? 0.0);
    $subtotal = $price * $quantity;

 // Retrieve shipping details
 $recipientName = $_POST['recipient_name'] ?? '';
 $address = $_POST['address'] ?? '';
 $state = $_POST['state'] ?? '';
 $postalCode = $_POST['postal_code'] ?? '';
 $contactNumber = $_POST['contact_number'] ?? '';


    
    $lineItems = [
        [
            "quantity" => $quantity,
            "price_data" => [
                "currency" => "myr",
                "unit_amount" => (int)($price * 100), // Convert price to cents
                "product_data" => [
                    "name" => $productName,
                ]
            ]
        ]
    ];
    if ($shippingFee > 0) {
        $lineItems[] = [
            "quantity" => 1,
            "price_data" => [
                "currency" => "myr",
                "unit_amount" => (int)($shippingFee * 100), // Convert RM to cents
                "product_data" => [
                    "name" => "Shipping Fee",
                ]
            ]
        ];
    } 

    // Define success and cancel URLs
    $successUrl = "http://localhost:8000/user/savePayment.php?order_id=" . urlencode($orderId);
    $successUrl .= "&product_id=" . urlencode($productId);
    $successUrl .= "&product_name=" . urlencode($productName);
    $successUrl .= "&quantity=" . urlencode($quantity);
    $successUrl .= "&subtotal=" . urlencode($subtotal);
    $successUrl .= "&shipping_fee=" . urlencode($shippingFee);
    $successUrl .= "&recipient_name=" . urlencode($recipientName);
    $successUrl .= "&address=" . urlencode($address);
    $successUrl .= "&state=" . urlencode($state);
    $successUrl .= "&postal_code=" . urlencode($postalCode);
    $successUrl .= "&contact_number=" . urlencode($contactNumber); 

    // Create a Stripe Checkout session for the single product
    $checkout_session = \Stripe\Checkout\Session::create([
        "mode" => "payment",
        "success_url" => $successUrl,
        "cancel_url" => "http://localhost:8000/user/product.php",
        "line_items" => $lineItems,
    ]);

    // Redirect to the Stripe Checkout page
    http_response_code(303);
    header("Location: " . $checkout_session->url);
    exit();
} else {
    // Redirect to cart if accessed directly
    header('Location: cart.php');
    exit();
}
