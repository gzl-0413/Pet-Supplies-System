<?php
require_once '../includes/_base.php';
auth();
if (empty($_SESSION['member'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = $_POST['rating'];
    $review = htmlspecialchars($_POST['review']);
    $product_id = $_POST['product_id'] ?? null;
    $order_id = $_POST['order_id'] ?? null;

    $member_id = $_SESSION['member'];
    $photos = '';

  
    if (isset($_FILES['photos']) && count($_FILES['photos']['name']) > 0) {
        $fileNames = [];
        foreach ($_FILES['photos']['name'] as $key => $name) {
            if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $uniqueName = uniqid() . '.' . $extension;

                $uploadPath = '../uploads/' . $uniqueName;
                if (move_uploaded_file($_FILES['photos']['tmp_name'][$key], $uploadPath)) {
                    $fileNames[] = $uniqueName;
                }
            }
        }
        $photos = implode(',', $fileNames);
    }

    if ($product_id && $order_id) {
        $sql = "SELECT rating_id FROM review ORDER BY rating_id DESC LIMIT 1";
        $stmt = $_db->prepare($sql);
        $stmt->execute();
        $lastRatingId = $stmt->fetchColumn();

        $newRatingId = 'R' . str_pad((intval(substr($lastRatingId, 1)) + 1), 4, '0', STR_PAD_LEFT);

        $sql = "INSERT INTO review (rating_id, comment, rate, reply, date_time, product_id, member_id, order_id, photos) 
                VALUES (:rating_id, :comment, :rate, :reply, :date_time, :product_id, :member_id, :order_id, :photos)";

        $stmt = $_db->prepare($sql);

        $date_time = date('Y-m-d H:i:s');
        $reply = '';

        $stmt->bindParam(':rating_id', $newRatingId);
        $stmt->bindParam(':comment', $review);
        $stmt->bindParam(':rate', $rating);
        $stmt->bindParam(':reply', $reply);
        $stmt->bindParam(':date_time', $date_time);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':member_id', $member_id);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->bindParam(':photos', $photos);

        if ($stmt->execute()) {
            header("Location: productPage.php?productId=" . urlencode($product_id));
            exit();
        } else {
            echo "<p>There was an error submitting your review. Please try again later.</p>";
        }
    } else {
        echo "<p>Product ID and Order ID are required to submit the review.</p>";
    }
} else {
    echo "<p>Invalid request method.</p>";
}
?>
