<?php

include_once '../includes/_base.php';  

define('LOW_STOCK_THRESHOLD', 5);  

$notification = '';

if (is_post()) {
    $product_id = req('product_id');
    $new_stock = req('stock');

    // Validate inputs
    if ($product_id && is_numeric($new_stock)) {
        $stmt = $_db->prepare('UPDATE product SET quantity = ? WHERE id = ?');
        $stmt->execute([$new_stock, $product_id]);

        if ($stmt->rowCount() > 0) {
            $notification = 'Stock updated successfully!';
        } else {
            $notification = 'Error updating stock.';
        }
    } else {
        $notification = 'Invalid input. Please try again.';
    }
}

$stmt = $_db->query('
    SELECT p.id, p.name, c.name AS category, p.quantity
    FROM product p
    JOIN category c ON p.category_id = c.id
    WHERE p.name IS NOT NULL AND p.name != "" 
    ORDER BY c.name, p.name
');
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check for low stock products
$low_stock_products = array_filter($products, function($product) {
    return $product['quantity'] <= LOW_STOCK_THRESHOLD;
});

include '../includes/_head.php';
include 'sideNav.php';
include 'adminHeader.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        .low-stock {
            background-color: #f8d7da !important; /* Ensure this color is applied */
            color: #721c24;
        }
        table {
            width: 82%; /* Set a percentage or specific width */
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            cursor: pointer; /* Add cursor pointer to indicate clickable rows */
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>

    <!-- Form to update stock -->
    <form method="POST" class="form">
        <label for="product_id">Select Product:</label>
        <select name="product_id" id="product_id">
            <option value="">Select a product</option>
            <!-- Options will be dynamically filled by JavaScript -->
        </select>

        <label for="stock">New Stock Quantity:</label>
        <input type="number" name="stock" id="stock" min="0" required>

        <button type="submit" class="update-stock-btn">Update Stock</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Category</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr data-id="<?= $product['id']; ?>" data-stock="<?= $product['quantity']; ?>" class="<?= $product['quantity'] <= LOW_STOCK_THRESHOLD ? 'low-stock' : ''; ?>">
                    <td><?= htmlspecialchars($product['name']); ?></td>
                    <td><?= htmlspecialchars($product['category']); ?></td>
                    <td><?= htmlspecialchars($product['quantity']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var rows = document.querySelectorAll('table tbody tr');
            var productSelect = document.getElementById('product_id');
            var stockInput = document.getElementById('stock');
            
            var phpNotification = "<?= addslashes($notification); ?>"; // Get PHP notification message

            if (phpNotification) {
                alert(phpNotification);  // Use JavaScript alert to display notification
            }

            rows.forEach(function(row) {
                row.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    var stock = this.getAttribute('data-stock');
                    var productName = this.children[0].textContent;

                    // Update select element with selected product info
                    productSelect.innerHTML = ''; // Clear previous options
                    var option = document.createElement('option');
                    option.value = id;
                    option.textContent = productName + ' (Stock: ' + stock +')';
                    productSelect.appendChild(option);
                    productSelect.value = id; // Set value to selected product ID
                    stockInput.value = stock; // Set the stock input field to the selected product's stock
                });
            });
        });
    </script>
</body>
</html>

<?php
include '../includes/_foot.php';
?>