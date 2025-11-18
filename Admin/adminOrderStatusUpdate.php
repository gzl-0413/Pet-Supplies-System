<?php
require_once '../includes/_base.php';
auth();

$orderId = req('orderId');
$action = req('action');
$reason = req('reason');
 
try {
    if ($action === 'cancel') {
       
        $cancelQuery = "UPDATE orders SET status = 'Cancelled', remark = ? WHERE orderId = ? ";
        $cancelStm = $_db->prepare($cancelQuery);
        
         
        $cancelStm->execute([$reason, $orderId]);
    
        header('Location: adminOrderStatus.php?message=cancel_success');
        exit;
    } else {
        
        $query = "SELECT status FROM orders WHERE orderId = ?";
        $stm = $_db->prepare($query);
        
      
        $stm->execute([$orderId]);
        
        $order = $stm->fetch();

        if ($order) {
            $currentStatus = $order->status; // Accessing the object property correctly
            $nextStatus = getNextStatus($currentStatus);
            if ($nextStatus) {
                // Using positional placeholders for the query
                $updateQuery = "UPDATE orders SET status = ? WHERE orderId = ?";
                $updateStm = $_db->prepare($updateQuery);
                
                // Execute the query with the parameters in the correct order
                $updateStm->execute([$nextStatus, $orderId]);
            
                header('Location: adminOrderStatus.php');
                exit;
            } else {
                echo "Invalid status transition.";
            }
        } else {
            echo "<script>
    alert('You are not allow to access this page with this way.');
    window.location.href = 'adminDashboard.php';
</script>";
exit;
        }
    }
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}

// Helper function remains unchanged
function getNextStatus($currentStatus) {
    $statusMap = [
        'Pending' => 'Shipping',
        'Shipping' => 'Delivering',
        'Delivering' => 'Completed',
    ];

    return $statusMap[$currentStatus] ?? null;
}
?>
