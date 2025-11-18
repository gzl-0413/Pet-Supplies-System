<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="../CSS/register.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="../JS/app.js"></script>

    <?php
    include '../includes/_base.php';

    if (isset($_SESSION['member']) && $_SESSION['member'] !== null) {
        echo "<script>alert('You are already logged in!');
        window.location.href = 'index.php';
        </script>";
        exit();
    }

    if (isset($_SESSION['admin']) && $_SESSION['admin'] !== null) {
        echo "<script>alert('You are already logged in!');
        window.location.href = '../Admin/adminDashboard.php';
        </script>";
        exit();
    }


    if (isset($_POST['validate']) && $_POST['validate'] === 'true') {
        header('Content-Type: application/json');

        $field = isset($_POST['username']) ? 'username' : (isset($_POST['email']) ? 'email' : null);
        $value = $_POST[$field] ?? null;

        if (!$field || !$value) {
            echo json_encode(['error' => 'Invalid request']);
            exit;
        }

        $result = ['status' => 'available'];

        try {
            $stmt = $_db->prepare("SELECT COUNT(*) FROM member WHERE $field = ?");
            $stmt->execute([$value]);
            if ($stmt->fetchColumn() > 0) {
                $result['status'] = 'duplicate';
            }
            echo json_encode($result);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(['error' => 'Database error occurred']);
        }
        exit;
    }

    function validateField($field, $value, $additionalParam = null)
    {
        global $_err;

        switch ($field) {
            case 'username':
            case 'email':
                if (empty($value)) {
                    $_err[$field] = ucfirst($field) . " is required.";
                }
                break;
            case 'password':
                if (strlen($value) < 8) {
                    $_err[$field] = "Password must be at least 8 characters long.";
                }
                break;
            case 'repeat':
                if ($value !== $additionalParam) {
                    $_err[$field] = "Passwords do not match.";
                }
                break;
            case 'photo':
                if (isset($_FILES['photo'])) {
                    if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                        $_err[$field] = "Error uploading photo.";
                    } elseif ($_FILES['photo']['size'] === 0) {
                        $_err[$field] = "Photo is required.";
                    } else {
                        $validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                        if (!in_array($_FILES['photo']['type'], $validTypes)) {
                            $_err[$field] = "Invalid file type. Please upload an image (JPG, PNG, or GIF).";
                        }
                        $maxSize = 1 * 1024 * 1024;
                        if ($_FILES['photo']['size'] > $maxSize) {
                            $_err[$field] = "File is too large. Please upload an image less than 1MB.";
                        }
                    }
                } else {
                    $_err[$field] = "Photo is required.";
                }
                break;
            case 'captcha':
                $secretKey = '6LdaT04qAAAAAF3iHJS202HUWb6tI4agZjUH5igi';
                $verifyURL = 'https://www.google.com/recaptcha/api/siteverify';

                $response = file_get_contents($verifyURL . '?secret=' . $secretKey . '&response=' . $value);
                $responseData = json_decode($response);

                if (!$responseData->success) {
                    $_err['captcha'] = 'reCAPTCHA verification failed. Please try again.';
                }
        }
    }

    if (is_post()) {
        $username = req('username');
        $dob = req('birthdate');
        $contact = req('contactnumber');
        $email = req('email');
        $password = req('password');
        $repeat = req('repeat');
        $f = get_file('photo');
        $recaptcha = req('g-recaptcha-response');

        validateField('username', $username);
        validateField('birthdate', $dob);
        validateField('contactnumber', $contact);
        validateField('email', $email);
        validateField('password', $password);
        validateField('repeat', $repeat, $password);
        validateField('photo', $f);
        validateField('captcha', $recaptcha);

        foreach ($_POST as $key => $value) {
            $GLOBALS[$key] = $value;
        }

        if (!$_err) {
            $photo = save_photo($f, '../uploads');

            $stm = $_db->prepare('SELECT MAX(CAST(SUBSTRING(member_id, 2) AS UNSIGNED)) as max_id FROM member');
            $stm->execute();
            $row = $stm->fetch(PDO::FETCH_ASSOC);

            $last_id = $row ? (int)$row['max_id'] : 0;
            $next_id = $last_id + 1;
            $member_id = 'M' . str_pad($next_id, 4, '0', STR_PAD_LEFT);

            $activation_token = sha1(uniqid() . rand());

            $stm = $_db->prepare(
                'INSERT INTO member (member_id, username, birthdate, contactnumber, email, password, profilepic, status, accountactivationtoken, tokenexpiresat)
            VALUES (?, ?, ?, ?, ?, SHA1(?), ?, "Unverified", ?, ADDTIME(NOW(), "00:05"))'
            );
            $stm->execute([$member_id, $username, $dob, $contact, $email, $password, $photo, $activation_token]);

            updateCartWithMemberId($member_id);

            temp('info', 'Record inserted');
            verification_email($email, $activation_token);
            redirect('emailAuthenticate.php?member_id=' . $member_id . '&status=success');
        }
    }
    ?>

</head>

<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>Register</h1>
            </div>
            <form id="registerForm" class="register-form" action="register.php" method="post" enctype="multipart/form-data">
                <div class="form-item">
                    <span class="usericon"><i class="fa fa-user" aria-hidden="true"></i></span>
                    <?= html_text('username', 'maxlength="100" placeholder="Username" id="username"') ?>
                    <span id="usernameFeedback" class="feedback"><?= err('username') ?></span>
                </div>

                <div class="form-item">
                    <span class="birthdateicon"><i class="fa fa-birthday-cake" aria-hidden="true"></i></span>
                    <?= html_date('birthdate', 'placeholder="Birthdate", id="birthdate"') ?>
                    <span id="birthdateFeedback" class="feedback"><?= err('birthdate') ?></span>
                </div>

                <div class="form-item">
                    <span class="contacticon"><i class="fa fa-phone" aria-hidden="true"></i></span>
                    <?= html_text('contactnumber', 'maxlength="100" placeholder="Contact Number", id="contactnumber"') ?>
                    <span id="contactnumberFeedback" class="feedback"><?= err('contactnumber') ?></span>
                </div>

                <div class="form-item">
                    <span class="emailicon"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>
                    <?= html_text('email', 'maxlength="100", placeholder="Email", id="email"') ?>
                    <span id="emailFeedback" class="feedback"><?= err('email') ?></span>
                </div>


                <div class="form-item">
                    <span class="passwordicon"><i class="fa fa-unlock-alt" aria-hidden="true"></i></span>
                    <?= html_password('password', 'maxlength="100", placeholder="Password"') ?>
                    <span class="toggle-password" data-target="password"><i class="fa fa-eye" aria-hidden="true"></i></span>
                    <span id="passwordFeedback" class="feedback"><?= err('password') ?></span>
                </div>


                <div class="form-item">
                    <span class="passwordicon"><i class="fa fa-unlock-alt" aria-hidden="true"></i></span>
                    <?= html_password('repeat', 'maxlength="100", placeholder="Repeat Password"') ?>
                    <span class="toggle-password" data-target="repeat" style="top: 20%;"><i class="fa fa-eye" aria-hidden="true"></i></span>
                    <span id="repeatFeedback" class="feedback"><?= err('repeat') ?></span>

                </div>

                <div class="form-item">
                    <label for="photo">Photo</label>
                    <label class="upload">
                        <?= html_file('photo', 'image/*', 'hidden', 'id="photo"') ?>
                        <img src="/Images/photo.jpg" id="photoPreview" alt="Photo Preview">
                    </label>
                    <button type="button" id="captureButton" style="margin-top: 2%;">Capture from Webcam</button>
                    <div id="webcamContainer" style="display: none;">
                        <video id="webcam" autoplay></video>
                        <button type="button" id="takePhoto">Take Photo</button>
                        <canvas id="photoCanvas" style="display: none;"></canvas>
                    </div>
                    <span id="photoFeedback" class="feedback"><?= err('photo') ?></span>
                </div>

                <div class="form-other">
                    <div class="checkbox">
                        <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
                        <label for="agreeTerms">I agree to the <a href="#">terms and conditions</a>.</label>
                    </div>
                </div>

                <div class="g-recaptcha" data-sitekey="6LdaT04qAAAAAHSIocWGPfx69T4vNOzMf4pz3vlZ"></div>
                <span id="captchaFeedback" class="feedback"><?= err('captcha') ?></span>

                <button type="submit">Register</button>
                <div class="login">
                    Already have an account? <a href="login.php">Login here</a>.
                </div>
        </div>
    </div>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="../JS/register.js"></script>
    <script src="../JS/webcam.js"></script>
    <script src="../JS/initializer.js">
        $(document).ready(function() {
            if ($('.toggle-password').length) {
                initializePasswordToggle();
            }
        });
    </script>

</body>

</html>