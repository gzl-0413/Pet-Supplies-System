
<?php

require_once '../includes/_base.php';
auth();
// include 'header.php';


if (!isset($_GET['success']) || $_GET['success'] != '1') {
    header('Location: index.php');
    exit();
}
 
?>



<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="../CSS/header.css" rel="stylesheet" type="text/css"/>
    <link href="../CSS/productPage.css" rel="stylesheet" type="text/css"/>
    <link href="../CSS/cart.css" rel="stylesheet" type="text/css"/>
    <link href="../CSS/successful.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Stripe Payment Success</title>
</head>
<body>
<div class="container">
        <div class="checkmark">✔</div>
        <h1>Payment Successful!</h1>
        <h4>Your payment has been completed.</h4></br>
        <p>An receipt has sent to your email.</p>
        <a href="index.php" class="finish-btn">Finish</a>
    </div>

</body>
</html>

 