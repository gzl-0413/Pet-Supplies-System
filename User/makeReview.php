<?php

require_once '../includes/_base.php';
auth();
if (empty($_SESSION['member'])) {
    header("Location: login.php");
    exit();
}

$product_id = $_GET['product_id'] ?? null;
$order_id = $_GET['order_id'] ?? null;

if ($product_id) {
    $sql = "SELECT name, photo FROM product WHERE id = :product_id";
    $stmt = $_db->prepare($sql);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_STR);
    $stmt->execute();

    $product = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$product) {
        echo "<p>Product not found.</p>";
        exit;
    }
} else {
    echo "<p>No product ID provided.</p>";
    exit;
}
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Review</title>
    <link href="../CSS/makeReview.css" rel="stylesheet" type="text/css"/>
    <script src="../JS/makeReview.js" defer></script>
</head>
<body>

    <div class="container">
        <div class="review-form">
            <div class="product-info">
                <img src="../uploads/<?php echo htmlspecialchars($product->photo); ?>" alt="Product Image">
                <h2><?php echo htmlspecialchars($product->name); ?></h2>
            </div>
            <h1>Leave a Review</h1>

            <form action="submitReview.php" method="post" enctype="multipart/form-data" onsubmit="return confirmSubmit();">
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">

                <label for="">Star Rate:</label><br>
                <div class="star-rating">
                    <input type="radio" id="star5" name="rating" value="5"><label for="star5">&#9733;</label>
                    <input type="radio" id="star4" name="rating" value="4"><label for="star4">&#9733;</label>
                    <input type="radio" id="star3" name="rating" value="3"><label for="star3">&#9733;</label>
                    <input type="radio" id="star2" name="rating" value="2"><label for="star2">&#9733;</label>
                    <input type="radio" id="star1" name="rating" value="1"><label for="star1">&#9733;</label>
                </div>
                <br><br>
                <label for="review">Your Review:</label><br>
                <textarea id="review" name="review" rows="4" placeholder="Write your review here..."></textarea><br><br>

                <div class="file-upload">
                    <label for="photos">Upload Photos (Max 3):</label><br>
                    <input type="file" name="photos[]" accept="image/*" multiple id="photoInput">
                    <div class="image-preview" id="imagePreview"></div>
                </div>
                
                <div class="button-container">
                    <input type="submit" value="Submit Review">
                    <button type="button" class="cancelbutton" onclick="if(confirmCancel()) { window.history.back(); }">Cancel</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>

<?php
include 'footer.php';
?>
