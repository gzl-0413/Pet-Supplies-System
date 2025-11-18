<?php
include '../includes/_base.php';
auth();

if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Admin not logged in']);
    exit;
}

$admin_id = $_SESSION['admin'];

$_err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = req('old');
    $new = req('new');
    $confirm = req('confirm');

    $stm = $_db->prepare('SELECT adminpassword FROM admin WHERE admin_id = ?');
    $stm->execute([$admin_id]);
    $current = $stm->fetchColumn();

    if (sha1($old) !== $current) {
        $_err['old'] = 'Incorrect old password';
    }

    if (strlen($new) < 8) {
        $_err['new'] = 'New password must be at least 8 characters long';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $new)) {
        $_err['new'] = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&)';
    }

    if ($new !== $confirm) {
        $_err['confirm'] = 'Passwords do not match';
    }

    if (empty($_err)) {
        $password = sha1($new);
        $update_stm = $_db->prepare('UPDATE admin SET adminpassword = ? WHERE admin_id = ?');
        $result = $update_stm->execute([$password, $admin_id]);

        if ($result) {
            $response = ['success' => true, 'message' => 'Password updated successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to update password', 'errors' => ['database' => 'Failed to update password']];
        }
    } else {
        $response = ['success' => false, 'message' => 'Validation failed', 'errors' => $_err];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

?>

<script src="../JS/adminProfile.js"></script>
<link rel="stylesheet" href="../CSS/adminChangePassword.css">
<div class="change-password-container">
    <h2>Change Admin Password</h2>
    <form method="post" class="change-password-form">
        <div class="form-group">
            <label for="old">Old Password:</label>
            <div class="password-input-wrapper">
                <?= html_password('old', 'class="form-control" id="old" required') ?>
                <span class="toggle-password" data-target="old"><i class="fa fa-eye" aria-hidden="true"></i></span>
            </div>
        </div>
        <div class="form-group">
            <label for="new">New Password:</label>
            <div class="password-input-wrapper">
                <?= html_password('new', 'class="form-control" id="new" required') ?>
                <span class="toggle-password" data-target="new"><i class="fa fa-eye" aria-hidden="true"></i></span>
            </div>
        </div>
        <div class="form-group">
            <label for="confirm">Confirm New Password:</label>
            <div class="password-input-wrapper">
                <?= html_password('confirm', 'class="form-control" id="confirm" required') ?>
                <span class="toggle-password" data-target="confirm"><i class="fa fa-eye" aria-hidden="true"></i></span>
            </div>
        </div>
        <div class="password-requirements">
            <p>Password must be:</p>
            <ul>
                <li>At least 8 characters long</li>
                <li>At least one uppercase letter</li>
                <li>At least one lowercase letter</li>
                <li>At least one number</li>
                <li>At least one special character (@$!%*?&)</li>
            </ul>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Change Password</button>
            <button type="reset" class="btn btn-secondary">Reset</button>
        </div>
    </form>
</div>
<script src="../JS/adminProfile.js">
</script>