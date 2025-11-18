<?php
include '../includes/_base.php';
auth(); // Assuming you have an adminAuth function

// Ensure the admin is logged in
if (!isset($_SESSION['admin'])) {
    redirect('../User/login.php');
}

$admin_id = $_SESSION['admin'];

// Fetch admin information
$stm = $_db->prepare('SELECT * FROM admin WHERE admin_id = ?');
$stm->execute([$admin_id]);
$admin = $stm->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    redirect('adminDashboard.php'); //change to admin homepage
}

$_title = 'Admin Profile';
?>

<link rel="stylesheet" href="../CSS/adminProfile.css">
<script src="../JS/adminProfile.js"></script>

<div class="admin-profile">
    <div class="profile-modal">
        <h2>Admin Profile</h2>
        <div class="profile-container">
            <img src="../uploads/<?= htmlspecialchars($admin['adminprofilepic']) ?>" alt="Profile Picture" class="profile-picture">
            <div class="profile-info">
                <div class="form-group">
                    <label for="adminemail">Email:</label>
                    <span id="adminemail"><?= htmlspecialchars($admin['adminemail']) ?></span>
                </div>
                <div class="form-group">
                    <label for="adminname">Name:</label>
                    <span id="adminname" class="editable"><?= htmlspecialchars($admin['adminname']) ?></span>
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
</div>