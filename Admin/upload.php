<?php
 include_once '../includes/_base.php';

 function check_product_exists($name, $db) {
    $stmt = $db->prepare('SELECT COUNT(*) FROM product WHERE name = ?');
    $stmt->execute([$name]);
    return $stmt->fetchColumn() > 0;
}
if (!function_exists('get_categories')) {
    function get_categories($db) {
        $stmt = $db->prepare('SELECT id, name FROM category ORDER BY name');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ); // Fetch as objects
    }
}

if (is_post() && req('action') == 'upload') {
    $name = req('name');
    $price = req('price');
    $stock = req('stock');
    $description = req('description');
    $selected_category_id = req('category'); 
    $new_category = req('new_category');
    $photo = $_FILES['photo'] ?? [];
    
    $_err = []; // Initialize the error array

   
    if (empty($name)) $_err['name'] = 'Required';
    elseif (strlen($name) > 100) $_err['name'] = 'Maximum 100 characters';

    
     if (check_product_exists($name, $_db)) {
        $_err['name'] = 'Product already exists';
    }

    if (empty($price)) $_err['price'] = 'Required';
    elseif (!is_numeric($price)) $_err['price'] = 'Must be numeric';
    elseif ($price < 0.01 || $price > 999.99) $_err['price'] = 'Must be between 0.01 - 99.99';

    if (empty($stock)) $_err['stock'] = 'Required';
    elseif (!is_numeric($stock)) $_err['stock'] = 'Must be numeric';
    elseif ($stock < 0) $_err['stock'] = 'Cannot be negative';



    if (empty($description)) $_err['description'] = 'Required';
elseif (strlen($description) > 350) $_err['description'] = 'Maximum 500 characters';

  
    if ($photo['error'] === UPLOAD_ERR_OK) {
        if (!str_starts_with($photo['type'], 'image/')) $_err['photo'] = 'File must be an image';
        elseif ($photo['size'] > 1 * 1024 * 1024) $_err['photo'] = 'File size exceeds 1MB';
    } elseif ($photo['error'] !== UPLOAD_ERR_NO_FILE) {
        $_err['photo'] = 'File upload error';
    }

    // If there are no errors, process the file and insert into database
    if (empty($_err)) {
        $photo_file = null;
        if ($photo['error'] === UPLOAD_ERR_OK) {
            $photo_file = uniqid() . '.jpg';
            require_once '../lib/SimpleImage.php'; // Adjusted path
            $img = new SimpleImage();
            $img->fromFile($photo['tmp_name'])
                ->thumbnail(200, 200)
                ->toFile("../uploads/$photo_file", 'image/jpeg');
        }
    $last_id_stmt = $_db->query('SELECT id FROM product ORDER BY id DESC LIMIT 1');
        $last_id_row = $last_id_stmt->fetch(PDO::FETCH_ASSOC);
        if ($last_id_row) {
            $last_id = $last_id_row['id'];
            // Extract the numeric part and increment it
            $num = (int)substr($last_id, 1) + 1;
            $new_id = 'p' . str_pad($num, 3, '0', STR_PAD_LEFT); // Generate the new ID like p001, p002, etc.
        } else {
            $new_id = 'p001';
        }

        $stm = $_db->prepare('
            INSERT INTO product (id,name, oriPrice, quantity, category_id, photo , description) VALUES (?,?, ?, ?, ?, ? ,?)
        ');
        if ($stm->execute([$new_id,$name, $price, $stock, $selected_category_id, $photo_file, $description])) {
            $notification = 'Product uploaded successfully!';
        } else {
            $notification = 'Error uploading product.';
        }

         redirect('list.php');
    }
}
 include '../includes/_head.php'; 
 include 'sideNav.php';  


//   display_notification($notification);
   ?>



<!-- Upload Product Form -->
<form method="post" class="form" enctype="multipart/form-data" id="upload-form">
    <div id="drop-zone">
        <label for="name">Name</label>
        <input type="text" name="name" id="name" required maxlength="100">
        <span class="error" id="error_name"><?php echo $_err['name'] ?? ''; ?></span>
        
        <label for="price">Price</label>
        <input type="number" name="price" id="price" min="0.01" max="999.99" step="0.01" required>
        <span class="error" id="error_price"><?php echo $_err['price'] ?? ''; ?></span>
        
        <label for="stock">Stock</label>
        <input type="number" name="stock" id="stock" min="0" required>
        <span class="error" id="error_stock"><?php echo $_err['stock'] ?? ''; ?></span>
        
        <label for="category">Category</label>
<select name="category" id="category" required>
    <option value="">Select Category</option>
    <?php
    $categories = get_categories($_db);
    foreach ($categories as $cat) {
        echo "<option value=\"{$cat->id}\">{$cat->name}</option>"; // Use object property syntax
    }
    ?>
</select>
<span class="error" id="error_category"><?php echo $_err['category'] ?? ''; ?></span>

<label for="description">Description</label>
<textarea name="description" id="description" rows="4" required></textarea>
<span class="error" id="error_description"><?php echo $_err['description'] ?? ''; ?></span>



        <label for="photo">Photo</label>
        <input type="file" name="photo" id="photo" accept="image/*" required>
        <span class="error" id="error_photo"><?php echo $_err['photo'] ?? ''; ?></span>

        <!-- Image preview -->
        <div id="photo-preview" class="photo-preview"></div>
    </div>


    <div id="form-buttons">
    <button type="submit" name="action" value="upload" class="submit-btn">Submit</button>
        <button type="reset">Reset</button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const categorySelect = document.getElementById('category');
        const newCategoryContainer = document.getElementById('new-category-container');
        const newCategoryInput = document.getElementById('new_category');

        // Show/Hide new category input field based on selection
        categorySelect.addEventListener('change', function() {
            if (this.value === 'Other') {
                newCategoryContainer.style.display = 'block';
                newCategoryInput.required = true;
            } else {
                newCategoryContainer.style.display = 'none';
                newCategoryInput.required = false;
            }
        });

        const uploadForm = document.getElementById('upload-form');
        const photoInput = document.getElementById('photo');
        const photoPreview = document.getElementById('photo-preview');
        const dropZone = document.getElementById('drop-zone');

        // Drag and Drop Feedback Styling
        const highlightDropZone = () => dropZone.style.border = '2px dashed green';
        const resetDropZone = () => dropZone.style.border = '2px dashed #ccc';

        // Photo preview function
        const previewPhoto = (file) => {
            photoPreview.innerHTML = ''; // Clear previous preview
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '100%';
                    img.style.maxHeight = '100%';
                    img.style.objectFit = 'cover';
                    img.style.border = '1px solid #ddd';
                    img.style.borderRadius = '8px';
                    photoPreview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        };

        // File input change event
        photoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) previewPhoto(file);
        });

        // Drag and drop photo upload
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (event) => {
                event.preventDefault();
                event.stopPropagation();
                highlightDropZone();
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (event) => {
                event.preventDefault();
                event.stopPropagation();
                resetDropZone();
            });
        });

        dropZone.addEventListener('drop', (event) => {
            const files = event.dataTransfer.files;
            if (files.length) {
                photoInput.files = files; // Assign dropped files to input
                previewPhoto(files[0]);
            }
        });

        // Click to choose file via image preview
        photoPreview.addEventListener('click', () => {
            photoInput.click();
        });
    });
</script>
<style>
    .error {
        color: red;
        font-size: 0.9em;
    }
    #upload-form {
        border: 2px dashed #ccc;
        padding: 20px;
        margin: 0px auto;
        max-width: 600px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    #drop-zone {
        border: 2px dashed #ccc;
        padding: 10px;
        background-color: #fafafa;
        cursor: pointer;
        transition: border 0.3s ease;
        width: 100%; /* Ensure it takes up the full width */
        box-sizing: border-box;
    }
    #drop-zone.drag-over {
        border-color: green;
    }
    .photo-preview {
        margin-top: 10px;
        width: 100%; /* Make it fit the width of the form */
        height: 200px; /* Fixed height */
        border: 1px solid #ddd; /* Border to define the area */
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden; /* Hide overflow to keep layout tidy */
        background: #f9f9f9;
        box-sizing: border-box;
    }
    .photo-preview img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
    }
    form label {
        display: block;
        margin: 10px 0 5px;
    }
    form input, form button {
        width: 100%;
        box-sizing: border-box;
        margin-bottom: 10px;
    }
    #form-buttons {
        display: flex;
        justify-content: flex-end; /* Align buttons to the bottom right */
        gap: 10px;
    }
    #form-buttons button {
        width: auto;
    }
</style>


<?php
include '../includes/_foot.php'; // Ensure correct path

?>
