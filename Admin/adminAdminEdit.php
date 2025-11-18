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

$adminId = req('id');

$query = "SELECT * FROM admin WHERE admin_id = ?";
$stmt = $_db->prepare($query);
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

if (!$admin) {
    echo "Admin not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin</title>
    <link rel="stylesheet" href="../css/adminMemberEdit.css">
    <link rel="stylesheet" href="../css/adminValidation.css">
    <script src="../JS/adminAdminEdit.js"></script>
</head>
<body>
    <div class="form-container">
        <h2>Edit Admin Information</h2>
        <form action="adminAdmin.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <input type="hidden" name="admin_id" value="<?php echo htmlspecialchars($admin->admin_id); ?>">

            <div class="form-group">
                <label for="username">Name</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin->adminname); ?>" required>
                <div id="username_feedback" class="feedback"></div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin->adminemail); ?>" required>
                <div id="email_feedback" class="feedback"></div>
            </div>

            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" onchange="previewImage(event)">
                
                <?php if (!empty($admin->adminprofilepic)): ?>
                    <div class="current-picture">
                        <img id="current-profile-pic" src="../uploads/<?php echo htmlspecialchars($admin->adminprofilepic); ?>" alt="Profile Picture" style="max-width: 150px;">
                    </div>
                <?php endif; ?>
                <div class="new-picture-preview">
                    <img id="new-profile-pic" style="display:none; max-width: 150px;">
                </div>
                <div id="profile_picture_feedback" class="feedback"></div>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <?php echo html_text('status', 'id="status" disabled', $admin->status); ?>
            </div>

            <div class="button-group">
                <button type="submit" class="update-button">Update Admin</button>
                <a href="#" class="cancel-button" onclick="confirmCancel(); return false;">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>

