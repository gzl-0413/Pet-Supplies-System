<?php
include '../includes/_base.php';
auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_err = []; // Initialize error array

    $stm = $_db->prepare('SELECT * FROM member WHERE member_id = ?');
    $stm->execute([$_SESSION['member']]);
    $u = $stm->fetch(PDO::FETCH_OBJ);

    $username = req('username');
    $contact = req('contactnumber');
    $old_photo = $u->profilepic;
    $f = get_file('profilepic');

    // Validate: username
    if (!$username) {
        $_err['username'] = 'Required';
    } else if (strlen($username) > 100) {
        $_err['username'] = 'Maximum 100 characters';
    }

    // Validate: contact
    if (!$contact) {
        $_err['contactnumber'] = 'Required';
    } else if (!is_numeric($contact)) {
        $_err['contactnumber'] = 'Invalid contact number, must be numeric';
    } else if (strlen($contact) < 8 || strlen($contact) > 15) {
        $_err['contactnumber'] = 'Contact number must be between 8 and 15 digits';
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
            UPDATE member
            SET username = ?, contactnumber = ?, profilepic = ?
            WHERE member_id = ?
        ');
        $result = $stm->execute([$username, $contact, $photo, $_SESSION['member']]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            $_err['database'] = 'Failed to update profile';
            echo json_encode(['success' => false, 'message' => 'Failed to update profile', 'errors' => $_err]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $_err]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
