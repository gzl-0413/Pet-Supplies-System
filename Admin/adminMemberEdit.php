<?php

require_once '../includes/_base.php';
include 'sideNav.php';

$memberId = req('id');

$query = "SELECT * FROM member WHERE member_id = ?";
$stmt = $_db->prepare($query);
$stmt->execute([$memberId]);
$member = $stmt->fetch();

if (!$member) {
    echo "Member not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member</title>
    <link rel="stylesheet" href="../css/adminMemberEdit.css">
    <link rel="stylesheet" href="../css/adminValidation.css">
    <script src="../JS/adminAdminEdit.js"></script>
    <script src="../JS/adminMemberAdd.js"></script>
    <script src="../JS/adminMemberEdit.js"></script>
</head>
<body>
    <div class="form-container">
        <h2>Edit Member Information</h2>
        <form action="adminMember.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($member->member_id); ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <?php echo html_text('username', 'id="username" required', $member->username); ?>
                <div id="username_feedback" class="feedback"></div>
            </div>

            <div class="form-group">
                <label for="birthdate">Birth Date</label>
                <input type="date" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($member->birthdate); ?>" required>
                <div id="birthdate_feedback" class="feedback"></div>
            </div>

            <div class="form-group">
                <label for="contactnumber">Contact Number</label>
                <?php echo html_text('contactnumber', 'id="contactnumber" required', $member->contactnumber); ?>
                <div id="contactnumber_feedback" class="feedback"></div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($member->email); ?>" required>
                <div id="email_feedback" class="feedback"></div>
            </div>

            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" onchange="previewImage(event)">
                
                <?php if (!empty($member->profilepic)): ?>
                    <div class="current-picture">
                        <img id="current-profile-pic" src="../uploads/<?php echo htmlspecialchars($member->profilepic); ?>" alt="Profile Picture" style="max-width: 150px;">
                    </div>
                <?php endif; ?>

                <div class="new-picture-preview">
                    <img id="new-profile-pic" style="display:none; max-width: 150px;">
                </div>
                <div id="profile_picture_feedback" class="feedback"></div>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <?php echo html_text('status', 'id="status" disabled', $member->status); ?>
            </div>

            <div class="button-group">
                <button type="submit" class="update-button">Update Member</button>
                <a href="#" class="cancel-button" onclick="confirmCancel(); return false;">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
