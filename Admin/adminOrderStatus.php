<?php

require_once '../includes/_base.php'; 
include 'sideNav.php';
include 'adminHeader.php';

$search = req('search');
$page = max(1, (int)req('page', 1));
$perPage = req('rowsPerPage', 10);
$start = ($page - 1) * $perPage;
$filterOrderId = req('filter') ?? null;

// Define sort and order with default values
$sort = req('sort') ?? 'orderId';
$order = req('order') ?? 'desc';

// Ensure the sort and order are valid
$allowedSorts = ['orderId', 'userId'];
$allowedOrders = ['asc', 'desc'];

if (!in_array($sort, $allowedSorts)) {
    $sort = 'orderId';
}
if (!in_array($order, $allowedOrders)) {
    $order = 'desc';
}

try {
    // Fetch distinct orderIds with search filter if applicable
    $query = 'SELECT DISTINCT orderId FROM orders';
    
    $conditions = [];
    if ($search) {
        $conditions[] = '(orderId LIKE :search OR userId LIKE :search)';
    }
    if ($filterOrderId) {
        $conditions[] = 'orderId = :filterOrderId';
    }
    
    if ($conditions) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }

    // Add sorting and limit for pagination
    $query .= " ORDER BY $sort $order LIMIT :start, :perPage";
    
    $stm = $_db->prepare($query);
    
    if ($search) {
        $stm->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    if ($filterOrderId) {
        $stm->bindValue(':filterOrderId', $filterOrderId, PDO::PARAM_STR);
    }
    $stm->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stm->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
    $stm->execute();
    
    $orderIds = $stm->fetchAll(PDO::FETCH_COLUMN);

    // Fetch full order details for the selected order IDs
    $orders = [];
    if (!empty($orderIds)) {
        $detailsQuery = 'SELECT * FROM orders WHERE orderId IN (' . implode(',', array_fill(0, count($orderIds), '?')) . ') ORDER BY ' . $sort . ' ' . $order;
        $stm = $_db->prepare($detailsQuery);
        
        foreach ($orderIds as $index => $id) {
            $stm->bindValue($index + 1, $id);
        }
        
        $stm->execute();
        $orders = $stm->fetchAll();
    }
    
    // Total count query to get total unique orderIds
    $totalQuery = 'SELECT COUNT(DISTINCT orderId) FROM orders';
    
    if ($conditions) {
        $totalQuery .= ' WHERE ' . implode(' AND ', $conditions);
    }
    
    $totalStm = $_db->prepare($totalQuery);
    if ($search) {
        $totalStm->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    if ($filterOrderId) {
        $totalStm->bindValue(':filterOrderId', $filterOrderId, PDO::PARAM_STR);
    }
    $totalStm->execute();
    
    // Fetch total records count for pagination
    $total = $totalStm->fetchColumn();
    
    // Calculate total pages
    $totalPages = ceil($total / $perPage);
    
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}

// Message handling for successful order cancellation
if (isset($_GET['message']) && $_GET['message'] === 'cancel_success') {
    echo "<script>
        alert('Order cancellation was successful.');
       
        if (window.history.replaceState) {
            const url = new URL(window.location);
            url.searchParams.delete('message');
            window.history.replaceState({}, document.title, url);
        }
    </script>";
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <link rel="stylesheet" href="../CSS/adminMember.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>

.address-tooltip {
    position: relative;
    cursor: pointer;
    border-bottom: 1px dashed #000; /* Optional styling */
}


.address-tooltip::after {
    content: attr(data-tooltip);
    position: absolute;
    background-color: #333;
    color: #fff;
    padding: 5px;
    border-radius: 4px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all 0.3s ease;
    z-index: 10;
    font-size: 12px;
    left: 50%;
    transform: translateX(-50%);
    bottom: 100%; 
}

.address-tooltip:hover::after {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);

}

.disabled-text {
    color: #999;
    font-style: italic;
}

.profilepic-column .edit-btn, .profilepic-column .cancel-btn {
    display: inline-block;
    padding: 5px 10px;
    background-color: #007bff;
    color: #fff;
    border-radius: 4px;
    text-decoration: none;
    margin-right: 5px;
}

.profilepic-column .cancel-btn {
    background-color: #dc3545;
}

.profilepic-column .edit-btn:hover, .profilepic-column .cancel-btn:hover {
    opacity: 0.8;
}

.profilepic-column .disabled-text {
    display: block;
    padding: 5px;
    color: #999;
    background-color: #f5f5f5;
    border-radius: 4px;
}
</style>
</head>
<body>
    <div class="container">
        <h1>Order Management</h1>
        <div class="search-container">
        <form method="get" action="">
    <label for="searchInput">Search:</label>
    <input type="text" id="searchInput" name="search" placeholder="Order Id, User Id, Product Id, or Product Name" value="<?= htmlspecialchars($search) ?>">
    <input type="hidden" name="rowsPerPage" value="<?= $perPage ?>">  
    <button type="submit" class="edit-btn">Search</button>
</form>
        </div>
        <table id="orderTable">
            <thead>
                <tr>
                <th>
                        <a href="?search=<?= urlencode($search) ?>&rowsPerPage=<?= $perPage ?>&sort=orderId&order=<?= ($sort === 'orderId' && $order === 'asc') ? 'desc' : 'asc' ?>" class="sort-link">
                           Order ID <?= ($sort === 'orderId') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?>
                        </a>
                    </th>
                    <th>Total Product</th>
                    <th>Total Quantity</th>
                    <th>Subtotal(RM)</th>
                    <th>
                        <a href="?search=<?= urlencode($search) ?>&rowsPerPage=<?= $perPage ?>&sort=userId&order=<?= ($sort === 'userId' && $order === 'asc') ? 'desc' : 'asc' ?>" class="sort-link">
                           User ID <?= ($sort === 'userId') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?>
                        </a>
                    </th>
                    <th>Recipient</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Details</th>
                   
                </tr>
            </thead>
            <tbody>
    <?php 
   
    $displayedOrderIds = []; 
    $uniqueOrderCount = 0; 

    foreach ($orders as $order): 
        if (!in_array($order->orderId, $displayedOrderIds)): 
            $displayedOrderIds[] = $order->orderId;
            $uniqueOrderCount++; 
           
            $totalProducts = 0;
            $totalQuantity = 0;
            $totalSubtotal = 0;
    
            foreach ($orders as $o) {
                if ($o->orderId == $order->orderId) {
                    $totalProducts++;
                    $totalQuantity += $o->quantity;
                    $totalSubtotal += $o->subtotal;
                }
            }
    ?>
  <tr>
        <td><?= htmlspecialchars($order->orderId) ?></td>
        <td><?= $totalProducts ?></td> < 
        <td><?= $totalQuantity ?></td>  
        <td><?= number_format($totalSubtotal, 2) ?></td>  
        <td><?= htmlspecialchars($order->userId) ?></td>
        <td><?= htmlspecialchars($order->recipient) ?></td>
        <td>
            <span class="address-tooltip" data-tooltip="Postcode: <?= htmlspecialchars($order->postCode) ?>, State: <?= htmlspecialchars($order->state) ?>">
                <?= htmlspecialchars($order->address) ?>
            </span>
        </td>
        <td style="<?= $order->status == 'Completed' ? 'background-color: #90EE90;' : ($order->status == 'Cancelled' ? 'background-color: #ffcccc;' : '') ?>">
            <?= htmlspecialchars($order->status) ?>
        </td>
        <td class="profilepic-columns">
            <div class="action-buttons">
                <?php if ($order->status == 'Completed' || $order->status == 'Cancelled'): ?>
                    <span class="disabled-text"><?= $order->status == 'Completed' ? 'Order Completed' : 'Order Cancelled' ?></span>
                <?php else: ?>
                    <a href="adminOrderStatusUpdate.php?orderId=<?= $order->orderId ?>" class="edit-btn" onclick="return confirmUpdate()">Update</a>
                    <a href="javascript:void(0);" class="cancel-btn" onclick="return confirmCancel('<?= $order->orderId ?>')">Cancel</a>
                <?php endif; ?>
            </div>
        </td>
        <td><a href="javascript:void(0);" class="view-details" data-orderId="<?= $order->orderId ?>">View Details</a></td>
    </tr>
<?php endif;
 endforeach; ?>

    <?php if (empty($displayedOrderIds)): ?>
    <tr><td colspan="10">No orders found</td></tr>
    <?php endif; ?>
</tbody>
        </table>

        <div id="orderDetailsModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span id="closeModal" class="close">&times;</span>
        <div id="orderDetailsContainer"></div>
    </div>
</div>
        <div class="pagination-controls">
            <label for="rowsPerPage">Rows per page:</label>
            <form method="get" action="">
                <select id="rowsPerPage" name="rowsPerPage" onchange="this.form.submit()">
                    <option value="5" <?= $perPage == 5 ? 'selected' : '' ?>>5</option>
                    <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                    <option value="15" <?= $perPage == 15 ? 'selected' : '' ?>>15</option>
                </select>
            </form>
            <div id="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="<?= ($i == $page) ? 'active' : '' ?>">
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&rowsPerPage=<?= $perPage ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <script src="../js/adminMember.js"></script>
    <script>
function confirmUpdate() {
    return confirm("Are you sure you want to update the order status?");
}

function confirmCancel(orderId, productId) {
        let reason = prompt("Please enter the reason for canceling this order:");
        
        if (reason !== null && reason.trim() !== "") {
            // Redirect with reason, orderId, and productId as URL parameters
            window.location.href = `adminOrderStatusUpdate.php?action=cancel&orderId=${orderId}&productId=${productId}&reason=${encodeURIComponent(reason)}`;
            return true;
        } else {
            alert("Cancellation reason is required.");
            return false;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
    const viewDetailsButtons = document.querySelectorAll('.view-details');

    viewDetailsButtons.forEach(button => {
        button.addEventListener('click', function () {
            const orderId = this.getAttribute('data-orderId');
            fetch(`adminOrderDetails.php?orderId=${orderId}`)
                .then(response => response.text())
                .then(data => {
                    // Display the order details in a modal or new section
                    document.getElementById('orderDetailsContainer').innerHTML = data;
                    document.getElementById('orderDetailsModal').style.display = 'block';
                })
                .catch(error => console.error('Fetch error:', error));
        });
    });

    // Close the modal
    const closeModal = document.getElementById('closeModal');
    if (closeModal) {
        closeModal.addEventListener('click', () => {
            document.getElementById('orderDetailsModal').style.display = 'none';
        });
    }
});
</script>
</body>
</html>