
<?php  auth();?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="../CSS/adminHeader.css" rel="stylesheet" type="text/css" />
    <link href="../CSS/adminProfile.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../JS/adminProfile.js"></script>
</head>
<body>
    <div class="header">
        <div class="containerHeader">
            <div class="logo">
                <a href="index.php">Admin Dashboard</a>
            </div>
            <div class="user-dropdown">
                <button class="dropbtn">
                    Admin Account <i class="fa fa-caret-down" aria-hidden="true"></i>
                </button>
                <div class="dropdown-content">
                    <a href="#" id="viewProfileBtn"><i class="fa fa-user" aria-hidden="true"></i> Profile</a>
                    <a href="../User/logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div id="profileModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="profile-container">
                <div class="side-nav">
                    <button class="nav-item active" data-target="profileInfo">Profile Information</button>
                    <button class="nav-item" data-target="changePassword">Change Password</button>
                </div>
                <div id="profileContent" class="main-content"></div>
            </div>
        </div>
    </div>

    <script src="../JS/initializer.js">
        $(document).ready(function() {
            if (document.querySelector('.dropbtn')) {
                initializeDropdown();
            }
        });
    </script>
     
</body>

</html>