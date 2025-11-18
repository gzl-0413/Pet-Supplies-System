 
<?php
require_once '../includes/_base.php';  

$notification = '';

$stmt = $_db->query('SELECT id, name FROM category ORDER BY name');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (is_post()) {
    $old_category_id = req('old_category');
    $new_category = req('new_category');

    if ($new_category && $old_category_id == 'New') {
        $stmt = $_db->prepare('SELECT COUNT(*) FROM category WHERE name = :new_category');
        $stmt->bindValue(':new_category', $new_category, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            $notification = 'Error: Category name already exists. Please choose a different name.';
        } else {
            $stmt = $_db->prepare('INSERT INTO category (name) VALUES (:new_category)');
            $stmt->bindValue(':new_category', $new_category, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $notification = 'New category added successfully!';
            } else {
                $notification = 'Error adding new category.';
            }
        }
    } elseif ($old_category_id && $new_category) {
        $stmt = $_db->prepare('SELECT COUNT(*) FROM category WHERE name = :new_category AND id != :old_category_id');
        $stmt->bindValue(':new_category', $new_category, PDO::PARAM_STR);
        $stmt->bindValue(':old_category_id', $old_category_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            $notification = 'Error: Category name already exists. Please choose a different name.';
        } else {
            $stmt = $_db->prepare('UPDATE category SET name = :new_category WHERE id = :old_category_id');
            $stmt->bindValue(':new_category', $new_category, PDO::PARAM_STR);
            $stmt->bindValue(':old_category_id', $old_category_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $notification = 'Category updated successfully!';
            } else {
                $notification = 'Error updating category.';
            }
        }
    } else {
        $notification = 'Invalid input. Please try again.';
    }

    if (req('action') === 'batch_hide_unhide') {
        $ids = req('ids');
        if ($ids) {
            $ids = array_map('htmlspecialchars', $ids);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            // Toggle hidden status
            $stm = $_db->prepare("UPDATE product SET hidden = NOT hidden WHERE id IN ($placeholders)");
            $stm->execute($ids);

            $notification = 'Selected products updated.';
        } else {
            $notification = 'No products selected.';
        }
    }
}

$stmt = $_db->query('
    SELECT p.id, c.name AS category_name, p.name AS product_name, p.hidden 
    FROM product p
    JOIN category c ON p.category_id = c.id
    WHERE c.name IS NOT NULL AND p.name IS NOT NULL 
    ORDER BY c.name
');
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/_head.php';
include 'sideNav.php'; 
include 'adminHeader.php';  
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        table {
            width: 80%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
        
        /* Add a rule to ensure all category headers have the same style */
        tr.category-header td {
            background-color: #eaeaea; /* Apply background to all category-header cells */
        }

        .category-header {
            font-weight: bold;
            background-color: #eaeaea;
        }

        .status-column {
            width: 80px; /* Adjust width for the Status column */
        }

        .select-column {
            width: 40px; /* Adjust width for the Select column */
        }
    </style>
    <script>
        function toggleCategoryCheckbox(checkbox) {
            const rows = checkbox.closest('table').querySelectorAll('tbody tr[data-category="' + checkbox.dataset.category + '"] input[type="checkbox"]');
            rows.forEach(row => {
                row.checked = checkbox.checked;
            });
        }

        function validateCategoryInput(input) {
            // Remove non-alphabetic characters
            input.value = input.value.replace(/[^a-zA-Z\s]/g, '');
        }

        window.onload = function() {
            // Show notification message if exists
            const notification = <?= json_encode($notification); ?>;
            if (notification) {
                alert(notification);
            }
        };
    </script>
</head>
<body>
    <!-- Notification Panel -->
    <?php if ($notification): ?>    
        <div class="notification"><?= ($notification); ?></div>
    <?php endif; ?>

    <!-- Form to update category -->
    <form method="POST" class="form">
        <label for="category_select">Select Category:</label>
        <select name="old_category" id="category_select">
            <option value="">Select a category</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat['id']); ?>"><?= htmlspecialchars($cat['name']); ?></option>
            <?php endforeach; ?>
            <option value="New">Add New Category</option>
        </select>

        <label for="new_category">New Category Name:</label>
        <input type="text" name="new_category" id="new_category" required oninput="validateCategoryInput(this)">

        <button type="submit" class="update-category-btn">Submit</button>
    </form>

    <!-- Product List -->
    <form method="post" id="batch-hide-form">
        <table>
            <thead>
                <tr>
                    <th class="select-column">Select</th> <!-- Add class here -->
                    <th>Product Name</th>
                    <th>Category</th>
                    <th class="status-column">Status</th> <!-- New Status column -->
                </tr>
            </thead>
            <tbody>
                <?php
                $current_category = '';
                foreach ($products as $product):
                    // Group products by category
                    if ($product['category_name'] !== $current_category) {
                        $current_category = $product['category_name'];
                        echo "<tr class='category-header'><td><input type='checkbox' data-category='$current_category' onchange='toggleCategoryCheckbox(this)'></td><td colspan='3'>$current_category</td></tr>";
                    }
                ?>
                    <tr data-category="<?= htmlspecialchars($product['category_name']); ?>">
                        <td class="select-column"><input type="checkbox" name="ids[]" value="<?= htmlspecialchars($product['id']); ?>"></td> <!-- Add class here -->
                        <td><?= htmlspecialchars($product['product_name']); ?></td>
                        <td><?= htmlspecialchars($product['category_name']); ?></td>
                        <td class="status-column"><?= $product['hidden'] ? 'Hidden' : 'Unhidden'; ?></td> <!-- Display Status -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <section class="form-action">
            <button type="submit" name="action" value="batch_hide_unhide" class="hide-btn">Hide/Unhide Selected</button>
        </section>
    </form>

</body>
</html>

<?php
include '../includes/_foot.php'; // Include footer
?>