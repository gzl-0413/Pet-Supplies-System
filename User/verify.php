<?php
include '../includes/_base.php';

if (is_get()) {
    $token = req('token');

    if ($token) {
        $stm = $_db->prepare('
            SELECT email
            FROM member
            WHERE accountactivationtoken = ?
            AND status = "Unverified"
        ');
        $stm->execute([$token]);
        $member = $stm->fetch(PDO::FETCH_OBJ);

        if ($member) {
            $stm = $_db->prepare('
            UPDATE member 
            SET status = "Verified",
                accountactivationtoken = NULL
            WHERE email = ?');
            $stm->execute([$member->email]);

            session_start();
            $_SESSION['email_verified'] = true;
            $_SESSION['email'] = $member->email;

            header('Location: emailAuthenticate.php?status=success');
            exit();
        } else {
            header('Location: emailAuthenticate.php?status=failure');
            exit();
        }
    } else {
        header('Location: emailAuthenticate.php?status=failure');
        exit();
    }
} else {
    echo 'Invalid request method.';
}