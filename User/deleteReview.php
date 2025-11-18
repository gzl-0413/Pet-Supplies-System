<?php
require_once '../includes/_base.php';

if (empty($_SESSION['member'])) {
    header("Location: login.php");
    exit();
}


if (isset($_GET['id'])) {
    $rating_id = $_GET['id'];

    $sql = "DELETE FROM review WHERE rating_id = :rating_id";
    $stmt = $_db->prepare($sql);
    $stmt->bindParam(':rating_id', $rating_id, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "<script>alert('Review deleted successfully.');</script>";
    } else {
        echo "<script>alert('Error deleting review. Please try again.');</script>";
    }
} else {
    echo "<script>alert('Invalid request. No review ID provided.');</script>";
}

echo "<script>window.location.href = 'viewReview.php';</script>";
?>
