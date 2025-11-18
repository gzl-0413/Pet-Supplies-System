<?php
require_once '../includes/_base.php';
 
if (!isset($_SESSION['admin']) || $_SESSION['admin'] === null) {
    echo "<script>
        alert('You do not have permission to access this page.');
        window.location.href = '../User/index.php';
    </script>";
    exit;
}

 
if (is_post()) {
    $promoId = req('promoId');
    $newStatus = req('status');  // New status is passed from the checkbox change event

    try {
        $updateQuery = "UPDATE promotion SET status = ? WHERE promoId = ?";
        $stmt = $_db->prepare($updateQuery);
        $stmt->execute([$newStatus, $promoId]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo "<script>
    alert('You are not allow to access this page with this way.');
    window.location.href = 'adminDashboard.php';
</script>";
exit;
}
?>
