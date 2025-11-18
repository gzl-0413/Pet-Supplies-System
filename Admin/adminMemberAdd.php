<?php

require_once '../includes/_base.php';
include 'sideNav.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Member</title>
    <link rel="stylesheet" href="../css/adminMemberEdit.css">
    <link rel="stylesheet" href="../css/adminValidation.css">
    <script src="../JS/adminAdminAdd.js"></script>
    <script src="../JS/adminMemberAdd.js"></script>
</head>
<body>
    <div class="form-container">
        <h2>Add New Member</h2>
        <form action="adminMember.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
                <div id="username_feedback" class="feedback"></div>
            </div>

            <div class="form-group">
                <label for="birthdate">Birth Date</label>
                <input type="date" id="birthdate" name="birthdate" required>
                <div id="birthdate_feedback" class="feedback"></div>
            </div>

            <div class="form-group">
                <label for="contactnumber">Contact Number</label>
                <input type="text" id="contactnumber" name="contactnumber" required>
                <div id="contactnumber_feedback" class="feedback"></div>
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
                <button type="submit" class="update-button">Add Member</button>
                <a href="#" class="cancel-button" onclick="confirmCancel(); return false;">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
