<?php
include '../includes/_base.php';

if (is_post()) {
    $email = req('email');

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    } else if (!is_exists($email, 'member', 'email')) {
        $_err['email'] = 'Not exists';
    }

    // Send OTP (if valid)
    if (!$_err) {
        // Select user
        $stm = $_db->prepare('SELECT * FROM member WHERE email = ?');
        $stm->execute([$email]);
        $u = $stm->fetch();

        $otp = sprintf("%06d", mt_rand(1, 999999));

        $_SESSION['reset_otp'] = [
            'code' => $otp,
            'email' => $email,
            'expires' => time() + 300
        ];

        // Send email with OTP
        $m = get_mail();
        $m->addAddress($u->email, $u->name);
        $m->isHTML(true);
        $m->Subject = 'Reset Password OTP';
        $m->Body = "
            <p>Dear $u->name,</p>
            <h1 style='color: red'>Reset Password</h1>
            <p>Your OTP for password reset is: <strong>$otp</strong></p>
            <p>This OTP will expire in 5 minutes.</p>
            <p>From, 😺 Admin</p>
        ";

        $m->send();

        redirect('verifyOTP.php');
    }
}

$_title = 'Forgot Password';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../CSS/forgotPassword.css">
</head>

<body>
    <div class="container">
        <div class="image-section">
            <!-- Add an image here -->
            <img src="../Images/lock.jpg" alt="Forgot Password" width="300">
        </div>
        <div class="form-section">
            <h1><?= $_title ?></h1>
            <p>Enter your email and we'll send you a link to reset your password.</p>
            <form method="post" class="form">
                <label for="email">Email</label>
                <?= html_text('email', 'maxlength="100"') ?>
                <?= err('email') ?>

                <section>
                    <button type="submit">Submit</button>
                    <button type="reset">Reset</button>
                </section>
            </form>
            <a href="login.php" class="back-link"><i class="fa fa-arrow-circle-left" aria-hidden="true"></i> Back to Login</a>
        </div>
    </div>
</body>

</html>