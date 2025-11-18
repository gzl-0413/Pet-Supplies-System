<?php
include '../includes/_base.php';
auth();

if (!isset($_SESSION['member'])) {
    redirect('login.php');
}

$member_id = $_SESSION['member'];

$stm = $_db->prepare('SELECT * FROM member WHERE member_id = ?');
$stm->execute([$member_id]);
$member = $stm->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    redirect('index.php');
}

$_title = 'Member Profile';
?>


<link rel="stylesheet" href="../CSS/memberProfile.css">
<script src="../JS/memberProfile.js"></script>

<div class="profile-modal">
    <h2>Member Profile</h2>
    <div class="profile-container">
        <img src="../uploads/<?= htmlspecialchars($member['profilepic']) ?>" alt="Profile Picture" class="profile-picture">
        <div class="profile-info">
            <div class="form-group">
                <label for="email">Email:</label>
                <span id="email"><?= htmlspecialchars($member['email']) ?></span>
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <span id="username" class="editable"><?= htmlspecialchars($member['username']) ?></span>
            </div>
            <div class="form-group">
                <label for="contactnumber">Contact Number:</label>
                <span id="contactnumber" class="editable"><?= htmlspecialchars($member['contactnumber']) ?></span>
            </div>
            <div class="form-group">
                <label for="birthdate">Birthdate:</label>
                <span id="birthdateSpan"><?= htmlspecialchars($member['birthdate']) ?></span>
            </div>
        </div>
    </div>
    <div class="profile-actions">
        <button id="editProfileBtn" class="btn btn-primary">Edit Profile</button>
        <button id="changeProfilePicBtn" class="btn btn-success" style="display: none;">Change Picture</button>
        <button id="saveProfileBtn" class="btn btn-primary" style="display: none;">Save Profile</button>
    </div>
</div>
<input type="file" id="profilePicInput" style="display: none;" accept="image/*">