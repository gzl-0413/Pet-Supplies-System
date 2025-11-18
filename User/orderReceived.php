<?php
require_once '../includes/_base.php';
auth();
$orderId = req('orderId');

try {
    // Handle order status update to 'Completed'
    $query = "UPDATE orders SET status = 'Completed' WHERE orderId = ?";
    $stm = $_db->prepare($query);
    $stm->execute([$orderId]);

    echo "<script>
        alert('Update Successfully');
        window.location.href = 'product.php';
    </script>";
    exit;
    
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}
?>
