<?php
require_once '../includes/_base.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => 'Invalid Promo Code.'];

if (isset($_POST['promo_code'])) {
    $promoCode = htmlspecialchars($_POST['promo_code'], ENT_QUOTES, 'UTF-8');
    $promo = getPromoCode($promoCode);

    if ($promo) {
        $totalAmount = $_SESSION['totalAmount'] ?? 0;
        $shippingFee = 5; // Fixed shipping fee
        $discountAmount = 0; // Initialize discount amount
        $newTotal = $totalAmount + $shippingFee; // Total before discount

        if ($totalAmount > 0) {
          
            if ($promo['promoType'] == 1) {
                 
                $newTotal -= $shippingFee;
                $shippingFee = 0;  
                $response['disMessage'] = "Free Shipping Applied!";
            } else {
                // Discount on the subtotal
                if ($promo['promoWay'] == 'amount') {
                    // Apply fixed amount discount
                    $discountAmount = $promo['amount'];
                } elseif ($promo['promoWay'] == 'percentage') {
                    // Apply percentage discount
                    $discountAmount = ($totalAmount * $promo['amount']) / 100;
                }

                
                $newTotal -= $discountAmount;
                $response['disMessage'] = "Discount: RM" . number_format($discountAmount, 2);
            }

          
            $_SESSION['totalAmount'] = $newTotal;
            $_SESSION['shippingFee'] = $shippingFee;
            $_SESSION['discountAmount'] = $discountAmount;

            
            $response = [
                'success' => true,
                'message' => "Promo Code Applied!",
                'newTotal' => $newTotal,  // Updated total after discount
                'shippingFee' => $shippingFee,  // Updated shipping fee
                'disMessage' => $response['disMessage'],
                'discountAmount' => $discountAmount  
            ];
        } else {
            $response['message'] = 'Cart is empty.';
        }
    }
} elseif (isset($_POST['clear_promo'])) {
  
    $totalAmount = $_SESSION['totalAmount'];
    $shippingFee = 5;  
    $newTotal = $totalAmount + $shippingFee;

    $_SESSION['totalAmount'] = $newTotal;
    $_SESSION['shippingFee'] = $shippingFee;
    $_SESSION['discountAmount'] = 0;

    $response = [
        'success' => true,
        'message' => "Promo Code Removed",
        'newTotal' => $newTotal,
        'shippingFee' => $shippingFee
    ];
}

echo json_encode($response);
?>
