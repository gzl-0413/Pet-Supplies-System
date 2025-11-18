<?php
require_once '../includes/_base.php';
auth();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field = isset($_POST['field']) ? $_POST['field'] : null;
    $value = isset($_POST['value']) ? $_POST['value'] : null;

    if ($field && $value) {
        if ($field === 'username') {
            $query = "SELECT * FROM member WHERE username = :value";
        } elseif ($field === 'email') {
            $query = "SELECT * FROM member WHERE email = :value";
        } elseif ($field === 'contact number') {
            $query = "SELECT * FROM member WHERE contactnumber = :value";
        }  elseif ($field === 'admin name') {
            $query = "SELECT * FROM admin WHERE adminname = :value";
        }  elseif ($field === 'admin email') {
            $query = "SELECT * FROM admin WHERE adminemail = :value";
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid field provided.']);
            exit;
        }

        try {
            $stmt = $_db->prepare($query);
            $stmt->execute([':value' => $value]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'duplicate']);
            } else {
                echo json_encode(['status' => 'available']);
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Database query error.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing field or value.']);
    }
} else {
    echo "<script>
    alert('You are not allow to access this page with this way.');
    window.location.href = 'adminDashboard.php';
</script>";
exit;
}
?>