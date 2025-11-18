<?php
require_once '../includes/_base.php'; // Ensure correct path

 include 'sideNav.php'; 

$id = req('id');
$action = req('action') ?: 'edit';

function check_product_exists($name, $db, $exclude_id = null) {
    $query = 'SELECT COUNT(*) FROM product WHERE name = ?';
    $params = [$name];

    // Add condition to exclude current product ID if provided
    if ($exclude_id) {
        $query .= ' AND id != ?';
        $params[] = $exclude_id;
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
}

if (is_post() && $action == 'edit') {
    $name = req('name');
    $oriPrice = req('oriPrice');
    $category_id = req('category');
    $description = req('description'); 
    $f = $_FILES['photo'] ?? null; 
    $photo = req('existing_photo');
    
    $_err = []; // Initialize the error array

    // Validate name
    if (empty($name)) $_err['name'] = 'Required';
    elseif (strlen($name) > 100) $_err['name'] = 'Maximum 100 characters';  

    // Check if product with the same name already exists, excluding the current product ID
    if (check_product_exists($name, $_db, $id)) {
        $_err['name'] = 'Product already exists';
    }

    if (empty($description)) $_err['description'] = 'Required';
elseif (strlen($description) > 350) $_err['description'] = 'Maximum 500 characters';

    // Validate oriPrice
    if (empty($oriPrice)) $_err['oriPrice'] = 'Required';
    elseif (!is_numeric($oriPrice)) $_err['oriPrice'] = 'Must be numeric';
    elseif ($oriPrice < 1 || $oriPrice > 999.99) $_err['oriPrice'] = 'Must be between 1 - 999.99';

    // Validate category
    if (empty($category_id)) $_err['category'] = 'Required';

    // Validate file
    if ($f && $f['error'] === UPLOAD_ERR_OK) {
        if (!str_starts_with($f['type'], 'image/')) $_err['photo'] = 'Must be an image';
        elseif ($f['size'] > 1 * 1024 * 1024) $_err['photo'] = 'Maximum 1MB';
    } elseif ($f['error'] !== UPLOAD_ERR_NO_FILE) {
        $_err['photo'] = 'File upload error';
    }

    
    if (empty($_err)) {
        // Handle photo upload
        if ($f && $f['error'] === UPLOAD_ERR_OK) {
            if ($photo) unlink("../uploads/$photo");
            $photo = uniqid() . '.jpg';
            require_once '../lib/SimpleImage.php'; // Adjusted path
            $img = new SimpleImage();
            $img->fromFile($f['tmp_name'])
                ->thumbnail(200, 200)
                ->toFile("../uploads/$photo", 'image/jpeg');
        }

        // Update the product
        $stm = $_db->prepare('
            UPDATE product
            SET name = ?, oriPrice = ?, category_id = ?, photo = ? ,description = ?
            WHERE id = ?
        ');
        $stm->execute([$name, $oriPrice, $category_id, $photo,$description, $id]);
        temp('info', 'Record updated');
        redirect('list.php');
    }
} 

// Fetch all categories for the dropdown
$stm = $_db->prepare('SELECT id, name FROM category');
$stm->execute();
$categories = $stm->fetchAll(PDO::FETCH_ASSOC);

// Fetch existing product data for edit
$stm = $_db->prepare('SELECT p.*, c.name AS category_name FROM product p LEFT JOIN category c ON p.category_id = c.id WHERE p.id = ?');
$stm->execute([$id]);
$product = $stm->fetch();

require_once '../includes/_head.php'; // Ensure correct path
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <style>
        .edit-form {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-top: 10px;
        }
        .edit-form.highlight {
            border-color: green;
        }
        #photo-preview {
            margin-top: 10px;
            width: 150px;
            height: 150px;
            border-radius: 8px;
            overflow: hidden;
        }
        #photo-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>

    <!-- Main content area -->
    <div class="content">

        <!-- Edit Product Form -->
        <form id="upload-form" method="post" class="edit-form" enctype="multipart/form-data">
            <input type="hidden" name="existing_photo" value="<?= htmlspecialchars($product->photo) ?>">

            <label for="name">Name</label>
            <?= html_text('name', '', $product->name) ?>
            <span class="error" id="error_name"><?= $_err['name'] ?? ''; ?></span>

            <label for="oriPrice">Price</label>
            <?= html_number('oriPrice', 1, 999.99, 1, $product->oriPrice) ?>
            <span class="error" id="error_oriPrice"><?= $_err['oriPrice'] ?? ''; ?></span>

            <label for="category">Category</label>
            <select name="category" id="category">
                <option value="">Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category['id']) ?>" <?= ($category['id'] == $product->category_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="error" id="error_category"><?= $_err['category'] ?? ''; ?></span>


            <label for="description">Description</label>
<textarea name="description" id="description" rows="4"><?= htmlspecialchars($product->description) ?></textarea>
<span class="error" id="error_description"><?= $_err['description'] ?? ''; ?></span>

            <label for="photo">Photo</label>
            <div class="upload">
                <!-- Hidden file input -->
                <?= html_file('photo', 'image/*', 'hidden') ?>
                <!-- Image that triggers file input -->
                <div id="photo-preview">
                    <img src="../uploads/<?= htmlspecialchars($product->photo) ?>" alt="Product Photo">
                </div>
                <!-- Drag-and-drop area -->
                <div id="drop-area">
                    <p>Drag & Drop your file here or click on the image to select</p>
                </div>
            </div>
            <span class="error" id="error_photo"><?= $_err['photo'] ?? ''; ?></span>

            <section>
                <button name="action" value="edit">Update</button>
                <button type="reset">Reset</button>
            </section>
        </form>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const uploadForm = document.getElementById('upload-form');
            const photoInput = document.querySelector('input[type="file"]');
            const photoPreview = document.getElementById('photo-preview');
            const dropArea = document.getElementById('drop-area');

            // Highlight drop area on drag
            const highlightDropArea = () => dropArea.classList.add('highlight');
            const resetDropArea = () => dropArea.classList.remove('highlight');

            // Preview photo function
            const previewPhoto = (file) => {
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        photoPreview.innerHTML = ''; // Clear previous preview
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        photoPreview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            };

            // Handle file input change
            photoInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) previewPhoto(file);
            });

            // Handle click on photo preview to trigger file input
            photoPreview.addEventListener('click', () => photoInput.click());

            // Handle drag and drop events
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    highlightDropArea();
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    resetDropArea();
                });
            });

            dropArea.addEventListener('drop', (event) => {
                const file = event.dataTransfer.files[0];
                if (file && file.type.startsWith('image/')) {
                    photoInput.files = event.dataTransfer.files; // Update file input
                    previewPhoto(file);
                }
            });

            // Reset form and preview
            uploadForm.addEventListener('reset', () => {
                photoPreview.innerHTML = ''; // Clear preview
                photoInput.value = ''; // Clear file input
            });
        });
    </script>

</body>
</html>

<?php
require_once '../includes/_foot.php'; // Ensure correct path
?>
