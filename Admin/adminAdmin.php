<?php 

require_once '../includes/_base.php';
include 'sideNav.php'; 
include 'adminHeader.php';
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

function generateNextAdminID($_db) {
    try {
        $query = "SELECT admin_id FROM admin ORDER BY admin_id DESC LIMIT 1";
        $stmt = $_db->prepare($query);
        $stmt->execute();
        $latestAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($latestAdmin) {
            $latestID = (int)substr($latestAdmin['admin_id'], 1);
            $nextID = $latestID + 1;
            return 'A' . str_pad($nextID, 4, '0', STR_PAD_LEFT);
        } else {
            return 'A0001';
        }
    } catch (PDOException $e) {
        echo 'Database error: ' . $e->getMessage();
    }
}

if (is_post()) {
    $adminId = req('admin_id');

    if ($adminId) {
        // edit admin
        $adminname = req('username');
        $adminemail = req('email');
        
        $query = "SELECT adminprofilepic FROM admin WHERE admin_id = ?";
        $stmt = $_db->prepare($query);
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            echo "Admin not found.";
            exit;
        }
        
        $profilepic = $admin->adminprofilepic;
        
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $targetDirectory = '../uploads/';
            $fileName = basename($_FILES['profile_picture']['name']);
            $targetFilePath = $targetDirectory . $fileName;
        
            if (!is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0755, true);
            }

            if ($profilepic && file_exists($targetDirectory . $profilepic) && $profilepic !== $fileName) {
                unlink($targetDirectory . $profilepic);
            }
        
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
                $profilepic = $fileName;
            } else {
                echo 'File upload failed.';
                exit;
            }
        }
        
        try {
            $updateQuery = "UPDATE admin 
                            SET adminname = ?, adminemail = ?, adminprofilepic = ? 
                            WHERE admin_id = ?";
            $stmt = $_db->prepare($updateQuery);
            $stmt->execute([$adminname, $adminemail, $profilepic, $adminId]);
        
            echo "<script>
                window.location.href = 'adminAdmin.php?success_edit=$adminId';
                </script>";
            exit;

        } catch (PDOException $e) {
            echo 'Database error: ' . $e->getMessage();
        }

    } else {
        // add admin
        $admin_id = generateNextAdminID($_db);
        $adminname = req('username');
        $adminemail = req('email');
        $password = sha1(req('password'));
        $adminlevel = 'Low';
        $adminstatus = 'Active';
        
        $profile_picture = null;
        
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $targetDirectory = '../uploads/';
            $fileExtension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $randomFileName = uniqid() . '.' . $fileExtension;
            $targetFilePath = $targetDirectory . $randomFileName;
        
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
                $profile_picture = $randomFileName;
            } else {
                echo 'File upload failed.';
            }
        }
        
        try {
            $insertQuery = "INSERT INTO admin (admin_id, adminname, adminemail, adminpassword, adminprofilepic, adminlevel, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $_db->prepare($insertQuery);
            $stmt->execute([$admin_id, $adminname, $adminemail, $password, $profile_picture, $adminlevel, $adminstatus]);
        
            echo "<script>
                    window.location.href = 'adminAdmin.php?success_add=$admin_id';
                </script>";
            exit;
        } catch (PDOException $e) {
            echo 'Database error: ' . $e->getMessage();
        }
        
    }
}

$search = req('search');
$page = max(1, (int)req('page', 1));
$perPage = req('rowsPerPage', 5);
$start = ($page - 1) * $perPage;

try {
    $query = 'SELECT * FROM admin WHERE adminlevel = "Low"';
    
    if ($search) {
        $query .= ' AND (adminname LIKE :search OR adminemail LIKE :search)';
    }

    $query .= ' LIMIT :start, :perPage';
    
    $stm = $_db->prepare($query);
    
    if ($search) {
        $stm->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $stm->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stm->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
    $stm->execute();
    
    $admins = $stm->fetchAll();
    
    $totalQuery = 'SELECT COUNT(*) FROM admin WHERE adminlevel = "Low"';
    if ($search) {
        $totalQuery .= ' AND (adminname LIKE :search OR adminemail LIKE :search)';
    }
    
    $totalStm = $_db->prepare($totalQuery);
    if ($search) {
        $totalStm->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $totalStm->execute();
    $total = $totalStm->fetchColumn();
    
    $totalPages = ceil($total / $perPage);
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}

$sort = req('sort') ?? 'admin_id';
$order = req('order') ?? 'asc';

$allowedSorts = ['admin_id', 'adminname'];
$allowedOrders = ['asc', 'desc'];

if (!in_array($sort, $allowedSorts)) {
    $sort = 'admin_id';
}
if (!in_array($order, $allowedOrders)) {
    $order = 'asc';
}

try {
    $query = "SELECT * FROM admin WHERE adminlevel = 'Low'";
    
    if ($search) {
        $query .= " AND (adminname LIKE :search OR adminemail LIKE :search)";
    }
    
    $query .= " ORDER BY $sort $order LIMIT :start, :perPage";
    
    $stm = $_db->prepare($query);
    
    if ($search) {
        $stm->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $stm->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stm->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
    $stm->execute();
    
    $admins = $stm->fetchAll();
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management</title>
    <link rel="stylesheet" href="../css/adminMember.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Admin Management</h1>
        <div class="search-container">
            <form method="get" action="">
                <label for="searchInput">Search:</label>
                <input type="text" id="searchInput" name="search" placeholder="Name or Email..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="edit-btn">Search</button>
                <a href="adminAdminAdd.php" class="add-btn">Add</a>
            </form>
        </div>
        <table id="adminTable">
            <thead>
                <tr>
                    <th>Profile Picture</th>
                    <th>
                        <a href="?search=<?= urlencode($search) ?>&rowsPerPage=<?= $perPage ?>&sort=admin_id&order=<?= ($sort === 'admin_id' && $order === 'asc') ? 'desc' : 'asc' ?>" class="sort-link">
                            ID <?= ($sort === 'admin_id') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?search=<?= urlencode($search) ?>&rowsPerPage=<?= $perPage ?>&sort=adminname&order=<?= ($sort === 'adminname' && $order === 'asc') ? 'desc' : 'asc' ?>" class="sort-link">
                            Admin Name <?= ($sort === 'adminname') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?>
                        </a>
                    </th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                <tr>
                    <td class="profilepic-column"><img src="../uploads/<?= htmlspecialchars($admin->adminprofilepic) ?>" width="70" height="70" alt="Profile"></td>
                    <td><?= htmlspecialchars($admin->admin_id) ?></td>
                    <td><?= htmlspecialchars($admin->adminname) ?></td>
                    <td><?= htmlspecialchars($admin->adminemail) ?></td>
                    <td class="profilepic-column">
                        <label class="switch">
                            <input type="checkbox" class="toggle-status" data-id="<?= $admin->admin_id ?>" <?= $admin->status == 'Active' ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                    </td>
                    <td class="profilepic-column">
                        <div class="action-buttons">
                            <a href="adminAdminEdit.php?id=<?= $admin->admin_id ?>" class="edit-btn">Edit</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($admins)): ?>
                <tr><td colspan="6">No admins found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="pagination-controls">
            <label for="rowsPerPage">Rows per page:</label>
            <form method="get" action="">
                <select id="rowsPerPage" name="rowsPerPage" onchange="this.form.submit()">
                    <option value="5" <?= $perPage == 5 ? 'selected' : '' ?>>5</option>
                    <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                    <option value="15" <?= $perPage == 15 ? 'selected' : '' ?>>15</option>
                </select>
            </form>
            <div id="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="<?= ($i == $page) ? 'active' : '' ?>">
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&rowsPerPage=<?= $perPage ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <script src="../JS/adminAdmin.js"></script>
</body>
</html>
