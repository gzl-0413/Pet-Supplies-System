<?php
include '../includes/_base.php';

// Retrieve search and sorting parameters
$search = req('search');
$sort = req('sort') ?: 'name';
$page = max(1, (int)req('page', 1));
$perPage = 12;
$start = ($page - 1) * $perPage;

$categoryFilter = '';  
$params = []; 

// Get category filter
$categoryId = req('category') ?? ''; 
if (!empty($categoryId)) {
    $categoryFilter = 'AND category_id = :category_id';
    $params[':category_id'] = (int)$categoryId; 
}

// Retrieve min and max price values
$minPrice = req('minPrice');
$maxPrice = req('maxPrice');

$priceFilter = ''; // Initialize an empty string for the price filter
if (!empty($minPrice)) {
    $priceFilter .= ' AND oriPrice >= :minPrice';
    $params[':minPrice'] = (float)$minPrice;
}
if (!empty($maxPrice)) {
    $priceFilter .= ' AND oriPrice <= :maxPrice';
    $params[':maxPrice'] = (float)$maxPrice;
}

try {
    // Prepare and execute the main query
    $stm = $_db->prepare("
        SELECT * FROM product
        WHERE name LIKE :search $categoryFilter $priceFilter AND hidden = 0
        ORDER BY $sort
        LIMIT :start, :perPage
    ");
    $stm->bindValue(':search', "%$search%", PDO::PARAM_STR);

    if (!empty($categoryId)) {
        $stm->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
    }
    if (!empty($minPrice)) {
        $stm->bindValue(':minPrice', (float)$minPrice, PDO::PARAM_STR);
    }
    if (!empty($maxPrice)) {
        $stm->bindValue(':maxPrice', (float)$maxPrice, PDO::PARAM_STR);
    }

    $stm->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stm->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);

    $stm->execute();
    $products = $stm->fetchAll();

    // Prepare and execute the total count query
    $totalQuery = $_db->prepare('
        SELECT COUNT(*) FROM product WHERE name LIKE :search ' . $categoryFilter . $priceFilter . ' AND hidden = 0'
    );

    $totalQuery->bindValue(':search', "%$search%", PDO::PARAM_STR);
    if (!empty($categoryId)) {
        $totalQuery->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
    }
    if (!empty($minPrice)) {
        $totalQuery->bindValue(':minPrice', (float)$minPrice, PDO::PARAM_STR);
    }
    if (!empty($maxPrice)) {
        $totalQuery->bindValue(':maxPrice', (float)$maxPrice, PDO::PARAM_STR);
    }

    $totalQuery->execute();
    $total = $totalQuery->fetchColumn();
    $totalPages = ceil($total / $perPage);
    $categories = $_db->query("SELECT id, name FROM category ORDER BY name")->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}

// Alert message for order cancellation success
if (isset($_GET['message']) && $_GET['message'] === 'cancel_success') {
    echo "<script>
        alert('Order cancellation was successful.');
        // Remove the message parameter from the URL
        if (window.history.replaceState) {
            const url = new URL(window.location);
            url.searchParams.delete('message');
            window.history.replaceState({}, document.title, url);
        }
    </script>";
}
?>

<?php include 'header.php'; ?>

<head>
<title>Product Page</title>

<link rel="stylesheet" href="../CSS/product.css">
</head>
<body>
    <header>
        <div class="header-container">
        <form method="get" class="form">
            <div class="search-bar">
                <label for="category" class="category">Category</label>
                <?php 
                    $categories = get_categories(); // Fetch categories from the new table
                    echo '<select name="category" id="category" class="categorySelect">';
                    echo '<option value="">All Categories</option>';
                    foreach ($categories as $cat) {
                        $selected = ($cat->id == htmlspecialchars(req('category'))) ? 'selected' : '';
                        echo "<option value=\"{$cat->id}\" $selected>{$cat->name}</option>";
                    }
                    echo '</select>';
                ?>
             
                
                <!-- Add the input fields for the price range -->
                <label for="minPrice"  class="category">Min Price:</label>
                <input type="number" name="minPrice" id="minPrice" value="<?= htmlspecialchars(req('minPrice')) ?>" min="0" step="0.01">

                <label for="maxPrice"  class="category">Max Price:</label>
                <input type="number" name="maxPrice" id="maxPrice" value="<?= htmlspecialchars(req('maxPrice')) ?>" min="0" step="0.01" style="margin-right: 50px;">
                <?= html_text('search', 'maxlength="100"', $search) ?>
                <button type="submit">Search</button> 
                <button type="button" class="clear-button" onclick="window.location.href='?sort=<?= htmlspecialchars($sort) ?>'">Clear Search</button>
                
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                <input type="hidden" name="page" value="<?= (int)req('page', 1) ?>">
            </div>
        </form>
        </div>
    </header>
    
    <section class="product-grid">
    <?php foreach ($products as $product): ?>
        <div class="product-card">
            <img src="/uploads/<?= htmlspecialchars($product->photo) ?>" alt="" width="100">
            <p class="product-title"><?= htmlspecialchars($product->name) ?></p>
            <p class="price">RM<?= htmlspecialchars($product->oriPrice) ?></p>
            <button class="buy-now" onclick="window.location.href='productPage.php?productId=<?= htmlspecialchars($product->id) ?>'">View</button>
        </div>
    <?php endforeach ?>
    </section>

    <!-- Pagination Controls -->
    <div class="pagination-controls">
        <?php if ($page > 1): ?>
            <a href="?search=<?= urlencode($search) ?>&category=<?= urlencode($categoryId) ?>&minPrice=<?= urlencode($minPrice) ?>&maxPrice=<?= urlencode($maxPrice) ?>&sort=<?= urlencode($sort) ?>&page=<?= $page - 1 ?>" class="pagination-button">Previous</a>
        <?php endif; ?>

        <span>Page <?= $page ?> of <?= $totalPages ?></span>

        <?php if ($page < $totalPages): ?>
            <a href="?search=<?= urlencode($search) ?>&category=<?= urlencode($categoryId) ?>&minPrice=<?= urlencode($minPrice) ?>&maxPrice=<?= urlencode($maxPrice) ?>&sort=<?= urlencode($sort) ?>&page=<?= $page + 1 ?>" class="pagination-button">Next</a>
        <?php endif; ?>
    </div>
</body>
</html>

<script>
function confirmPurchase(productId, productName, quantity, price) {
    if (confirm(`Are you sure you want to buy ${productName}?`)) {
        const url = `buyNowpPyment.php?product_id=${productId}&product_name=${encodeURIComponent(productName)}&quantity=${quantity}&price=${price}`;
        window.location.href = url;
    }
}
</script>

<?php include 'footer.php'; ?>
