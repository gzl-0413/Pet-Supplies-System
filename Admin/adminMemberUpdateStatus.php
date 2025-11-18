<?php
require_once '../includes/_base.php';
auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = $_POST['member_id'];
    $newStatus = $_POST['status'];

    try {
        $updateQuery = "UPDATE member SET status = ? WHERE member_id = ?";
        $stmt = $_db->prepare($updateQuery);
        $stmt->execute([$newStatus, $memberId]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>

