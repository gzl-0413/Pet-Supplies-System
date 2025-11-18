<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="../CSS/login.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>

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

    if (isset($_SESSION['password_reset_success'])) {
        $message = htmlspecialchars($_SESSION['password_reset_success']);
        echo "<script>alert('$message');</script>";
        unset($_SESSION['password_reset_success']);
    }

    $_err = [];
    $email = '';
    $password = '';
    $login_type = '';
    $admin_email = '';
    $admin_password = '';

    function handleFailedLogin($email)
    {
        global $_db;

        if (!isset($_SESSION['login_attempts'][$email])) {
            $_SESSION['login_attempts'][$email] = [
                'count' => 0,
                'time' => time(),
                'has_been_temp_blocked' => false
            ];
        } else if (!isset($_SESSION['login_attempts'][$email]['has_been_temp_blocked'])) {
            $_SESSION['login_attempts'][$email]['has_been_temp_blocked'] = false;
        }

        $loginAttempts = &$_SESSION['login_attempts'][$email];

        $stmt = $_db->prepare("SELECT status FROM member WHERE email = ?");
        $stmt->execute([$email]);
        $dbStatus = $stmt->fetchColumn();

        if ($dbStatus === 'Block') {
            return ["message" => "Your account has been permanently blocked. Please contact support.", "type" => "permanent"];
        }

        if ($dbStatus === 'TempBlock') {
            $remainingTime = 60 - (time() - $loginAttempts['time']);
            if ($remainingTime > 0) {
                return ["message" => "Your account is temporarily blocked. Please try again after {$remainingTime} seconds.", "type" => "temp", "remainingTime" => $remainingTime];
            } else {
                $stmt = $_db->prepare("UPDATE member SET status = 'Verified' WHERE email = ?");
                $stmt->execute([$email]);
                $loginAttempts['count'] = 0;
                $loginAttempts['time'] = time();
            }
        }

        $loginAttempts['count']++;

        $remainingAttempts = 3 - $loginAttempts['count'];

        if ($loginAttempts['count'] >= 3) {
            if ($loginAttempts['has_been_temp_blocked']) {
                $stmt = $_db->prepare("UPDATE member SET status = 'Block' WHERE email = ?");
                $stmt->execute([$email]);
                return ["message" => "Your account has been permanently blocked due to multiple failed login attempts. Please contact support.", "type" => "permanent"];
            } else {
                $stmt = $_db->prepare("UPDATE member SET status = 'TempBlock' WHERE email = ?");
                $stmt->execute([$email]);
                $loginAttempts['has_been_temp_blocked'] = true;
                $loginAttempts['time'] = time();
                return ["message" => "Too many failed attempts. Your account is temporarily blocked. Please try again after 60 seconds.", "type" => "temp", "remainingTime" => 60];
            }
        }

        return ["message" => "Invalid email or password. {$remainingAttempts} attempts left.", "type" => "attempt", "remainingAttempts" => $remainingAttempts];
    }

    if (is_post()) {
        $email = req('email');
        $password = req('password');
        $login_type = req('login_type');
        $admin_email = req('admin_email');
        $admin_password = req('admin_password');
        $rememberMe = req('rememberMe');


        if (isset($_COOKIE['remember_email']) && $_COOKIE['remember_email'] !== $email) {
            setcookie('remember_me', '', time() - 3600, "/");
            setcookie('remember_email', '', time() - 3600, "/");
            setcookie('remember_password', '', time() - 3600, "/");
        }

        if ($login_type == 'member') {
            if ($email == '') {
                $_err['email'] = 'Required';
            } else if (!is_email($email)) {
                $_err['email'] = 'Invalid email';
            }
            if ($password == '') {
                $_err['password'] = 'Required';
            }

            if (!$_err) {
                $stmt = $_db->prepare('
                    SELECT member_id, status FROM member
                    WHERE email = ?
                ');
                $stmt->execute([$email]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    if ($result['status'] == 'TempBlock') {
                        $loginAttempts = &$_SESSION['login_attempts'][$email];
                        $remainingTime = 60 - (time() - $loginAttempts['time']);
                        if ($remainingTime > 0) {
                            $_err['password'] = "Your account is temporarily blocked. Please try again after {$remainingTime} seconds.";
                        } else {
                            $stmt = $_db->prepare("UPDATE member SET status = 'Verified' WHERE email = ?");
                            $stmt->execute([$email]);
                            $loginAttempts['count'] = 0;
                            $loginAttempts['time'] = time();
                        }
                    } elseif($result['status'] == 'Unverified'){
                        $_err['password'] = 'Please verify your email before logging in.';
                    }

                    if (!isset($_err['password'])) {
                        $stm = $_db->prepare('
                            SELECT member_id, status FROM member
                            WHERE email = ? AND password = SHA1(?)
                        ');

                        $stm->execute([$email, $password]);
                        $result = $stm->fetch(PDO::FETCH_ASSOC);

                        if ($result) {
                            if ($result['status'] == 'Verified') {

                                if ($rememberMe) {
                                    setcookie('remember_me', session_id(), time() + (30 * 24 * 60 * 60), "/");
                                    setcookie('remember_email', $email, time() + (30 * 24 * 60 * 60), "/");
                                } else {
                                    setcookie('remember_me', '', time() - 3600, "/");
                                    setcookie('remember_email', '', time() - 3600, "/");
                                }

                                unset($_SESSION['login_attempts'][$email]);
                                temp('info', 'Login successful');
                                updateCartWithMemberId($result['member_id']);
                                loginMember($result['member_id']);
                                exit();
                            } elseif ($result['status'] == 'Unverified') {
                                $_err['password'] = 'Please verify your email.';
                            } elseif ($result['status'] == 'Block') {
                                $_err['password'] = 'This account has been blocked. Please contact support.';
                            }
                        } else {
                            $loginResult = handleFailedLogin($email);
                            $_err['password'] = $loginResult['message'];
                            if ($loginResult['type'] == 'temp') {
                                $_SESSION['block_timer'] = $loginResult['remainingTime'];
                            }
                        }
                    }
                } else {
                    $_err['email'] = 'Account not found.';
                }
            }
        } elseif ($login_type == 'admin') {
            $stm = $_db->prepare('
            SELECT admin_id, status FROM admin
            WHERE adminemail = ? AND adminpassword = SHA1(?)
        ');

            $stm->execute([$admin_email, $admin_password]);
            $result = $stm->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                if ($result['status'] == 'Block') {
                    $_err['admin_password'] = 'This account has been blocked.';
                } else {
                    temp('info', 'Login successfully');
                    loginAdmin($result['admin_id']);
                    exit();
                }
            } else {
                $_err['admin_password'] = 'Invalid email or password.';
            }
        } else {
            $_err['admin_password'] = 'Invalid email or password.';
        }
    }


    if (isset($_SESSION['alert'])) {
        $alertMessage = addslashes($_SESSION['alert']['message']);
        echo "<script>
            alert('$alertMessage');
        </script>";
        unset($_SESSION['alert']);
    }

    $emailCookie = isset($_COOKIE['remember_email']) ? htmlspecialchars($_COOKIE['remember_email']) : '';
    ?>

    <div class="login-container">
        
        <div class="login-card">
            <div class="login-header">
                <h1>Login</h1>
            </div>
            <div class="login-tabs">
                <button class="tab-button active" data-tab="member">Member</button>
                <button class="tab-button" data-tab="admin">Admin</button>
            </div>
            <form id="memberLoginForm" class="login-form" method="post">
                <div class="form-item">
                    <span class="email"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>
                    <?= html_text('email', 'maxlength="100"', $emailCookie) ?>
                </div>
                <?= err('email') ?>
                <div class="form-item">
                    <span class="password"><i class="fa fa-unlock-alt" aria-hidden="true"></i></span>
                    <?= html_password('password', 'maxlength="100"') ?>
                    <span class="toggle-password" data-target="password"><i class="fa fa-eye" aria-hidden="true"></i></span>
                </div>
                <?= err('password') ?>

                <div class="form-other">
                    <div class="checkbox">
                        <input type="checkbox" id="rememberMe" name="rememberMe">
                        <label for="rememberMe">Remember Me</label>
                    </div>
                    <a href="forgotPassword.php">Forgot Password?</a>
                </div>
                <div class="form-actions">
                    <button type="submit">Sign In</button>
                    <button type="reset">Reset</button>
                </div>
                <div class="form-back">
   
</div>
                <input type="hidden" name="login_type" value="member">
                <div class="register">
                    Don't have an account? <a href="register.php">Create here</a>.
                </div>
                <a href="index.php" class="back-button" style="text-align:center;">Back to Home</a>
            </form>

            <form id="adminLoginForm" class="login-form hidden" method="post">
                <div class="form-item">
                    <span class="email"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>
                    <?= html_text('admin_email', 'maxlength="100"') ?>
                </div>
                <?= err('admin_email') ?>
                <div class="form-item">
                    <span class="password"><i class="fa fa-unlock-alt" aria-hidden="true"></i></span>
                    <?= html_password('admin_password', 'maxlength="100"') ?>
                    <span class="toggle-password" data-target="admin_password"><i class="fa fa-eye" aria-hidden="true"></i></span>
                </div>
                <?= err('admin_password') ?>
                <div class="form-actions">
                    <button type="submit">Admin Sign In</button>
                    <button type="reset">Reset</button>
                </div>
                <input type="hidden" name="login_type" value="admin">
            </form>

        </div>
    </div>
    <script src="../JS/initializer.js">
        $(document).ready(function() {
            if ($('.tab-button').length) {
                initializeLoginTabs();
            }
            if ($('.toggle-password').length) {
                initializePasswordToggle();
            }
        });
    </script>
</body>

</html>