<?php
$adminlevel = null;

if (!isset($_SESSION['admin']) || $_SESSION['admin'] === null) {
    echo "<script>
        alert('You do not have permission to access this page.');
        window.location.href = '../User/index.php';
    </script>";
    exit;
}

if (isset($_SESSION['admin']) && $_SESSION['admin'] !== null) {
    $admin_id = $_SESSION['admin'];
    $sql = "SELECT adminlevel 
            FROM admin 
            WHERE admin_id = :admin_id";
    $stmt = $_db->prepare($sql);
    $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $adminlevel = $result['adminlevel'];
    }   
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
       
        <link href="../CSS/sideNav.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    </head>
    <body>
        <div class="navbar">
            <div class="main">
                <div>
                    <a href="adminDashboard.php" class="bar">
                        <i class="fa fa-globe" aria-hidden="true"></i>
                        <p>Dashboard</p>
                    </a>
                </div>
                <?php
                if ($adminlevel !== null && $adminlevel === 'High') {
                    ?>
                    <div>
                        <a href="adminAdmin.php" class="bar">
                            <i class="fa fa-address-book" aria-hidden="true"></i>
                            <p>Admin</p>
                        </a>
                    </div>
                    
                    <?php
                }
                ?>
                <div>
                    <a href="adminMember.php" class="bar">
                        <i class="fa fa-address-card" aria-hidden="true"></i>
                        <p>Member</p>
                    </a>
                </div>
                <div>
                    <a href="list.php" class="bar">
                        <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                        <p>Products</p>
                    </a>
                </div>
                <div>
                    <a href="stock.php" class="bar">
                        <i class="fa fa-cubes" aria-hidden="true"></i> 
                        <p>Stock</p>
                    </a>
                </div> 
                <div>
                    <a href="category.php" class="bar">
                        <i class="fa fa-sitemap" aria-hidden="true"></i> 
                        <p>Category</p>
                    </a>
                </div> 
                <div>
                    <a href="AdminOrderStatus.php" class="bar">
                        <i class="fa fa-shopping-bag" aria-hidden="true"></i>
                        <p>Order</p>
                    </a>
                </div>
                <div>
                    <a href="adminPayment.php" class="bar">
                        <i class="fa fa-money" aria-hidden="true"></i>
                        <p>Payment</p>
                    </a>
                </div>
                <div>
                    <a href="adminDiscount.php" class="bar">
                        <i class="fa fa-usd" aria-hidden="true"></i>
                        <p>Discount</p>
                    </a>
                </div>
                <div>
                    <a href="adminReview.php" class="bar">
                        <i class="fa fa-comment" aria-hidden="true"></i>
                        <p>Review</p>
                    </a>
                </div>
 
                <div>
                    <a href="../User/logout.php" class="bar">
                        <i class="fa fa-sign-out" aria-hidden="true"></i>
                        <p>Logout</p>
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
