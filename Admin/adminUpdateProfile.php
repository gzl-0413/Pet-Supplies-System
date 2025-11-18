<?php
include '../includes/_base.php';
auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_err = []; // Initialize error array

    $stm = $_db->prepare('SELECT * FROM admin WHERE admin_id = ?');
    $stm->execute([$_SESSION['admin']]);
    $a = $stm->fetch(PDO::FETCH_OBJ);

    $name = req('adminname');
    $old_photo = $a->adminprofilepic;
    $f = get_file('profilepic');

    // Validate: name
    if (!$name) {
        $_err['name'] = 'Required';
    } else if (strlen($name) > 100) {
        $_err['name'] = 'Maximum 100 characters';
    }

    // Validate: photo (optional)
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['photo'] = 'Must be image';
        } else if ($f->size > 1 * 1024 * 1024) {
            $_err['photo'] = 'Maximum 1MB';
        }
    }

    if (!$_err) {
        $photo = $old_photo;

        if ($f) {
            if ($old_photo && file_exists("../uploads/$old_photo")) {
                unlink("../uploads/$old_photo");
            }
            $photo = save_photo($f, '../uploads');
        }
        $stm = $_db->prepare('
            UPDATE admin
            SET adminname = ?, adminprofilepic = ?
            WHERE admin_id = ?
        ');
        $result = $stm->execute([$name, $photo, $_SESSION['admin']]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully', 'newPhoto' => $photo]);
        } else {
            $_err['database'] = 'Failed to update profile';
            echo json_encode(['success' => false, 'message' => 'Failed to update profile', 'errors' => $_err]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $_err]);
    }
} else {
    echo "<script>
    alert('You are not allow to access this page with this way.');
    window.location.href = 'adminDashboard.php';
</script>";
exit;
}