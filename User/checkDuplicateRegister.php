<?php
require '../includes/_base.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $field = $_POST['field'] ?? '';
    $value = $_POST['value'] ?? '';

    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

    $column = "";

    if ($field == "username") {
        $column = "username";
    } elseif ($field == "email") {
        $column = "email";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid field type']);
        exit;
    }

    $stm = $_db->prepare("
        SELECT COUNT(*) as count 
        FROM member 
        WHERE $column = ?
    ");
    $stm->execute([$value]);
    $result = $stm->fetch(PDO::FETCH_OBJ);

    if ($result) {
        if ($result->count > 0) {
            echo json_encode(['status' => 'duplicate']);
        } else {
            echo json_encode(['status' => 'available']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Query failed']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}