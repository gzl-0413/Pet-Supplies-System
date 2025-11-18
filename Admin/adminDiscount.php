<?php

require_once '../includes/_base.php';
include 'adminHeader.php';
include 'sideNav.php'; 

// Handle delete request
$deletePromoID = req('delete');
if ($deletePromoID) {
    try {
        $stmt = $_db->prepare("DELETE FROM promotion WHERE promoId = ?");
        $stmt->execute([$deletePromoID]);
        
        
        if ($stmt) {
            header("Location: adminDiscount.php?success=1");
            exit();
        } else {
            header("Location: adminDiscount.php?error=Failed to delete promotion");
            exit();
        }
    } catch (PDOException $e) {
        echo 'Database error: ' . $e->getMessage();
    }
}

// Retrieve filter parameters
$search = req('search');
$startDate = req('startDate');
$endDate = req('endDate');
$page = max(1, (int)req('page', 1));
$perPage = req('rowsPerPage', 5);
$start = ($page - 1) * $perPage;

try {
    // Build the base query to fetch promotion data
    $query = '
        SELECT promoId, PromoName, amount, promoWay, promoType, EndDate, status
        FROM promotion
        WHERE 1';
    
    // Apply search filter
    if ($search) {
        $query .= ' AND (PromoName LIKE :search OR promoId LIKE :search)';
    }
    
    // Apply date filter
    if ($startDate && $endDate) {
        $query .= ' AND (EndDate BETWEEN :startDate AND :endDate)';
    }

    // Sorting logic
    $sort = req('sort') ?? 'promoId';
    $order = req('order') ?? 'asc';

    $allowedSorts = ['promoId', 'PromoName', 'amount', 'promoWay', 'promoType', 'EndDate', 'status'];
    $allowedOrders = ['asc', 'desc'];

    if (!in_array($sort, $allowedSorts)) {
        $sort = 'promoId';
    }
    if (!in_array($order, $allowedOrders)) {
        $order = 'asc';
    }

    $query .= " ORDER BY $sort $order LIMIT :start, :perPage";
    
    $stm = $_db->prepare($query);
    
    if ($search) {
        $stm->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    if ($startDate && $endDate) {
        $stm->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $stm->bindValue(':endDate', $endDate, PDO::PARAM_STR);
    }
    $stm->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stm->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
    $stm->execute();
    
    $promotions = $stm->fetchAll(PDO::FETCH_ASSOC);

    // Fetch the total count for pagination
    $totalQuery = '
        SELECT COUNT(*)
        FROM promotion
        WHERE 1';
    
    if ($search) {
        $totalQuery .= ' AND (PromoName LIKE :search OR promoId LIKE :search)';
    }
    if ($startDate && $endDate) {
        $totalQuery .= ' AND (EndDate BETWEEN :startDate AND :endDate)';
    }
    
    $totalStm = $_db->prepare($totalQuery);
    if ($search) {
        $totalStm->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    if ($startDate && $endDate) {
        $totalStm->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $totalStm->bindValue(':endDate', $endDate, PDO::PARAM_STR);
    }
    $totalStm->execute();
    $total = $totalStm->fetchColumn();
    
    $totalPages = ceil($total / $perPage);
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Discount Management</title>
    <link rel="stylesheet" href="../css/adminMember.css">
    <link rel="stylesheet" href="../css/adminDiscount.css">
</head>
<body>
    <div class="container">
        <h1>Discount Management</h1>
        <!-- Display success or error messages -->
        <?php if (req('success')): ?>
            <div class="alert alert-success">Promotion deleted successfully!</div>
        <?php elseif (req('error')): ?>
            <div class="alert alert-danger"><?= htmlspecialchars(req('error')) ?></div>
        <?php endif; ?>

        <div class="search-container">
            <form method="get" action="">
                <label for="searchInput">Search:</label>
                <input type="text" id="searchInput" name="search" placeholder="Promo ID or Name..." value="<?= htmlspecialchars($search) ?>" style="width: 250px;">
                <label for="startDate">Start Date:</label>
                <input type="date" id="startDate" name="startDate" value="<?= htmlspecialchars($startDate) ?>">
                <label for="endDate">End Date:</label>
                <input type="date" id="endDate" name="endDate" value="<?= htmlspecialchars($endDate) ?>">
                <button type="submit" class="edit-btn" id="searchButton">Search</button>
                <a href="adminDiscountAdd.php" class="add-btn">Add</a>
            </form>
        </div>
        
        <table id="discountTable">
            <thead>
                <tr>
                    <th><a href="?sort=promoId&order=<?= ($sort === 'promoId' && $order === 'asc') ? 'desc' : 'asc' ?>">Promo ID <?= ($sort === 'promoId') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?></a></th>
                    <th><a href="?sort=PromoName&order=<?= ($sort === 'PromoName' && $order === 'asc') ? 'desc' : 'asc' ?>">Promo Name <?= ($sort === 'PromoName') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?></a></th>
                    <th><a href="?sort=amount&order=<?= ($sort === 'amount' && $order === 'asc') ? 'desc' : 'asc' ?>">Amount <?= ($sort === 'amount') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?></a></th>
                    <th><a href="?sort=promoWay&order=<?= ($sort === 'promoWay' && $order === 'asc') ? 'desc' : 'asc' ?>">Promo Way <?= ($sort === 'promoWay') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?></a></th>
                    <th><a href="?sort=promoType&order=<?= ($sort === 'promoType' && $order === 'asc') ? 'desc' : 'asc' ?>">Promo Type <?= ($sort === 'promoType') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?></a></th>
                    <th><a href="?sort=EndDate&order=<?= ($sort === 'EndDate' && $order === 'asc') ? 'desc' : 'asc' ?>">End Date <?= ($sort === 'EndDate') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?></a></th>
                    <th><a href="?sort=status&order=<?= ($sort === 'status' && $order === 'asc') ? 'desc' : 'asc' ?>">Status <?= ($sort === 'status') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?></a></th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promotions as $promo): ?>
                <tr>
                    <td><?= htmlspecialchars($promo['promoId']) ?></td>
                    <td><?= htmlspecialchars($promo['PromoName']) ?></td>
                    <td><?= htmlspecialchars($promo['amount']) ?></td>
                    <td><?= $promo['promoType'] == 1 ? 'Free Shipping' : 'Subtotal Discount' ?></td>
                    <td><?= htmlspecialchars($promo['promoWay']) ?></td>
                    <td><?= htmlspecialchars($promo['EndDate']) ?></td>
                 
                        <td>
                        <div class="checkbox-wrapper-25">      
            <input type="checkbox" class="status-checkbox" data-id="<?= htmlspecialchars($promo['promoId']) ?>" <?= $promo['status'] == '1' ? 'checked' : '' ?>>
            </div>    </td>      
               
        <td>
                        <div class="action-buttons">
                        <a href="adminDiscountEdit.php?promoId=<?= htmlspecialchars($promo['promoId']) ?>" class="edit-btn">Edit</a>
                     <a href="?delete=<?= htmlspecialchars($promo['promoId']) ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this promotion?')">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <!-- Pagination here if necessary -->
    </div>

    <script>
    document.querySelectorAll('.status-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const promoId = this.getAttribute('data-id');
            const newStatus = this.checked ? '1' : '0';

            // Send an AJAX request to update the status
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'adminDiscountUpdate.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    console.log('Status updated successfully');
                } else {
                    console.error('Failed to update status');
                }
            };
            xhr.send('promoId=' + promoId + '&status=' + newStatus);
        });
    });
</script>
</body>
</html>
