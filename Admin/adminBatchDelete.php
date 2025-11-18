<?php
require_once '../includes/_base.php';
auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['selected_reviews'])) {
    $selectedReviews = $_POST['selected_reviews'];

    try {
        $_db->beginTransaction();

        $placeholders = implode(',', array_fill(0, count($selectedReviews), '?'));
        $deleteQuery = "DELETE FROM review WHERE rating_id IN ($placeholders)";
        
        $stmt = $_db->prepare($deleteQuery);
        $stmt->execute($selectedReviews);

        $_db->commit();

        echo "<script>
        alert('Selected reviews deleted successfully.');
        window.location.href = 'adminReview.php';
        </script>";
        exit;
    } catch (PDOException $e) {
        $_db->rollBack();
        echo 'Database error: ' . $e->getMessage();
    }
} else {
    echo "<script>
    alert('No reviews selected for deletion.');
    window.location.href = 'adminReview.php';
    </script>";
    exit;
}
?>
