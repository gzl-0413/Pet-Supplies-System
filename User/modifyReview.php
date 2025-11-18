<?php
require_once '../includes/_base.php';
auth();

if (empty($_SESSION['member'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['member'];

$rating_id = $_GET['id'] ?? null;
if ($rating_id) {
    $sql = "SELECT * FROM review WHERE rating_id = ? AND member_id = ?";
    $stmt = $_db->prepare($sql);
    $stmt->execute([$rating_id, $member_id]);  // Use an array to bind the values

    $review = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$review) {
        echo "<script>
                alert('Review not found or you do not have permission to access this review.');
                window.location.href = 'viewReview.php';
              </script>";
        exit();
    }

    $product_id = $review->product_id;
    $product_sql = "SELECT name, photo FROM product WHERE id = ?";
    $product_stmt = $_db->prepare($product_sql);
    $product_stmt->execute([$product_id]);  // Bind the value using an array

    $product = $product_stmt->fetch(PDO::FETCH_OBJ);

    if (!$product) {
        echo "<p>Product not found.</p>";
        exit;
    }
} else {
    echo "<p>No rating ID provided.</p>";
    exit;
}


include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Review</title>
    <link href="../CSS/modifyReview.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<div class="container">
    <h1>Modify Review</h1>
    <form id="modifyReviewForm" action="viewReview.php" method="POST" enctype="multipart/form-data" onsubmit="return confirmUpdate()">
        <input type="hidden" name="rating_id" value="<?php echo htmlspecialchars($review->rating_id); ?>">

        <label for="product_picture">Product Picture:</label>
        <img src="../uploads/<?php echo htmlspecialchars($product->photo); ?>" style="margin-bottom:10px;" alt="<?php echo htmlspecialchars($product->name); ?>" width="200">

        <label for="product_name" style="margin-bottom:5px;">Product Name:</label>
        <p><?php echo htmlspecialchars($product->name); ?></p>
        
        <label for="rate" style="margin-bottom: -5px">Rating:</label>
        <div class="stars" id="star-rating" data-selected="<?php echo htmlspecialchars($review->rate); ?>">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="star <?php if ($i <= $review->rate) echo 'filled'; ?>" data-value="<?php echo $i; ?>">★</span>
            <?php endfor; ?>
        </div>
        <input type="hidden" name="rate" id="hidden-rate" value="<?php echo htmlspecialchars($review->rate); ?>" required>

        <label for="comment">Comment:</label>
        <textarea name="comment" id="comment"><?php echo htmlspecialchars($review->comment); ?></textarea>

        <label>Existing Review Photo(s):</label>
        <div class="existing-photos">
            <?php
            $existing_photos = !empty($review->photos) ? explode(',', $review->photos) : [];

            if (!empty($existing_photos)) {
                foreach ($existing_photos as $photo): ?>
                    <div class="photo-preview">
                        <img src="../uploads/<?php echo htmlspecialchars(trim($photo)); ?>" alt="Review Photo" style="max-width: 150px; margin: 5px;">
                    </div>
                <?php endforeach;
            } else {
                echo "<p>No existing photos.</p>";
            }
            ?>
        </div>

        <label for="photos">New Review Photo(s):</label>
        <input type="file" name="photos[]" id="photos" multiple accept="image/*" onchange="previewImages(event)">
        <div class="new-photos-preview"></div>

        <div class="form-buttons">
            <button type="submit" class="submit-btn">Update Review</button>
            <button type="button" class="cancel-btn" onclick="confirmCancel()">Cancel</button>
        </div>
    </form>
</div>

<script src="../JS/modifyReview.js"></script>
</body>
</html>
