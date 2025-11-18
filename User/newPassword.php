<?php
include '../includes/_base.php';

if (!isset($_SESSION['allow_password_reset']) || $_SESSION['allow_password_reset'] !== true) {
    temp('error', 'Unauthorized access. Please start the password reset process again.');
    redirect('forgotPassword.php');
}

$email = $_SESSION['reset_email'];

if (is_post()) {
    $password = req('password');
    $confirm  = req('repeat');

    if ($password == '') {
        $_err['password'] = 'Required';
    } else if (strlen($password) < 5 || strlen($password) > 100) {
        $_err['password'] = 'Between 5-100 characters';
    }

    if ($confirm == '') {
        $_err['repeat'] = 'Required';
    } else if (strlen($confirm) < 5 || strlen($confirm) > 100) {
        $_err['repeat'] = 'Between 5-100 characters';
    } else if ($confirm != $password) {
        $_err['repeat'] = 'Not matched';
    }

    if (!$_err) {
        $password = trim($password);

        $stm = $_db->prepare(
            '
            UPDATE member
            SET password = SHA1(?)
            WHERE email = ?
            '
        );
        $stm->execute([$password, $email]);

        unset($_SESSION['allow_password_reset']);
        unset($_SESSION['reset_otp']);

        $_SESSION['success'] = 'Password updated successfully.';
        redirect('login.php');
    }
}

$_title = 'Reset Password';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?></title>
    <link rel="stylesheet" href="../CSS/newPassword.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
    <div class="container">
        <div class="password-reset-form">
            <h1>Reset Your Password</h1>
            <p>Please enter your new password below.</p>

            <form method="post" class="form" id="newPasswordForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" value="<?= htmlspecialchars($_SESSION['reset_email']) ?>" readonly class="form-control readonly-email">
                </div>

                <div class="form-group">
                    <label for="password">New Password</label>
                    <?= html_password('password', 'class="form-control" maxlength="100"') ?>
                    <span class="toggle-password" data-target="password"><i class="fa fa-eye" aria-hidden="true"></i></span>
                    <span id="passwordFeedback" class="feedback"><?= err('password') ?></span>
                </div>

                <div class="form-group">
                    <label for="confirm">Confirm New Password</label>
                    <?= html_password('repeat', 'class="form-control" maxlength="100"') ?>
                    <span class="toggle-password" data-target="repeat"><i class="fa fa-eye" aria-hidden="true"></i></span>
                    <span id="repeatFeedback" class="feedback"><?= err('repeat') ?></span>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                    <button type="reset" class="btn btn-secondary">Clear</button>
                </div>
            </form>
        </div>
    </div>
    <script src="../JS/register.js"></script>
    <script src="../JS/initializer.js">
        $(document).ready(function() {
            if ($('.toggle-password').length) {
                initializePasswordToggle();
            }
        });
    </script>
</body>

</html>