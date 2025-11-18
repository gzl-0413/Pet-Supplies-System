<?php
include '../includes/_base.php';
auth();
if (!isset($_SESSION['member'])) {
    redirect('login.php');
}
$member_id = $_SESSION['member'];

$stm = $_db->prepare('SELECT * FROM orders WHERE userId = ? ORDER BY orderId DESC');
$stm->execute([$member_id]);
$orders = $stm->fetchAll(PDO::FETCH_ASSOC);


$toShipOrders = toShipOrders($member_id);
$toReceiveOrders = toReceiveOrders($member_id);
$completedOrders = completedOrders($member_id);
$cancelledOrders = cancelledOrders($member_id);

function fetchOrderDetails($productId)
{
    global $_db;
    $stm = $_db->prepare('SELECT photo FROM product WHERE id = ?');
    $stm->execute([$productId]);
    return $stm->fetchAll(PDO::FETCH_ASSOC);
}

function fetchPaymentDate($orderId)
{
    global $_db;
    // Fetch the payment date from the payment table
    $dateStm = $_db->prepare('SELECT timestamp FROM payment WHERE orderId = ?');
    $dateStm->execute([$orderId]);
    $paymentDate = $dateStm->fetch(PDO::FETCH_ASSOC);

    return $paymentDate['timestamp'] ?? 'N/A'; // Return the date or 'N/A' if not found
}
function toShipOrders($member_id)
{
    global $_db;
    $stm = $_db->prepare('SELECT * FROM orders WHERE userId = ? AND status = "Shipping"');
    $stm->execute([$member_id]);
    return $stm->fetchAll(PDO::FETCH_ASSOC);
}

function toReceiveOrders($member_id)
{
    global $_db;
    $stm = $_db->prepare('SELECT * FROM orders WHERE userId = ? AND status = "Delivering"');
    $stm->execute([$member_id]);
    return $stm->fetchAll(PDO::FETCH_ASSOC);
}

function completedOrders($member_id)
{
    global $_db;
    $stm = $_db->prepare("SELECT * FROM orders WHERE userId = ? AND status = 'Completed'");
    $stm->execute([$member_id]);
    return $stm->fetchAll(PDO::FETCH_ASSOC);
}

function cancelledOrders($member_id)
{
    global $_db;
    $stm = $_db->prepare('SELECT * FROM orders WHERE userId = ? AND status = "Cancelled"');
    $stm->execute([$member_id]);
    return $stm->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link href="../CSS/userOrder.css" rel="stylesheet" type="text/css" />

    <title>Order Management</title>
    <script src="../JS/memberProfile.js"></script>
   
</head>

<body>
<h2>Your Orders</h2>
    <div class="tab">
        <button class="tablinks active" onclick="openTab(event, 'All')">All</button>
        <button class="tablinks" onclick="openTab(event, 'ToShip')">To Ship</button>
        <button class="tablinks" onclick="openTab(event, 'ToReceive')">To Receive</button>
        <button class="tablinks" onclick="openTab(event, 'Completed')">Completed / To Rate</button>
        <button class="tablinks" onclick="openTab(event, 'Cancelled')">Cancelled</button>
    </div>

  
    <div id="All" class="tab-content active">
    <h3>All Orders</h3>
    <?php if (!empty($orders)) { ?>
        <table class="order-list">
            <tr>
                <th>Order ID</th>
                <th>Total Products</th>
                <th>Total Quantity</th>
                <th>Total Subtotal (RM)</th>
                <th>Status</th>
                <th>Order Date</th>
                <th>Actions</th>
            </tr>
            <?php 
            $uniqueOrders = []; // To track unique order IDs

            foreach ($orders as $order) {
                if (!isset($uniqueOrders[$order['orderId']])) {
                    $uniqueOrders[$order['orderId']] = [
                        'orderId' => $order['orderId'],
                        'status' => $order['status'],
                        'subtotal' => 0,
                        'quantity' => 0,
                        'total_products' => 0,
                        'date' => fetchPaymentDate($order['orderId'])
                    ];

                    $productDetails = fetchOrderDetails($order['productId']);
                    $uniqueOrders[$order['orderId']]['total_products']++;
                    $uniqueOrders[$order['orderId']]['quantity'] += $order['quantity'];
                    $uniqueOrders[$order['orderId']]['subtotal'] += $order['subtotal'];
                } else {
                    $uniqueOrders[$order['orderId']]['total_products']++;
                    $uniqueOrders[$order['orderId']]['quantity'] += $order['quantity'];
                    $uniqueOrders[$order['orderId']]['subtotal'] += $order['subtotal'];
                }
            }

            foreach ($uniqueOrders as $uniqueOrder) {
            ?>
                <tr>
                    <td><?php echo $uniqueOrder['orderId']; ?></td>
                    <td><?php echo $uniqueOrder['total_products']; ?></td>
                    <td><?php echo $uniqueOrder['quantity']; ?></td>
                    <td><?php echo number_format($uniqueOrder['subtotal'], 2); ?></td>
                    <td style="background-color: 
                        <?php 
                            if ($uniqueOrder['status'] == 'Completed') {
                                echo 'lightgreen'; 
                            } elseif ($uniqueOrder['status'] == 'Cancelled') {
                                echo 'lightcoral';
                            } else {
                                echo 'transparent';  
                            }
                        ?>;">
                        <?php echo ($uniqueOrder['status']); ?>
                    </td>
                    <td><?php echo $uniqueOrder['date']; ?></td>
                    <td>
                        <a href="javascript:void(0);" class="view-details" data-orderId="<?php echo $uniqueOrder['orderId']; ?>">View Details</a>
                        <?php if ($uniqueOrder['status'] == 'Pending' || $uniqueOrder['status'] == 'Shipping'): ?>
                            <a href="javascript:void(0);" class="cancel-button" data-orderId="<?php echo $uniqueOrder['orderId']; ?>" onclick="return confirmCancel('<?php echo $uniqueOrder['orderId']; ?>')">Cancel Order</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } else {
        echo "No orders found.";
    } ?>
</div>

    <div id="ToShip" class="tab-content">
    <h3>To Ship</h3>
    <?php if (!empty($toShipOrders)) { ?>
        <table class="order-list">
            <tr>
                <th>Order ID</th>
                <th>Total Products</th>
                <th>Total Quantity</th>
                <th>Total Subtotal (RM)</th>
                <th>Status</th>
                <th>Estimated Ship Day</th>
                <th>Actions</th>
            </tr>
            <?php 
            $uniqueToShipOrders = [];

            foreach ($toShipOrders as $order) {
                if (!isset($uniqueToShipOrders[$order['orderId']])) {
                    $uniqueToShipOrders[$order['orderId']] = [
                        'orderId' => $order['orderId'],
                        'status' => $order['status'],
                        'subtotal' => 0,
                        'quantity' => 0,
                        'total_products' => 0,
                        'date' => fetchPaymentDate($order['orderId'])
                    ];
                    $uniqueToShipOrders[$order['orderId']]['total_products']++;
                    $uniqueToShipOrders[$order['orderId']]['quantity'] += $order['quantity'];
                    $uniqueToShipOrders[$order['orderId']]['subtotal'] += $order['subtotal'];
                } else {
                    $uniqueToShipOrders[$order['orderId']]['total_products']++;
                    $uniqueToShipOrders[$order['orderId']]['quantity'] += $order['quantity'];
                    $uniqueToShipOrders[$order['orderId']]['subtotal'] += $order['subtotal'];
                }
            }

            foreach ($uniqueToShipOrders as $uniqueOrder) {
                $paymentDate = $uniqueOrder['date'];
                $date = new DateTime($paymentDate);
                $date->modify('+3 days');
                $formattedDate = $date->format('Y-m-d');
            ?>
                <tr>
                    <td><?php echo $uniqueOrder['orderId']; ?></td>
                    <td><?php echo $uniqueOrder['total_products']; ?></td>
                    <td><?php echo $uniqueOrder['quantity']; ?></td>
                    <td><?php echo number_format($uniqueOrder['subtotal'], 2); ?></td>
                    <td><?php echo ($uniqueOrder['status']); ?></td>
                    <td><?php echo $formattedDate; ?></td>
                    <td>
                        <a href="javascript:void(0);" class="view-details" data-orderId="<?php echo $uniqueOrder['orderId']; ?>">View Details</a>
                        <a href="javascript:void(0);" class="cancel-button" data-orderId="<?php echo $uniqueOrder['orderId']; ?>" onclick="return confirmCancel(this)">Cancel Order</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } else {
        echo "No orders found.";
    } ?>
</div>

<div id="ToReceive" class="tab-content">
    <h3>To Receive</h3>
    <?php if (!empty($toReceiveOrders)) { ?>
        <table class="order-list">
            <tr>
                <th>Order ID</th>
                <th>Total Products</th>
                <th>Total Quantity</th>
                <th>Total Subtotal (RM)</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php 
            $uniqueToReceiveOrders = []; 

            foreach ($toReceiveOrders as $order) {
                if (!isset($uniqueToReceiveOrders[$order['orderId']])) {
                    $uniqueToReceiveOrders[$order['orderId']] = [
                        'orderId' => $order['orderId'],
                        'status' => $order['status'],
                        'subtotal' => 0,
                        'quantity' => 0,
                        'total_products' => 0,
                        'date' => fetchPaymentDate($order['orderId'])
                    ];
                    $uniqueToReceiveOrders[$order['orderId']]['total_products']++;
                    $uniqueToReceiveOrders[$order['orderId']]['quantity'] += $order['quantity'];
                    $uniqueToReceiveOrders[$order['orderId']]['subtotal'] += $order['subtotal'];
                } else {
                    $uniqueToReceiveOrders[$order['orderId']]['total_products']++;
                    $uniqueToReceiveOrders[$order['orderId']]['quantity'] += $order['quantity'];
                    $uniqueToReceiveOrders[$order['orderId']]['subtotal'] += $order['subtotal'];
                }
            }

            foreach ($uniqueToReceiveOrders as $uniqueOrder) {
            ?>
                <tr>
                    <td><?php echo $uniqueOrder['orderId']; ?></td>
                    <td><?php echo $uniqueOrder['total_products']; ?></td>
                    <td><?php echo $uniqueOrder['quantity']; ?></td>
                    <td><?php echo number_format($uniqueOrder['subtotal'], 2); ?></td>
                    <td><?php echo ($uniqueOrder['status']); ?></td>
                    <td>
                        <a href="javascript:void(0);" class="view-details" data-orderId="<?php echo $uniqueOrder['orderId']; ?>">View Details</a>
                        <a class="update-button" data-orderId="<?php echo $order['orderId']; ?>" data-productId="<?php echo $order['productId']; ?>" onclick="return confirmUpdate(this)">Update</a>
                </tr>
            <?php } ?>
        </table>
    <?php } else {
        echo "No orders found.";
    } ?>
</div>

<div id="Completed" class="tab-content">
    <h3>Completed / To Rate</h3>
    <?php if (!empty($completedOrders)) { ?>
        <table class="order-list">
            <tr>
                <th>Order ID</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Subtotal (RM)</th>
                <th>Action</th>
            </tr>
            <?php foreach ($completedOrders as $order) {
                $orderDetails = fetchOrderDetails($order['productId']);
                foreach ($orderDetails as $detail) {
            ?>
                    <tr>
                        <td><?php echo $order['orderId']; ?></td>
                        <td class="product-info">
                            <img src="../uploads/<?php echo $detail['photo']; ?>" alt="Product Image" class="product-image-order">
                            <?php echo $order['productName']; ?>
                        </td>
                        <td><?php echo $order['quantity']; ?></td>
                        <td><?php echo number_format($order['subtotal'], 2); ?></td>
                        <td>
                        
                        <?php
    $productId = $order['productId'];
    $orderId = $order['orderId'];
    
    // Query to check if a review already exists for this order and product
    $reviewCheckQuery = "SELECT COUNT(*) FROM review WHERE product_id = :productId AND order_id = :orderId";
    $stmt = $_db->prepare($reviewCheckQuery);
    $stmt->bindParam(':productId', $productId);
    $stmt->bindParam(':orderId', $orderId);
    $stmt->execute();
    
    $reviewExists = $stmt->fetchColumn();  
                    
    
    if ($reviewExists == 0): ?>
        <a href="makeReview.php?product_id=<?php echo $productId; ?>&order_id=<?php echo $orderId; ?>" class="review-button">Rate</a>
    <?php else: ?>
        <!-- Optionally, you can display a message or nothing here -->
        <span style="color: gray;">Review Submitted</span>
        <a href="viewReview.php" class="reviews-button">Your Reviews</a>

    <?php endif; ?>                            
    
    <a href="productPage.php?productId=<?php echo $order['productId']; ?>" class="buy-again-button">Buy Again</a>
                        </td>
                    </tr>
            <?php }
            } ?>
        </table>
    <?php } else {
        echo "No orders found.";
    } ?>
</div>
<div id="Cancelled" class="tab-content">
    <h3>Cancelled</h3>
    <?php if (!empty($cancelledOrders)) { ?>
        <table class="order-list">
            <tr>
                <th>Order ID</th>
                <th>Total Products</th>
                <th>Total Quantity</th>
                <th>Total Subtotal (RM)</th>
                <th>Seller's Reason</th>
                <th>Your Reason</th>
                <th>Actions</th>
            </tr>
            <?php 
            $uniqueCancelledOrders = []; // To track unique order IDs

            foreach ($cancelledOrders as $order) {
                if (!isset($uniqueCancelledOrders[$order['orderId']])) {
                    // Initialize the unique order details
                    $uniqueCancelledOrders[$order['orderId']] = [
                        'orderId' => $order['orderId'],
                        'total_products' => 0,
                        'total_quantity' => 0,
                        'total_subtotal' => 0,
                        'seller_reason' => $order['remark'],
                        'member_reason' => $order['member_cancel']
                    ];
                }

                // Update the totals
                $uniqueCancelledOrders[$order['orderId']]['total_products']++;
                $uniqueCancelledOrders[$order['orderId']]['total_quantity'] += $order['quantity'];
                $uniqueCancelledOrders[$order['orderId']]['total_subtotal'] += $order['subtotal'];
            }

            // Display each unique order
            foreach ($uniqueCancelledOrders as $uniqueOrder) {
            ?>
                <tr>
                    <td><?php echo $uniqueOrder['orderId']; ?></td>
                    <td><?php echo $uniqueOrder['total_products']; ?></td>
                    <td><?php echo $uniqueOrder['total_quantity']; ?></td>
                    <td><?php echo number_format($uniqueOrder['total_subtotal'], 2); ?></td>
                    <td <?php if (!is_null($uniqueOrder['seller_reason'])) { echo 'style="background-color: lightcoral;"'; } ?>>
                        <?php echo htmlspecialchars($uniqueOrder['seller_reason'] ?? '-'); ?>
                    </td>
                    <td <?php if (!is_null($uniqueOrder['member_reason'])) { echo 'style="background-color: lightcoral;"'; } ?>>
                        <?php echo htmlspecialchars($uniqueOrder['member_reason'] ?? '-'); ?>
                    </td>
                    <td>
                        <a href="javascript:void(0);" class="view-details" data-orderId="<?php echo $uniqueOrder['orderId']; ?>">View Details</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } else {
        echo "No orders found.";
    } ?>
</div>

    <script>
 
       function openTab(evt, tabName) {
    // Get all elements with class="tab-content" and hide them
    const tabContent = document.querySelectorAll('.tab-content');
    tabContent.forEach(content => {
        content.classList.remove('active');
        content.style.display = 'none';  // Hide the tab content
    });

    // Get all elements with class="tablinks" and remove the class "active"
    const tabLinks = document.querySelectorAll('.tablinks');
    tabLinks.forEach(link => {
        link.classList.remove('active');
    });

    const activeTab = document.getElementById(tabName);
    if (activeTab) {
        activeTab.style.display = 'block';
        activeTab.classList.add('active');
    }

    // Add "active" class to the clicked tab link
    evt.currentTarget.classList.add('active');
}

// Show the "All" tab content by default when the page loads
document.getElementById('All').style.display = 'block';




function confirmCancellation(orderId) {
    const reason = prompt("Please provide a reason for cancellation:");
    if (reason && reason.trim() !== "") {
        document.getElementById(`cancelReason_${orderId}`).value = reason;
        document.getElementById(`cancelForm_${orderId}`).submit();
    } else {
        alert("Cancellation reason is required.");
    }
}
</script>
</body>

</html>
