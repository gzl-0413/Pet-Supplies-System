<?php
include '../includes/_base.php';

if(!isset($_SESSION['reset_otp']) == true){
    temp('error', 'Unauthorized access. Please start the password reset process again.');
    redirect('forgotPassword.php');
}

$email = $_SESSION['reset_otp']['email'];
$alertMessage = '';

if (is_post()) {
    if (isset($_POST['resend'])) {
        $otp = sprintf("%06d", mt_rand(1, 999999));
        $_SESSION['reset_otp'] = [
            'code' => $otp,
            'email' => $email,
            'expires' => time() + 300 
        ];

        $m = get_mail();
        $m->addAddress($email);
        $m->isHTML(true);
        $m->Subject = 'New OTP for Password Reset';
        $m->Body = "
            <p>Your new OTP for password reset is: <strong>$otp</strong></p>
            <p>This OTP will expire in 5 minutes.</p>
        ";

        if ($m->send()) {
            $alertMessage = "New OTP has been sent to your email.";
        } else {
            $_err['resend'] = "Failed to send new OTP. Please try again.";
        }
    } else {
        $userOTP = '';
        for ($i = 1; $i <= 6; $i++) {
            $userOTP .= req("digit$i");
        }

        if ($userOTP === $_SESSION['reset_otp']['code']) {
            if (time() < $_SESSION['reset_otp']['expires']) {
                $_SESSION['reset_email'] = $_SESSION['reset_otp']['email'];
                $_SESSION['allow_password_reset'] = true;
                redirect('newPassword.php');
            } else {
                $_err['otp'] = 'OTP has expired. Please request a new one.';
            }
        } else {
            $_err['otp'] = 'Invalid OTP. Please try again.';
        }
    }
}
$_title = 'OTP Verification';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?></title>
    <link rel="stylesheet" href="../CSS/verifyOTP.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
    <div class="container">
        <div class="otp">
            <span class="btl">
                <a href="login.php">Back To Login <i class="fa fa-arrow-circle-left" aria-hidden="true"></i></a>
            </span>
            <form method="post" action="verifyOTP.php">
                <h1><?= $_title ?></h1>
                <p>Code has been sent to your email.</p>
                <?= err('otp') ?>
                <?= err('resend') ?>

                <div class="number">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <input type="text" maxlength="1" name="digit<?= $i ?>" id="digit<?= $i ?>" <?= $i === 1 ? 'autofocus' : '' ?>>
                    <?php endfor; ?>
                </div>
                <p class="resend">
                    <button type="submit" name="resend" value="1">Resend OTP <i class="fa fa-undo" aria-hidden="true"></i></button>
                </p>
                <button type="submit" class="otpVerifyBtn" disabled>Verify</button>
            </form>
        </div>
    </div>

    
    <?php if ($alertMessage): ?>
        <script>
            alert("<?= htmlspecialchars($alertMessage) ?>");
        </script>
    <?php endif; ?>
    <script src="../JS/initializer.js">
        $(document).ready(function() {
            if (document.querySelector('.number')) {
                initializeOTPInputs();
            }
        });
    </script>
</body>

</html>