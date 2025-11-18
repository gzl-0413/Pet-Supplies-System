<?php
require_once '../includes/_base.php';

if (isset($_SESSION['member']) && $_SESSION['member'] !== null) {
    $member = $_SESSION['member'];


    $query = "SELECT email FROM member WHERE member_id = ?";
    $stmt = $_db->prepare($query);
    $stmt->execute([$member]);
    $email = $stmt->fetchColumn();

}else {
        header('Location: login.php');  
        exit();
  }   

  $orderId = isset($_GET['order_id']) ? $_GET['order_id'] : null;
  $shippingFee = isset($_GET['shipping_fee']) ? (float)$_GET['shipping_fee'] : ($_SESSION['shippingFee'] ?? 5);
  $recipientName = urldecode($_GET['recipient_name']);
  $address = urldecode($_GET['address']);
  $state = urldecode($_GET['state']);
  $postalCode = urldecode($_GET['postal_code']);
  $contactNumber = urldecode($_GET['contact_number']);

  $cartItems = [];
 
if (isset($_GET['cart_ids'], $_GET['product_names'], $_GET['quantities'], $_GET['subtotals'])) {
    $cartIds = explode(',', $_GET['cart_ids']);
    $productIds = explode(',', $_GET['product_ids']);
    $productNames = array_map('urldecode', explode(',', $_GET['product_names']));
    $quantities = explode(',', $_GET['quantities']);
    $subtotals = explode(',', $_GET['subtotals']);

    

    try {

        foreach ($cartIds as $index => $cartId) {
            $productName = htmlspecialchars($productNames[$index], ENT_QUOTES, 'UTF-8');
            $productId = $productIds[$index];
            $quantity = (int)$quantities[$index];
            $subtotal = (float)$subtotals[$index];


           //email data
            $cartItems[] = [
                'productName' => htmlspecialchars($productNames[$index], ENT_QUOTES, 'UTF-8'),
                'productId' => $productIds[$index],
                'quantity' => (int)$quantities[$index],
                'subtotal' => (float)$subtotals[$index]
            ];
                //save order
            $insertOrderQuery = "INSERT INTO orders (orderId, productId, productName, quantity, subtotal, userId, status, recipient, address, postCode, state, contactNumber) 
                                 VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?, ?, ?, ?)";
            $stmt = $_db->prepare($insertOrderQuery);
            $stmt->execute([$orderId, $productId, $productName, $quantity, $subtotal, $member, $recipientName, $address, $postalCode, $state, $contactNumber]);
        }


        //save payment
        $totalAmount = array_sum(array_map('floatval', $subtotals));
        $finalTotal = $totalAmount + $shippingFee;
       
        $transactionId = 'TRX' . substr(uniqid(), -8);  

        $insertPaymentQuery = "INSERT INTO payment (transactionId, userId, orderId, subtotal, shipping, status) VALUES (?, ?, ?, ?,?,'successful')";
        $stmt = $_db->prepare($insertPaymentQuery);
        $stmt->execute([$transactionId, $member, $orderId, $finalTotal,$shippingFee]);

        // time
        $timestampQuery = "SELECT `timestamp` FROM payment WHERE transactionId = ?";
        $stmt = $_db->prepare($timestampQuery);
        $stmt->execute([$transactionId]);
        $timestamp = $stmt->fetchColumn();
      
        foreach ($cartIds as $index => $cartId) {
            $productName = htmlspecialchars($productNames[$index], ENT_QUOTES, 'UTF-8');
            $quantity = (int) $quantities[$index];
 
            $deleteCartQuery = "DELETE FROM temp_cart WHERE cart_id = ?";
            $stmt = $_db->prepare($deleteCartQuery);
            $stmt->execute([$cartId]);

            $updateStockQuery = "UPDATE product SET quantity = quantity - ? WHERE name = ?";
            $stmt = $_db->prepare($updateStockQuery);
            $stmt->execute([$quantity, $productName]);
}

receipt_email($email, $recipientName, $orderId, $cartItems, $shippingFee, $finalTotal, $address, $postalCode, $state, $contactNumber, $transactionId, $timestamp);


header("Location: success.php?success=1");
        exit();
    } catch (Exception $e) {
        
        error_log("Error processing payment: " . $e->getMessage());
         header('Location: error.php');
        exit();
    }
}elseif (isset($_GET['product_name'], $_GET['quantity'], $_GET['subtotal'])) {
    $productName = htmlspecialchars(urldecode($_GET['product_name']), ENT_QUOTES, 'UTF-8');
    $quantity = (int)$_GET['quantity'];
    $subtotal = (int)$_GET['subtotal'];
    $productId = htmlspecialchars(urldecode($_GET['product_id']), ENT_QUOTES, 'UTF-8');

   
    $transactionId = 'TRX' . substr(uniqid(), -8);
    $finalTotal = $subtotal + $shippingFee;
    try {
    
       $insertOrderQuery = "INSERT INTO orders (orderId, productId, productName, quantity, subtotal, userId, status, recipient, address, postCode, state, contactNumber) 
                                 VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?, ?, ?, ?)";
        $stmt = $_db->prepare($insertOrderQuery);
            $stmt->execute([$orderId, $productId, $productName, $quantity, $subtotal, $member, $recipientName, $address, $postalCode, $state, $contactNumber]);
  

      
        $insertPaymentQuery = "INSERT INTO payment (transactionId, userId, orderId, subtotal, shipping, status) VALUES (?, ?, ?, ?,?,'successful')";
        $stmt = $_db->prepare($insertPaymentQuery);
        $stmt->execute([$transactionId, $member, $orderId, $finalTotal,$shippingFee]);

       
        $updateStockQuery = "UPDATE product SET quantity = quantity - ? WHERE name = ?";
        $stmt = $_db->prepare($updateStockQuery);
        $stmt->execute([$quantity, $productName]);

        $cartItems = [
            [
                'productName' => $productName,
                'productId' => $productId,
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ]
        ];


        $timestampQuery = "SELECT `timestamp` FROM payment WHERE transactionId = ?";
        $stmt = $_db->prepare($timestampQuery);
        $stmt->execute([$transactionId]);
        $timestamp = $stmt->fetchColumn();

        receipt_email($email, $recipientName, $orderId, $cartItems, $shippingFee, $finalTotal, $address, $postalCode, $state, $contactNumber, $transactionId, $timestamp);
        header("Location: success.php?success=1");
        exit();
    } catch (Exception $e) {
        error_log("Error processing payment: " . $e->getMessage());
        header('Location: error.php');
        exit();
    }
 } else {
  
    header('Location: error.php');
    exit();
}
?>
