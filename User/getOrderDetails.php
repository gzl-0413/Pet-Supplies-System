<?php
require_once '../includes/_base.php';

if (!isset($_SESSION['member'])) {
    redirect('login.php');
}

if (!isset($_GET['orderId'])) {
    echo "Order ID is missing.";
    exit;
}

$orderId = $_GET['orderId'];
$member_id = $_SESSION['member'];

// Fetch all products for this order
$stmt = $_db->prepare("SELECT o.*, p.name AS product_name, p.photo AS product_photo 
                       FROM orders o 
                       JOIN product p ON o.productId = p.id 
                       WHERE o.orderId = ? AND o.userId = ?");
$stmt->execute([$orderId, $member_id]);
$orderProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($orderProducts)) {
    echo "No products found for this order.";
    exit;
}
?>

<head>
    <style>.order-details-container {
    max-width: 800px;
    margin: 20px auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.order-details-container h2 {
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
    border-bottom: 2px solid #f2f2f2;
    padding-bottom: 10px;
}

.order-details-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.order-details-table th, 
.order-details-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #f2f2f2;
}

.order-details-table th {
    background-color: #f9f9f9;
    color: #333;
    font-weight: bold;
}

.order-details-table td {
    vertical-align: middle;
    color: #555;
}

.product-image-order {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
    margin-right: 10px;
}

.product-info {
    display: flex;
    align-items: center;
}

.product-info p {
    margin: 0;
}

.order-details-container button {
    background-color: #007bff;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 20px;
    transition: background-color 0.3s ease;
}

.order-details-container button:hover {
    background-color: #0056b3;
}

/* Styling for better mobile responsiveness */
@media (max-width: 600px) {
    .order-details-container {
        padding: 10px;
    }

    .order-details-table th, .order-details-table td {
        font-size: 14px;
        padding: 8px;
    }

    .product-image-order {
        width: 40px;
        height: 40px;
    }

    .order-details-container h2 {
        font-size: 20px;
    }

    .order-details-container button {
        padding: 6px 12px;
        font-size: 14px;
    }
}
</style>
</head>

<div class="order-details-container">
    <h2>Order Details for Order ID: <?php echo htmlspecialchars($orderId); ?></h2>
    <table class="order-details-table">
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Subtotal (RM)</th>
        </tr>
        <?php foreach ($orderProducts as $product) { ?>
            <tr>
                <td class="product-info">
                    <img src="../uploads/<?php echo htmlspecialchars($product['product_photo']); ?>" alt="Product Image" class="product-image-order" style="width: 50px";>
                    <?php echo htmlspecialchars($product['product_name']); ?>
                </td>
                <td><?php echo $product['quantity']; ?></td>
                <td><?php echo number_format($product['subtotal'], 2); ?></td>
            </tr>
        <?php } ?>
    </table>
    <button onclick="closeOrderDetails()">Back to Orders</button>
</div>
