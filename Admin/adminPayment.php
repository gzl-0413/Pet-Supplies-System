<?php

require_once '../includes/_base.php';  
include 'adminHeader.php';
include 'sideNav.php';
 
 
$stmt = $_db->query("SELECT * FROM payment");
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

 
$newSubtotalSum = 0;
$target = 10000;  

 
foreach ($payments as $payment) {
    $newSubtotal = $payment['subtotal'] - $payment['shipping']; 
    $newSubtotalSum += $newSubtotal;
}

 
$progressPercentage = ($newSubtotalSum / $target) * 100;    
if ($progressPercentage > 100) {
    $progressPercentage = 100; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Payments Management</title>
    <link rel="stylesheet" href="../css/adminPayment.css">  
    <style>
    .balance-bar {
    background-color: #1c9bff;  
    height: 100%;
    border-radius: 20px;
    width: <?= number_format($progressPercentage, 2) ?>%;
    transition: width 0.4s ease; 
}


    </style>
</head>
<body>
    <div class="main-container">
        <div class="headerPayment">
            <h2>Payments Overview</h2>
         </div>

        <div class="wallet-balance">
            <h3>Total Revenue</h3>
            <p>$<?= number_format($newSubtotalSum, 2); ?></p>
            <div class="balance-bar-container">
                <div class="balance-bar"></div>
            </div>
            <p>Target: $<?= number_format($target, 2); ?></p>
        </div>

        <table class="payment-table">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>User ID</th>
                    <th>Order ID</th>
                    <th>Subtotal</th>
                    <th>Shipping</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): 
                     
                    $newSubtotal = $payment['subtotal'] - $payment['shipping'];
                ?>
<tr onclick="window.location.href='adminOrderStatus.php?filter=<?= urlencode($payment['orderId']); ?>'" style="cursor: pointer;">

                        <td><?= htmlspecialchars($payment['transactionId']); ?></td>
                        <td><?= htmlspecialchars($payment['userId']); ?></td>
                        <td><?= htmlspecialchars($payment['orderId']); ?></td>
                        <td>$<?= number_format($newSubtotal, 2); ?></td>
                        <td>$<?= number_format($payment['shipping'], 2); ?></td>
                        <td>$<?= number_format($payment['subtotal'], 2); ?></td>   
                        <td><?= htmlspecialchars($payment['status'])?></td>
                        <td><?= htmlspecialchars($payment['timestamp']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="payment-statistics">
            <h3>Payment Statistics</h3>
            <div class="chart">
 
                <div class="pie-chart"></div>
            </div>
        </div>
    </div>
</body>
</html>
