<!DOCTYPE html>
<html lang="en">

<?php
include '../includes/_base.php';

$status = isset($_GET['status']) ? $_GET['status'] : null;

if ($status === null) {
    header('Location: login.php');
    exit();
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../CSS/emailAuthenticate.css">
</head>

<body>

    <div class="container" id="message-container">

        <?php if ($status === 'success') : ?>
            <div class="email-icon">📧</div>
            <h1>Email Verified Successfully</h1>
            <p>Your email has been successfully verified. You will be redirected to the homepage shortly.</p>
        <?php elseif ($status === 'failure') : ?>
            <div class="email-icon">📧</div>
            <h1>Email Verification Failed</h1>
            <p>Sorry, there was an error verifying your email. Please try again later.</p>
        <?php else: ?>
            <h1>Check Your Email</h1>
            <p>A verification link has been sent to your email. Please check your email to activate your account.</p>
        <?php endif; ?>
    </div>

    <script>
        function redirectToLogin() {
            window.location.href = 'login.php';
        }

        function checkVerificationStatus() {
            fetch('checkVerification.php')
            .then(response => response.json())
            .then(data => {
                if (data.verified) {
                    document.getElementById('message-container').innerHTML = `
                        <div class="success-icon">✅</div>
                        <h1>Email Verified Successfully</h1>
                        <p>Your email has been successfully verified. You will be redirected to the homepage shortly.</p>
                    `;
                    setTimeout(redirectToLogin, 3000);
                }
            });
        }

        window.onload = function() {
            const status = '<?php echo $status; ?>';
            if (status === 'success') {
                setTimeout(redirectToLogin, 3000);
            } else if (status === 'failure') {
                setTimeout(redirectToLogin, 3000);
            } else {
                setInterval(checkVerificationStatus, 5000);
            }
        };
    </script>

</body>

</html>