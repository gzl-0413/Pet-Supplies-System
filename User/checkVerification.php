<?php
include '../includes/_base.php';
auth();

$email = $_SESSION['email'] ?? '';
$verified = false;

if ($email) {
    $stm = $_db->prepare('
        SELECT status FROM member
        WHERE email = ?
    ');

    $stm->execute([$email]);
    $result = $stm->fetchColumn();

    if ($result !== false) {
        $verified = (bool)$result;
        if ($verified) {
            $_SESSION['email_verified'] = true;
        }
    }
}

header('Content-Type: application/json');
echo json_encode(['Verified' => $verified]);
