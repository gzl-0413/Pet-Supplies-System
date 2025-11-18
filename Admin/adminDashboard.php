<?php 
    require_once '../includes/_base.php';
    include 'sideNav.php';
    include 'adminHeader.php';

    $query = $_db->query("SELECT rate, COUNT(*) as count FROM review GROUP BY rate");
    $ratings = $query->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $data = [];
    $percentages = [];
    $totalReviews = 0;
    $totalRatingValue = 0;

    foreach ($ratings as $rating) {
        $totalReviews += $rating['count'];
        $totalRatingValue += $rating['rate'] * $rating['count'];
    }

    if ($totalReviews > 0) {
        $averageRating = round($totalRatingValue / $totalReviews,2);
        $satisfactionPercentage = round(($averageRating / 5) * 100, 2);
    } else {
        $satisfactionPercentage = 0;
    }

    foreach ($ratings as $rating) {
        $labels[] = $rating['rate'] . ' star' . ($rating['rate'] > 1 ? 's' : '');
        $data[] = (int)$rating['count'];
        $percentages[] = $totalReviews > 0 ? round(($rating['count'] / $totalReviews) * 100, 2) : 0;
    }

    $currentMonth = date('m');
    $currentYear = date('Y');
    $query = $_db->prepare("SELECT SUM(subtotal) as totalRevenue, COUNT(*) as totalTransactions 
                            FROM payment 
                            WHERE status = 'successful' 
                            AND MONTH(timestamp) = :month AND YEAR(timestamp) = :year");
    $query->execute(['month' => $currentMonth, 'year' => $currentYear]);
    $result = $query->fetch(PDO::FETCH_ASSOC);

    $totalRevenue = $result['totalRevenue'] ?? 0;
    $totalTransactions = $result['totalTransactions'] ?? 0;

    $averageRevenuePerTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

    $queryByDate = $_db->prepare("SELECT DATE(timestamp) as date, SUM(subtotal) as dailyRevenue 
                                FROM payment 
                                WHERE status = 'successful' 
                                AND MONTH(timestamp) = :month AND YEAR(timestamp) = :year
                                GROUP BY DATE(timestamp)");
    $queryByDate->execute(['month' => $currentMonth, 'year' => $currentYear]);
    $revenuesByDate = $queryByDate->fetchAll(PDO::FETCH_ASSOC);

    $dates = [];
    $revenues = [];
    foreach ($revenuesByDate as $revenue) {
        $dates[] = $revenue['date'];
        $revenues[] = $revenue['dailyRevenue'];
    }

    $query = $_db->query("
        SELECT o.userId, COUNT(o.orderId) AS totalOrders, SUM(o.subtotal) AS totalPurchase, 
            (SUM(o.subtotal) / COUNT(o.orderId)) AS averagePurchase
        FROM orders o
        JOIN payment p ON o.orderId = p.orderId
        WHERE p.status = 'successful'
        GROUP BY o.userId
        ORDER BY totalPurchase DESC
        LIMIT 5
    ");
    $topCustomers = $query->fetchAll(PDO::FETCH_ASSOC);

    $queryTotalRevenue = $_db->query("SELECT SUM(subtotal) AS totalRevenue FROM payment WHERE status = 'successful'");
    $totalRevenueResult = $queryTotalRevenue->fetch(PDO::FETCH_ASSOC);
    $totalRevenue = (float) $totalRevenueResult['totalRevenue'];

    $customerIds = [];
    $purchaseTotals = [];
    $averagePurchases = [];
    $orderCounts = [];
    $revenuePercentages = [];

    foreach ($topCustomers as $customer) {
        $customerIds[] = $customer['userId'];
        $purchaseTotals[] = (float)$customer['totalPurchase'];
        $averagePurchases[] = (float)$customer['averagePurchase'];
        $orderCounts[] = (int)$customer['totalOrders'];

        $revenuePercentages[] = $totalRevenue > 0 ? round(($customer['totalPurchase'] / $totalRevenue) * 100, 2) : 0;
    }

    $query = $_db->query("
        SELECT o.productName, SUM(o.quantity) AS totalQuantity 
        FROM orders o
        JOIN payment p ON o.orderId = p.orderId 
        WHERE p.status = 'successful' 
        GROUP BY o.productId, o.productName 
        ORDER BY totalQuantity DESC 
        LIMIT 5");

    $salesByProduct = $query->fetchAll(PDO::FETCH_ASSOC);

    $productNames = [];
    $productQuantities = [];

    foreach ($salesByProduct as $product) {
        $productNames[] = $product['productName'];
        $productQuantities[] = (int)$product['totalQuantity'];
    }

    $totalProductQuantity = array_sum($productQuantities);
    $productPercentages = [];

    foreach ($salesByProduct as $product) {
        $percentage = $totalProductQuantity > 0 ? round(($product['totalQuantity'] / $totalProductQuantity) * 100, 2) : 0;
        $productPercentages[] = $percentage;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.0"></script>
    <link rel="stylesheet" href="../css/adminDashboard.css">
    <title>Admin Dashboard</title>
</head>
<body>
    <div class="dashboard">
    <h1>Dashboard</h1>
    <div class="dashboard-container">
        <div class="dashboard-box">
            <h3>Current Month's Revenue Summary</h3>
            <div class="revenue-display">
                Total Revenue: <strong>RM<?php echo number_format($totalRevenue, 2); ?></strong><br>
                Total Transactions: <strong><?php echo $totalTransactions; ?></strong><br>
                Average Revenue Per Transaction: <strong>RM<?php echo number_format($averageRevenuePerTransaction, 2); ?></strong><br>
            </div>
            <canvas id="revenueChart"></canvas>
        </div>

        <div class="dashboard-box">
            <h3>Customer Satisfaction</h3>
            <div class="percentage-display">
                Average Rating: <strong><?php echo $averageRating; ?> Stars</strong><br>
                Overall Satisfaction: <strong><?php echo $satisfactionPercentage; ?>%</strong>
            </div>
            <canvas id="ratingChart"></canvas>
        </div>

        <div class="dashboard-box">
            <h3>Top 5 Products by Sales</h3>
            <table>
                <tr>
                    <th>Product Name</th>
                    <th>Total Quantity Sold</th>
                    <th>Percentage of Total Sales</th>
                </tr>
                <?php foreach ($salesByProduct as $index => $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['productName']); ?></td>
                    <td><?php echo (int)$product['totalQuantity']; ?></td>
                    <td><?php echo $productPercentages[$index]; ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
            <div class="chart-container">
                <canvas id="salesChart" data-product-names='<?php echo json_encode($productNames); ?>' data-product-quantities='<?php echo json_encode($productQuantities); ?>'></canvas>
            </div>
        </div>

        <div class="dashboard-box">
            <h3>Top 5 Customers by Purchase Value</h3>
            <table>
                <tr>
                    <th>Customer ID</th>
                    <th>Total Purchases (RM)</th>
                    <th>Average Purchase (RM)</th>
                    <th>Total Orders</th>
                    <th>Percentage of Total Revenue</th>
                </tr>
                <?php foreach ($topCustomers as $index => $customer): ?>
                <tr>
                    <td><?php echo $customerIds[$index]; ?></td>
                    <td>RM<?php echo number_format($purchaseTotals[$index], 2); ?></td>
                    <td>RM<?php echo number_format($averagePurchases[$index], 2); ?></td>
                    <td><?php echo $orderCounts[$index]; ?></td>
                    <td><?php echo $revenuePercentages[$index]; ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
            <canvas id="topCustomersChart"></canvas>
        </div>
    </div>

    <!-- Hidden divs to pass data to the JS file -->
    <div id="chart-data-labels" style="display: none;"><?php echo json_encode($labels); ?></div>
    <div id="chart-data" style="display: none;"><?php echo json_encode($data); ?></div>
    <div id="chart-data-percentages" style="display: none;"><?php echo json_encode($percentages); ?></div>

    <div id="chart-data-dates" style="display: none;"><?php echo json_encode($dates); ?></div>
    <div id="chart-data-revenues" style="display: none;"><?php echo json_encode($revenues); ?></div>

    <div id="chart-data-customers" style="display: none;"><?php echo json_encode($customerIds); ?></div>
    <div id="chart-data-purchases" style="display: none;"><?php echo json_encode($purchaseTotals); ?></div>
    <div id="chart-data-avg-purchases" style="display: none;"><?php echo json_encode($averagePurchases); ?></div>
    <div id="chart-data-order-counts" style="display: none;"><?php echo json_encode($orderCounts); ?></div>
    <div id="chart-data-revenue-percentages" style="display: none;"><?php echo json_encode($revenuePercentages); ?></div>

    <div id="chart-data-product-names" style="display: none;"><?php echo json_encode($productNames); ?></div>
    <div id="chart-data-product-quantities" style="display: none;"><?php echo json_encode($productQuantities); ?></div>

    <script src="../js/adminDashboard.js"></script>
                </div>
</body>
</html>
