<?php
require '../includes/_base.php';
// ----------------------------------------------------------------------------

if (is_post()) {
    $id = req('id');

    $stm = $_db->prepare('DELETE FROM temp_cart WHERE cart_id = ?');
    $stm->execute([$id]);

    temp('info','Record deleted');
}
redirect('/');
?>