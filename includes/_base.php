<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

$_db = new PDO('mysql:dbname=assignmentNew', 'root', '', [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);

$_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// function req($name, $default = null) {
//     return $_REQUEST[$name] ?? $default;
// }


// Obtain REQUEST (GET and POST) parameter
function req($key, $value = null)
{
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}
function reqCx($name, $default = null)
{
    return $_REQUEST[$name] ?? $default;
}

function is_get()
{
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}
function is_post()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Obtain GET parameter
function get($key, $value = null)
{
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain POST parameter
function post($key, $value = null)
{
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}


// function temp($key, $val = null) {
//     if ($val === null) return $_SESSION[$key] ?? '';
//     $_SESSION[$key] = $val;
// }

// Set or get temporary session variable
function temp($key, $value = null)
{
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    } else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}

// function redirect($url) {
//     header("Location: $url");
//     exit;
// }

function redirect($url = null)
{
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

function get_file($key)
{
    $f = $_FILES[$key] ?? null;

    if ($f && $f['error'] == 0) {
        return (object)$f;
    }

    return null;
}


// Is money?
function is_money($value)
{
    return preg_match('/^\-?\d+(\.\d{1,2})?$/', $value);
}

// Is email?
function is_email($value)
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

// Crop, resize and save photo
function save_photo($f, $folder, $width = 200, $height = 200)
{
    $photo = uniqid() . '.jpg';

    require_once '../lib/SimpleImage.php';
    $img = new SimpleImage();
    $img->fromFile($f->tmp_name)
        ->thumbnail($width, $height)
        ->toFile("$folder/$photo", 'image/jpeg');

    return $photo;
}
function get_categories()
{
    global $_db;
    try {
        $stm = $_db->query("SELECT * FROM category ORDER BY name");
        return $stm->fetchAll(PDO::FETCH_OBJ); // Fetch categories as objects
    } catch (PDOException $e) {
        echo 'Database error: ' . $e->getMessage();
        return [];
    }
}

function display_notification($message)
{
    if (!empty($message)) { // Only display if the message is not empty
        echo '<div class="notification visible" id="notification">' . htmlspecialchars($message) . '</div>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                var notification = document.getElementById("notification");
                if (notification) {
                    setTimeout(function() {
                        notification.classList.add("hidden");
                    }, 3000); // Hide after 3 seconds
                }
            });
        </script>';
    }
}


function fetch_low_stock_alerts()
{
    global $_db;
    $stmt = $_db->prepare('
        SELECT a.message, p.name, p.stock
        FROM alerts a
        JOIN product p ON a.product_id = p.id
        WHERE a.type = "low_stock"
    ');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Insert new category into the category table
function insert_category($category)
{
    global $_db;
    $stmt = $_db->prepare('INSERT INTO category (name) VALUES (:category)');
    $stmt->bindValue(':category', $category);
    return $stmt->execute();
}

function encode($value)
{
    return htmlentities($value);
}

function html_text($name, $attrs = '', $val = '')
{
    return sprintf('<input type="text" name="%s" %s value="%s">', $name, $attrs, htmlspecialchars($val));
}

//Gr htmltext
function html_textgr($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' value='$value' $attr>";
}

// Gr htmlNumhber Generate <input type='number'>
// function html_number($key, $min = '', $max = '', $step = '', $attr = '') {
//     $value = encode($GLOBALS[$key] ?? '');
//     echo "<input type='number' id='$key' name='$key' value='$value'
//                  min='$min' max='$max' step='$step' $attr>";
// }

function html_number($name, $min, $max, $step, $val = '')
{
    return sprintf('<input type="number" name="%s" min="%s" max="%s" step="%s" value="%s">', $name, $min, $max, $step, htmlspecialchars($val));
}

//Generate <input type='date'>
function html_date($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='date' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='password'>
function html_password($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='password' id='$key' name='$key' value='$value' $attr>";
}

function html_select($key, $items, $default = '- Select One -', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

function html_select_cx($name, $options, $val)
{
    $html = sprintf('<select name="%s">', $name);
    foreach ($options as $option) {
        $selected = $option['id'] === $val ? ' selected' : ''; // Use id for selection
        $html .= sprintf('<option value="%s"%s>%s</option>', htmlspecialchars($option['id']), $selected, htmlspecialchars($option['name']));
    }
    $html .= '</select>';
    return $html;
}



// Generate <input type='search'>
function html_search($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='search' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='radio'> list
function html_radios($key, $items, $br = false)
{
    $value = encode($GLOBALS[$key] ?? '');
    echo '<div>';
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'checked' : '';
        echo "<label><input type='radio' id='{$key}_$id' name='$key' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

// Generate table headers <th>
function table_headers($fields, $sort, $dir, $href = '')
{
    foreach ($fields as $k => $v) {
        $d = 'asc'; // Default direction
        $c = '';    // Default class

        if ($k == $sort) {
            $d = $dir == 'asc' ? 'desc' : 'asc';
            $c = $dir;
        }

        echo "<th><a href='?sort=$k&dir=$d&$href' class='$c'>$v</a></th>";
    }
}

function html_file($key, $accept = '', $attr = '')
{
    echo "<input type='file' id='$key' name='$key' accept='$accept' $attr>";
}

// ============================================================================
// Error Handlings
// ============================================================================

// Global error array
$_err = [];

// Generate <span class='err'>
function err($key)
{
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='err'>$_err[$key]</span>";
    } else {
        echo '<span></span>';
    }
}

// ============================================================================
// Security
// ============================================================================

// Global user object
$_member = $_SESSION['member'] ?? null;
$_admin = $_SESSION['admin'] ?? null;

if($_member){
    checkBlockedStatus();
}

if($_admin){
    checkAdminBlockedStatus();
}


// Login user
function loginMember($memberId, $url = '/User/index.php')
{
    $_SESSION['member'] = $memberId;

    redirect($url);
}


function loginAdmin($adminId, $url = '/Admin/adminDashboard.php')
{

    $_SESSION['admin'] = $adminId;

    redirect($url);
}

// Logout user
function logout($url = '../User/login.php')
{
    session_regenerate_id(true);
    unset($_SESSION['member']);
    unset($_SESSION['admin']);
    redirect($url);
}

function checkLogin()
{
    if (empty($_SESSION['member'])) {
        echo "<script>alert('Please log in as member to proceed.');</script>";
        return false;
    }
    return true;
}

function cartCheckLogin()
{
    if (empty($_SESSION['member'])) {
        echo "<script>alert('Please log in as member to proceed.');
          window.location.href = '/User/login.php';</script>";
      
              
        return false;
    }
    return true;
}


 
function checkBlockedStatus()
{
    global $_db;

    $stmt = $_db->prepare("SELECT * FROM member WHERE member_id = ?");
    $stmt->execute([$_SESSION['member']]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        if ($result['status'] == 'Block' || $result['status'] == 'TempBlock') {
            if (isset($_SESSION['member'])) {
                unset($_SESSION['member']);
                echo "<script>alert('Your account has been blocked. Please contact support.');
                window.location.href = '/User/login.php';
                </script>";
                exit();
            }
            return true; 
        }
    }

    return false; 
}

function checkAdminBlockedStatus()
{
    global $_db;

    $stmt = $_db->prepare("SELECT * FROM admin WHERE admin_id = ?");
    $stmt->execute([$_SESSION['admin']]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        if ($result['status'] == 'Block' || $result['status'] == 'TempBlock') {
            if (isset($_SESSION['admin'])) {
                unset($_SESSION['admin']);
                echo "<script>alert('Your account has been blocked. Please contact support.');
                window.location.href = '/User/login.php';
                </script>";
                exit();
            }
            return true; 
        }
    }

    return false; 
}


function getTop10HotSalesProducts() {
    global $_db;
    
    $stmt = $_db->prepare("
        SELECT p.id, p.name, p.photo, p.oriPrice, SUM(o.quantity) as totalSold
        FROM product p
        JOIN orders o ON p.id = o.productId
        GROUP BY p.id
        ORDER BY totalSold DESC
        LIMIT 10
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}   

function getTop5MostWishedProducts() {
    global $_db;
    
    $stmt = $_db->prepare("
        SELECT p.id, p.name, p.photo, p.oriPrice, COUNT(w.productId) as wishCount
        FROM product p
        JOIN wishlist w ON p.id = w.productId
        GROUP BY p.id
        ORDER BY wishCount DESC
        LIMIT 5
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}



function updateCartWithMemberId($memberId)
{
    $sessionId = session_id();

    global $_db;
    $stmt = $_db->prepare('UPDATE temp_cart SET member_Id = ? WHERE session_id = ?');
    $stmt->execute([$memberId, $sessionId]);
}
// Authorization
function auth(...$roles)
{
    global $_member, $_admin;
    if ($_member) {
        if (empty($roles) || in_array($_member->role, $roles)) {
            return;
        }
    }
    if ($_admin) {
        if (empty($roles) || in_array('admin', $roles)) {
            return;
        }
    }
    $_SESSION['alert'] = [
        'type' => 'error',
        'message' => 'You are not authorized to access this page. Please log in with appropriate credentials.'
    ];
    redirect('../User/login.php');
}

// ============================================================================
// Database Setups and Functions
// ============================================================================



// Is raanique?
function is_unique($value, $table, $field)
{
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

// Is exists?
function is_exists($value, $table, $field)
{
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}

//promo
function getPromoCode($promoCode)
{
    global $_db;
    // Adjust the SQL to check that the promo code is active (status = 1)
    $stmt = $_db->prepare('SELECT * FROM promotion WHERE promoId = ? AND status = 1 LIMIT 1');
    $stmt->execute([$promoCode]);
    return $stmt->fetch(PDO::FETCH_ASSOC);  // Return promo details or false if not found
}


// ============================================================================
// Global Constants and Variables
// ============================================================================

// ============================================================================
// Email Functions
// ============================================================================

// Demo Accounts:
// --------------
// AACS3173@gmail.com           npsg gzfd pnio aylm
// BAIT2173.email@gmail.com     ytwo bbon lrvw wclr
// liaw.casual@gmail.com        wtpa kjxr dfcb xkhg
// liawcv1@gmail.com            obyj shnv prpa kzvj

// Initialize and return mail object
function get_mail()
{
    require_once '../lib/PHPMailer.php';
    require_once '../lib/SMTP.php';

    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->Host = 'smtp.gmail.com';
    $m->Port = 587;
    $m->Username = 'guangrongooi1@gmail.com';
    $m->Password = 'ztdi ewpa ddqn qnmg';
    $m->CharSet = 'utf-8';
    $m->setFrom($m->Username, '😺 Admin');

    return $m;
}

function verification_email($email, $activation_token_hash)
{
    $mail = get_mail();
    $mail->addAddress($email);
    $mail->isHTML(true);

    $verification_link = "http://localhost:8000/User/verify.php?token=$activation_token_hash";

    $mail->Subject = 'noreply';
    $mail->Body = "<b>Hello</b>,<br><br>
                    Thank you for registering with us. Please click on the <a href='$verification_link'>link</a> to verify your account.
                    <br>Thank You.";

    if (!$mail->send()) {
        echo "Error sending email.";
    }
}

function receipt_email($email, $recipientName, $orderId, $cartItems, $shippingFee, $finalTotal, $address, $postalCode, $state, $contactNumber, $transactionId, $timestamp)
{
    $mail = get_mail(); // Assuming get_mail() initializes PHPMailer
    $mail->addAddress($email);
    $mail->isHTML(true);

    // Create the email subject
    $mail->Subject = 'Your Order Receipt - Order ID: ' . $orderId;

    // Create the HTML body
    $mail->Body = "
    <div style='font-family: Arial, sans-serif;'>
        <h1 style='color: #008080; text-align: right;'>Receipt</h1>
        <table width='100%' cellspacing='0' cellpadding='10' border='0' style='border-collapse: collapse;'>
            <tr>
                <td width='50%' style='vertical-align: top;'>
                    <strong>GUANGRONG PETTY</strong><br>
                    Address:TARUMT Setapak Branch<br>
                    City: KL<br>
                    Country: Malaysia<br>
                    Post Code: 53300<br>
                </td>
                <td width='50%' style='vertical-align: top; text-align: right;'>
                    <strong>Details:</strong><br>
                    <strong>Transaction No:</strong> $transactionId<br>
                    <strong>Receipt Date:</strong> " . date('Y-m-d') . "<br>
                    <strong>Payment Date:</strong> $timestamp<br>
                </td>
            </tr>
            <tr>
                <td width='50%' style='background-color: #f2f2f2; padding: 10px;'>
                    <strong>Bill to:</strong> 
                    $recipientName<br>
                    $address, $state, $postalCode<br>
                    Contact: $contactNumber<br>
                </td>
                <td width='50%' style='background-color: #f2f2f2; padding: 10px;'>
                    <strong>Details:</strong><br>
                    <strong>Order ID:</strong> $orderId<br>
                </td>
            </tr>
        </table>
        <br>
        <table width='100%' cellspacing='0' cellpadding='5' border='1' style='border-collapse: collapse; text-align: left;'>
            <thead style='background-color: #f2f2f2;'>
                <tr>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Rate (RM)</th>
                    <th>Amount (RM)</th>
                </tr>
            </thead>
            <tbody>";

    // Loop through each cart item and append it to the email body
    foreach ($cartItems as $item) {
        $mail->Body .= "<tr>
                            <td>" . htmlspecialchars($item['productName'], ENT_QUOTES, 'UTF-8') . "</td>
                            <td>" . (int)$item['quantity'] . "</td>
                            <td>" . number_format((float)$item['subtotal'] / (int)$item['quantity'], 2) . "</td>
                            <td>" . number_format((float)$item['subtotal'], 2) . "</td>
                        </tr>";
    }

    $mail->Body .= "
            </tbody>
        </table>
        <br>
        <table width='100%' cellspacing='0' cellpadding='5' border='0' style='text-align: right;'>
            <tr>
                <td style='padding-right: 20px;'>Sub-total:</td>
                <td style='padding-left: 20px;'>RM " . number_format($finalTotal - $shippingFee, 2) . "</td>
            </tr>
            <tr>
                <td style='padding-right: 20px;'>Shipping Fee:</td>
                <td style='padding-left: 20px;'>RM " . number_format($shippingFee, 2) . "</td>
            </tr>
            <tr>
                <td style='padding-right: 20px;'>Total Paid:</td>
                <td style='padding-left: 20px; font-weight: bold;'>RM " . number_format($finalTotal, 2) . "</td>
            </tr>
        </table>
        <br><br>
        <p style='text-align: center; font-size: 12px; color: #999;'>Thank you for shopping with us!</p>
    </div>";

    // Send the email
    if (!$mail->send()) {
        echo "Error sending email.";
        error_log("Error sending e-receipt to $email: " . $mail->ErrorInfo);
    }
}
