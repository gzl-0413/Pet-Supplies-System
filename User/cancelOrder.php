<?php
require_once '../includes/_base.php';
auth();

if (!isset($_SESSION['member'])) {
    redirect('login.php');
}
$member_id = $_SESSION['member'];

$orderId = req('orderId');
$productId = req('productId');
$cancelReason = req('reason');

try {
    // Update the order status to "Cancelled" and save the cancellation reason
    $stm = $_db->prepare("UPDATE orders SET status = 'Cancelled' , member_cancel = ? WHERE orderId = ? AND userId = ?");
    $stm->execute([$cancelReason, $orderId,$member_id]);
    
    // Redirect back to the order page
    header('Location: product.php?message=cancel_success');
    exit;
       
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}

?>
