<?php
require_once '../includes/_base.php';
auth();

if (empty($_SESSION['member'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['member'];

// Fetch reviews with the updated query using the '?' placeholder
$sql = "SELECT r.*, p.name AS product_name, p.photo AS product_photo 
        FROM review r 
        JOIN product p ON r.product_id = p.id 
        WHERE r.member_id = ?"; 

$stmt = $_db->prepare($sql);
$stmt->execute([$member_id]);  
$reviews = $stmt->fetchAll(PDO::FETCH_OBJ);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating_id = $_POST['rating_id'];
    $rate = intval($_POST['rate']); // Convert to integer for security
    $comment = htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8'); // Sanitize input

    // Handle file uploads securely
    $uploaded_photos = [];
    if (!empty($_FILES['photos']['name'][0])) {
        foreach ($_FILES['photos']['tmp_name'] as $index => $tmp_name) {
            $file_type = mime_content_type($tmp_name);
            if (in_array($file_type, ['image/jpeg', 'image/png', 'image/gif'])) {
                $unique_name = uniqid() . '.' . pathinfo($_FILES['photos']['name'][$index], PATHINFO_EXTENSION);
                $destination = "../uploads/" . $unique_name;

                if (move_uploaded_file($tmp_name, $destination)) {
                    $uploaded_photos[] = $unique_name;
                } else {
                    echo "<p>Error uploading photo: " . htmlspecialchars($_FILES['photos']['name'][$index]) . "</p>";
                }
            } else {
                echo "<p>Invalid file type for photo: " . htmlspecialchars($_FILES['photos']['name'][$index]) . "</p>";
            }
        }
    }

    $photos_string = implode(',', $uploaded_photos);

    $sql = "UPDATE review SET rate = ?, comment = ?, photos = ? WHERE rating_id = ?";
    $stmt = $_db->prepare($sql);
    
    // Execute the statement using an array of values
    if ($stmt->execute([$rate, $comment, $photos_string, $rating_id])) {
        header("Location: viewReview.php");
        exit;
    } else {
        echo "<p>Error updating review.</p>";
    }
}
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reviews</title>
    <link href="../CSS/viewReview.css" rel="stylesheet" type="text/css"/>
    <script src="../JS/viewReview.js"></script>
</head>
<body>

<div class="container">
    <h1>My Reviews</h1>
    <div class="reviews">
        <?php if (count($reviews) > 0): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <div style="display: flex; align-items: center;">
                        <img src="../uploads/<?= htmlspecialchars($review->product_photo); ?>" alt="<?= htmlspecialchars($review->product_name); ?>">
                        <p><?= htmlspecialchars($review->product_name); ?></p>
                    </div>
                    <h3>Rating: <?= str_repeat('&#9733;', $review->rate) . str_repeat('&#9734;', 5 - $review->rate); ?></h3>
                    <p><?= nl2br(htmlspecialchars($review->comment)); ?></p>
             
                    <!-- Displaying photos if available -->
                    <?php if (!empty($review->photos)): ?>
                        <div class="review-photos">
                            <?php 
                            $photosArray = explode(',', $review->photos); 
                            foreach ($photosArray as $photo): ?>
                                <img src="../uploads/<?= htmlspecialchars($photo); ?>" alt="Review Photo" class="review-photo">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <p class="date"><em><?= htmlspecialchars($review->date_time); ?></em></p>
                    
                    <?php if (!empty($review->reply)): ?>
                        <hr style="border: 1px solid #ccc; margin: 10px 0;">
                        <p><strong>Reply:</strong> <?= nl2br(htmlspecialchars($review->reply)); ?></p>
                    <?php endif; ?>

                    <div class="review-buttons">
                        <button class="view" type="button" onclick="viewProduct('<?= $review->product_id; ?>')">View Product</button>
                        <button class="modify" type="button" onclick="modifyReview('<?= $review->rating_id; ?>')">Modify</button>
                        <button class="delete" type="button" onclick="deleteReview('<?= $review->rating_id; ?>')">Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-reviews-container">
                <div class="no-reviews">You have not made any reviews yet.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

<?php
include 'footer.php';
?>
