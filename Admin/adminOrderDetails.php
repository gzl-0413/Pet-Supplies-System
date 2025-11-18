<?php
require_once '../includes/_base.php';
auth();

$orderId = req('orderId');

try {
    $query = "SELECT * FROM orders WHERE orderId = :orderId";
    $stm = $_db->prepare($query);
    $stm->bindParam(':orderId', $orderId, PDO::PARAM_STR);
    $stm->execute();
    $orderDetails = $stm->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details for <?= htmlspecialchars($orderId) ?></title>
</head>
<body>
    <h2>Order Details for Order ID: <?= htmlspecialchars($orderId) ?></h2>
    <table border="1" cellspacing="0" cellpadding="10">
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Subtotal (RM)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orderDetails as $detail): ?>
                <tr>
                    <td><?= htmlspecialchars($detail->productId) ?></td>
                    <td><?= htmlspecialchars($detail->productName) ?></td>
                    <td><?= htmlspecialchars($detail->quantity) ?></td>
                    <td><?= number_format($detail->subtotal, 2) ?></td>
                    <td><?= htmlspecialchars($detail->status) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button id="closeModal">Close</button>
</body>
</html>
