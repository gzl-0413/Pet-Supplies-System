<?php
require_once '../includes/_base.php';
auth();

if (isset($_GET['id'])) {
    $reviewId = $_GET['id'];

    try {
        $_db->beginTransaction();

        $deleteReviewsQuery = "DELETE FROM review WHERE rating_id = ?";
        $stmt = $_db->prepare($deleteReviewsQuery);
        $stmt->execute([$reviewId]);

        $_db->commit();

        echo "<script>
        window.location.href = 'adminReview.php';
            </script>";
        exit;
    } catch (PDOException $e) {
        $_db->rollBack();
        echo 'Database error: ' . $e->getMessage();
    }
} else {
    echo "<script>
    alert('You are not allow to access this page with this way.');
    window.location.href = 'adminDashboard.php';
</script>";
exit;
}
?>
