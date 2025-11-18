<?php
require_once '../includes/_base.php';



if (isset($_SESSION['admin']) && $_SESSION['admin'] !== null) {
    $admin_id = $_SESSION['admin'];
    $sql = "SELECT adminlevel 
            FROM admin 
            WHERE admin_id = :admin_id";
    $stmt = $_db->prepare($sql);
    $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $adminlevel = $result['adminlevel'];
    }   
}

if ($adminlevel !== null && $adminlevel === 'Low') {
    echo "<script>
        alert('You do not have permission to access this page.');
        window.location.href = '../Admin/adminDashboard.php';
    </script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminId = $_POST['admin_id'];
    $newStatus = $_POST['status'];

    try {
        $updateQuery = "UPDATE admin SET status = ? WHERE admin_id = ?";
        $stmt = $_db->prepare($updateQuery);
        $stmt->execute([$newStatus, $adminId]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>

