<?php

require_once '../includes/_base.php';
 
 
$search = req('search') ?? ''; // Default to empty string if not set
$sort = req('sort') ?: 'name'; // Default sorting by name
$page = max(1, (int)req('page', 1)); // Default to page 1 if not set
$perPage = 5;
$start = ($page - 1) * $perPage;

// Handle CRUD Actions
$id = req('id');
$action = req('action');
$action = reqCx('action');


if (is_post()) {
   
    $ids = reqCx('ids');
    if ($action === 'batch_hide_unhide') {
       
        if ($ids) {
            $ids = array_map('htmlspecialchars', $ids);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            // Toggle hidden status
            $stm = $_db->prepare("UPDATE product SET hidden = NOT hidden WHERE id IN ($placeholders)");
            $stm->execute($ids);

            temp('info', 'Selected products updated');
        }
    }
}

// Pagination and Sorting
try {
    $categoryId = req('category') ?? ''; // Default to empty string if not set
    $minPrice = req('min_price') ?? ''; // Default to empty string if not set
    $maxPrice = req('max_price') ?? ''; // Default to empty string if not set

    $whereClauses = ['name LIKE :search'];
    $params = [':search' => "%$search%"];

    if ($categoryId) {
        $whereClauses[] = 'category_id = :category_id';
        $params[':category_id'] = (int)$categoryId; // Ensure it's an integer
    }

    if ($minPrice !== '' && $maxPrice !== '') {
        $whereClauses[] = 'oriPrice BETWEEN :minPrice AND :maxPrice';
        $params[':minPrice'] = $minPrice;
        $params[':maxPrice'] = $maxPrice;
    }

    $whereSql = implode(' AND ', $whereClauses);

    $stm = $_db->prepare("
        SELECT * FROM product
        WHERE $whereSql
        ORDER BY $sort
        LIMIT :start, :perPage
    ");
    foreach ($params as $key => $value) {
        $stm->bindValue($key, $value);
    }
    $stm->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stm->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
    $stm->execute();
    $products = $stm->fetchAll(PDO::FETCH_OBJ);

    $total = $_db->prepare("SELECT COUNT(*) FROM product WHERE $whereSql");
    foreach ($params as $key => $value) {
        $total->bindValue($key, $value);
    }
    $total->execute();
    $total = $total->fetchColumn();
    $totalPages = ceil($total / $perPage);
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}

include '../includes/_head.php';  
include 'sideNav.php';
include 'adminHeader.php'; ?>

 
<form method="get" class="form">
    <div class="form-groups">
        <label for="search">Search</label>
        <?= html_text('search', 'maxlength="100"', htmlspecialchars($search)) ?>
    </div>

    <div class="form-groups">
        <label for="category">Category</label>
        <?php 
        $categories = get_categories(); // Fetch categories from the new table
        echo '<select name="category" id="category">';
        echo '<option value="">All Categories</option>';
        foreach ($categories as $cat) {
            $selected = ($cat->id == htmlspecialchars(req('category'))) ? 'selected' : '';
            echo "<option value=\"{$cat->id}\" $selected>{$cat->name}</option>";
        }
        echo '</select>';
        ?>
    </div>

    <div class="form-groups">
        <label for="min_price">Price Range</label>
        <?= html_text('min_price', 'maxlength="10"', htmlspecialchars(req('min_price'))) ?> to
        <?= html_text('max_price', 'maxlength="10"', htmlspecialchars(req('max_price'))) ?>
    </div>

    <div class="form-groups">
        <label for="sort">Sort By</label>
        <?= html_select_cx('sort', [
            ['id' => 'name', 'name' => 'Name'],
            ['id' => 'oriPrice', 'name' => 'Price']
        ], htmlspecialchars($sort)) ?>
    </div>
   
    <section class="form-action">
        <button type="submit">Search</button>
        <button type="reset" class="reset-btn" onclick="window.location.href='list.php'">Reset</button>
      
    </section>
    
</form>

<form method="post" id="batch-hide-unhide-form">
<table>
        <thead>
            <tr>
                <th></th>
                <th>Name</th>
                <th>Price</th>
                <th>Category</th>
                <th>Photo</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr style="<?= $product->hidden ? 'background-color: #d9d9d9; color: #a0a0a0;' : '' ?>">
                <td><input type="checkbox" name="ids[]" value="<?= htmlspecialchars($product->id) ?>"></td>
                <td><?= htmlspecialchars($product->name) ?></td>
                <td><?= htmlspecialchars($product->oriPrice) ?></td>
                <td>
                    <?php 
                  
                    $categoryName = $_db->prepare("SELECT name FROM category WHERE id = :category_id");
                    $categoryName->bindValue(':category_id', $product->category_id);
                    $categoryName->execute();
                    echo htmlspecialchars($categoryName->fetchColumn());
                    ?>
                </td>
                <td>
                    <?php if ($product->photo): ?>
                        <img src="../uploads/<?= htmlspecialchars($product->photo) ?>" alt="Photo" width="100">
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit.php?id=<?= htmlspecialchars($product->id) ?>" class="table-button">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <section class="form-actions">
  <a class="upload-btn" href="upload.php" >Upload Product</a>
        <button type="submit" name="action" value="batch_hide_unhide" class="hide-unhide-btn">Hide/Unhide Selected</button>
    </section>
</form>

<!-- Pagination -->
<nav>
    <ul class="pagination">
        <!-- Previous Page Link -->
        <li class="<?= $page > 1 ? '' : 'disabled' ?>">
            <a href="<?= $page > 1 ? '?page=' . ($page - 1) . '&search=' . urlencode($search) . '&category=' . urlencode(req('category')) . '&min_price=' . urlencode(req('min_price')) . '&max_price=' . urlencode(req('max_price')) . '&sort=' . urlencode($sort) : '#' ?>" aria-label="Previous">
                &laquo;
            </a>
        </li>

        <!-- Current Page Number -->
        <li class="active">
            <span><?= $page ?></span>
        </li>

        <!-- Next Page Link -->
        <li class="<?= $page < $totalPages ? '' : 'disabled' ?>">
            <a href="<?= $page < $totalPages ? '?page=' . ($page + 1) . '&search=' . urlencode($search) . '&category=' . urlencode(req('category')) . '&min_price=' . urlencode(req('min_price')) . '&max_price=' . urlencode(req('max_price')) . '&sort=' . urlencode($sort) : '#' ?>" aria-label="Next">
                &raquo;
            </a>
        </li>
    </ul>
</nav>




<?php
include '../includes/_foot.php'; // Ensure correct path?>