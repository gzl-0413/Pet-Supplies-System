<?php
require "../vendor/autoload.php";
require_once '../includes/_base.php';
auth();
 
$stripe_secret_key = "sk_test_51PskWWL87wghZ5cHrZb8HKu91RO2FBUep3zJMZfxqlSmVjBepJx6vqCTzoZjIodPVC1CWuci3cRPpyFmHmUW61pv00Vq4LHQOL";
\Stripe\Stripe::setApiKey($stripe_secret_key);

// Start the session if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['member']) && $_SESSION['member'] !== null) {
    $member = $_SESSION['member'];
}
if (!isset($_SESSION['member']) || $_SESSION['member'] === null) {
    header('Location: index.php');  
    exit();
}
$lineItems = [];


$status="Pending";

$shippingFee = $_SESSION['shippingFee'] ?? 5;  
 
 
$query = $_db->prepare('SELECT orderId FROM orders ORDER BY orderId DESC LIMIT 1');
$query->execute();
$lastOrder = $query->fetch(PDO::FETCH_ASSOC);
 
    if ($lastOrder) {
      
        $lastOrderNumber = intval(substr($lastOrder['orderId'], 3));
      
        $nextOrderNumber = $lastOrderNumber + 1;
    } else {
      
        $nextOrderNumber = 1;
    }
     
    $orderId = 'ODR' . str_pad($nextOrderNumber, 4, '0', STR_PAD_LEFT);




 
if (is_post()) {
    
    $cartIds = $_POST['cart_ids'] ?? [];
    $productIds = $_POST['product_ids'] ?? [];
    $productNames = $_POST['product_names'] ?? [];
    $quantities = $_POST['quantities'] ?? [];
    $prices = $_POST['prices'] ?? [];
    $subtotals = $_POST['subtotals'] ?? [];

 
    $recipientName = $_POST['recipient_name'] ?? '';
    $address = $_POST['address'] ?? '';
    $state = $_POST['state'] ?? '';
    $postalCode = $_POST['postal_code'] ?? '';
    $contactNumber = $_POST['contact_number'] ?? '';

  

    foreach ($cartIds as $index => $cartId) {
        $productName = $productNames[$index] ?? 'Unknown Product';
        $productId = $productIds[$index] ?? 'Unknown Product';
        $quantity = (int)($quantities[$index] ?? 1);
        $subtotal = (int)($subtotals[$index] ?? 1);
        
        $lineItems[] = [
            "quantity" => $quantity,
            "price_data" => [
                "currency" => "myr",
                "unit_amount" => (int)($subtotal * 100), 
                "product_data" => [
                    "name" => $productName,
                ]
            ]
        ];
    }
    if ($shippingFee > 0) {
        $lineItems[] = [
            "quantity" => 1,
            "price_data" => [
                "currency" => "myr",
                "unit_amount" => (int)($shippingFee * 100),  
                "product_data" => [
                    "name" => "Shipping Fee",
                ]
            ]
        ];
    } 

    $successUrl = "http://localhost:8000/user/savePayment.php?order_id=" . urlencode($orderId);
    $successUrl .= "&cart_ids=" . urlencode(implode(',', $cartIds));
    $successUrl .= "&product_ids=" . urlencode(implode(',', $productIds));
$successUrl .= "&product_names=" . implode(',', array_map('urlencode', array_map('trim', $productNames)));
    $successUrl .= "&quantities=" . urlencode(implode(',', $quantities));
    $successUrl .= "&subtotals=" . urlencode(implode(',', $subtotals));
    $successUrl .= "&shipping_fee=" . urlencode($shippingFee);
    $successUrl .= "&recipient_name=" . urlencode($recipientName);
    $successUrl .= "&address=" . urlencode($address);
    $successUrl .= "&state=" . urlencode($state);
    $successUrl .= "&postal_code=" . urlencode($postalCode);
    $successUrl .= "&contact_number=" . urlencode($contactNumber);


   
    $checkout_session = \Stripe\Checkout\Session::create([
        "mode" => "payment",
        "success_url" => $successUrl,
        "cancel_url" => "http://localhost:8000/user/product.php",
        "line_items" => $lineItems,
    ]);

     
    http_response_code(303);
    header("Location: " . $checkout_session->url);
    exit(); 



} else {
    
    header('Location: index.php');
    exit();
}
