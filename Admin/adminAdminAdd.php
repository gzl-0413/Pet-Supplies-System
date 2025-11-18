<?php

require_once '../includes/_base.php';
include 'sideNav.php';


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

if ($adminlevel !== null && $adminlevel === 'Low') {
    echo "<script>
        alert('You do not have permission to access this page.');
        window.location.href = '../Admin/adminDashboard.php';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Admin</title>
    <link rel="stylesheet" href="../css/adminMemberEdit.css">
    <link rel="stylesheet" href="../css/adminValidation.css">
    <script src="../JS/adminAdminAdd.js"></script>
</head>
<body>
    <div class="form-container">
        <h2>Add New Admin</h2>
        <form action="adminAdmin.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">

            <div class="form-group">
                <label for="username">Name</label>
                <input type="text" id="username" name="username" required>
                <div id="username_feedback" class="feedback"></div>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                <div id="email_feedback" class="feedback"></div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <div id="password_feedback" class="feedback"></div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <div id="confirm_password_feedback" class="feedback"></div>
            </div>

            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" onchange="previewImage(event)">
                <div id="image_preview" class="current-picture" style="margin-top: 10px;"></div>
                <div id="profile_picture_feedback" class="feedback"></div>
            </div>

            <div class="button-group">
                <button type="submit" class="update-button">Add Admin</button>
                <a href="#" class="cancel-button" onclick="confirmCancel(); return false;">Cancel</a>            </div>
            </div>
        </form>
    </div>
</body>
</html>
