<?php
require_once '../includes/_base.php';
auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewId = filter_input(INPUT_POST, 'reviewId', FILTER_SANITIZE_STRING);
    $replyMessage = filter_input(INPUT_POST, 'replyMessage', FILTER_SANITIZE_STRING);

    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    if ($reviewId && $replyMessage) {
        try {
            // Check if the current reply message is different
            $checkQuery = 'SELECT reply FROM review WHERE rating_id = :reviewId';
            $checkStm = $_db->prepare($checkQuery);
            $checkStm->bindValue(':reviewId', $reviewId, PDO::PARAM_STR);
            $checkStm->execute();
            $existingReply = $checkStm->fetchColumn();
    
            if ($existingReply === $replyMessage) {
                echo '<script type="text/javascript">';
                echo 'alert("Reply is already set to this value. No changes made.");';
                echo 'window.location.href = "adminReview.php";';
                echo '</script>';
                exit();
            } else {
                // Proceed with the update
                $query = 'UPDATE review SET reply = :reply WHERE rating_id = :reviewId';
                $stm = $_db->prepare($query);
                
                $stm->bindValue(':reply', $replyMessage, PDO::PARAM_STR);
                $stm->bindValue(':reviewId', $reviewId, PDO::PARAM_STR);
    
                $result = $stm->execute();
    
                echo "Result of execution: " . ($result ? 'Success' : 'Failed');
                
                if ($stm->rowCount() > 0) {
                    echo "<script>
                        window.location.href = 'adminReview.php';
                        </script>";
                    exit;
                } else {
                    echo 'No review found with the specified ID or no changes made.';
                }
            }
        } catch (PDOException $e) {
            echo 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
    
} else {
    echo "<script>
    alert('You are not allow to access this page with this way.');
    window.location.href = 'adminDashboard.php';
</script>";
exit;
}
?>
