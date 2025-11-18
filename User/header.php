<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="../CSS/header.css" rel="stylesheet" type="text/css" />
    <link href="../CSS/productPage.css" rel="stylesheet" type="text/css" />
    <link href="../CSS/cart.css" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>



    <!-- <title><?= $_title ?? 'Untitled' ?></title> -->
</head>

<body>
    <!-- Flash message -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div id="info"><?= $_SESSION['flash_message'];
                        unset($_SESSION['flash_message']); ?></div>
    <?php endif; ?>

    <div class="header">
        <div class="container-1">
            <div class="top-1">
                <p style="width:50%">Call Us: +60 11-5699 4938</p>
                <p style="width:50%">Email: <a href="mailto:petshop@example.com">petshop@example.com</a></p>
            </div>
            <div class="top-2">
                <p class="fb"><a href="#"><i class="fa fa-facebook-official" aria-hidden="true"></i></a></p>
                <p class="ins"><a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a></p>
                <p class="twi"><a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a></p>
                <p class="yt"><a href="#"><i class="fa fa-youtube-play" aria-hidden="true"></i></a></p>
            </div>
        </div>
        <div class="container-2">
            <div class="bottom-1">
                <p class="logo"><a href="index.php">Home<i class="fa fa-home" aria-hidden="true"></i></a></p>
            </div>
            <div class="bottom-2">
                <p class="producticon" style="border: none;"><a href="product.php">Products<i class="fa fa-shopping-bag" aria-hidden="true"></i></a></p>
            </div>
            <div class="bottom-3">
                <p class="cart"><a href="cart.php">Cart<i class="fa fa-shopping-cart" aria-hidden="true"></i>
                <?php if ($_member): ?>
             <?php
            $userId = $_member; 
            $stmt = $_db->prepare("SELECT COUNT(*) as totalItems FROM temp_cart WHERE member_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);      
            $totalItems = $result && $result['totalItems'] ? $result['totalItems'] : 0;
            ?>
            <span class="cart-count">(<?= $totalItems ?>)</span>
            
            </a></p>
                <div class="user-dropdown">
                        <button class="dropbtn">
                            My Account <i class="fa fa-caret-down" aria-hidden="true"></i>
                        </button>
                        <div class="dropdown-content">
                            <a href="#" id="viewProfileBtn"><i class="fa fa-user" aria-hidden="true"></i> Profile</a>
                            <a href="paymentHistory.php" ><i class="fa fa-history" aria-hidden="true"></i> Payment History</a>
                            <a href="viewReview.php" ><i class="fa fa-edit" aria-hidden="true"></i> Review History</a>
                            <a href="logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a>
                        </div>
                    </div>
                    <div id="profileModal" class="modal">
                        <div class="modal-content">
                            <span class="close">&times;</span>
                            <div class="profile-container">
                                <div class="side-nav">
                                    <button class="nav-item active" data-target="profileInfo">Profile Information</button>
                                    <button class="nav-item" data-target="changePassword">Change Password</button>
                                    <button class="nav-item" data-target="order">Order</button>
                                    <button class="nav-item" data-target="wishlist"><i class="fa fa-heart"></i>WishList</button>
                                </div>

                                <!-- ij part -->
                                <div id="profileContent" class="main-content"></div>
                                <div id="orderDetailsModal" class="modal" style="display:none;">
                                 <div class="modal-content">
                             <span id="closeModal" class="close">&times;</span>
                             <div id="orderDetailsContainer"></div>
                                      </div>
                                                      </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($_admin): ?>
                    <p class="register"><a href="../Admin/adminDashboard.php">Admin Dashboard</a></p>
                <?php else : ?>
                    <p class="register"><a href="register.php">Register<i class="fa fa-user-plus" aria-hidden="true"></i></a></p>
                    <p class="login"><a href="login.php">Login<i class="fa fa-sign-in" aria-hidden="true"></i></a></p>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <main>
        <!-- <h1><?= $_title ?? 'Untitled' ?></h1> -->
    </main>

    <script src="../JS/initializer.js">
        $(document).ready(function() {
            if (document.querySelector('.dropbtn')) {
                initializeDropdown();
            }
        });
    </script>
    <script src="../JS/memberProfile.js"></script>
</body>

</html>