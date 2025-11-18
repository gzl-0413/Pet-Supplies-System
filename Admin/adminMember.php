<?php 
require_once '../includes/_base.php';
include 'sideNav.php'; 
include 'adminHeader.php';
 

function generateNextMemberID($_db) {
    try {
        $query = "SELECT member_id FROM member ORDER BY member_id DESC LIMIT 1";
        $stmt = $_db->prepare($query);
        $stmt->execute();
        $latestMember = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($latestMember) {
            $latestID = (int)substr($latestMember['member_id'], 1);
            $nextID = $latestID + 1;
            return 'M' . str_pad($nextID, 4, '0', STR_PAD_LEFT);
        } else {
            return 'M0001';
        }
    } catch (PDOException $e) {
        echo 'Database error: ' . $e->getMessage();
    }
}

if (is_post()) {
    $memberId = req('member_id');

    if ($memberId) {
        $username = req('username');
        $birthdate = req('birthdate');
        $contactnumber = req('contactnumber');
        $email = req('email');
        
        $query = "SELECT profilepic FROM member WHERE member_id = ?";
        $stmt = $_db->prepare($query);
        $stmt->execute([$memberId]);
        $member = $stmt->fetch();

        $profilepic = $member->profilepic;

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
            $updateQuery = "UPDATE member 
                            SET username = ?, birthdate = ?, contactnumber = ?, email = ?, profilepic = ? 
                            WHERE member_id = ?";
            $stmt = $_db->prepare($updateQuery);
            $stmt->execute([$username, $birthdate, $contactnumber, $email, $profilepic, $memberId]);

            echo "<script>
                    window.location.href = 'adminMember.php?success_edit=$memberId';
                </script>";
            exit;
        } catch (PDOException $e) {
            echo 'Database error: ' . $e->getMessage();
        }

    } else {
        $member_id = generateNextMemberID($_db);
        $username = req('username');
        $birthdate = req('birthdate');
        $contactnumber = req('contactnumber');
        $email = req('email');
        $password = sha1(req('password'));
        $status = 'Verified'; 

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
            $insertQuery = "INSERT INTO member (member_id, username, birthdate, contactnumber, email, password, profilepic, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $_db->prepare($insertQuery);
            $stmt->execute([$member_id, $username, $birthdate, $contactnumber, $email, $password, $profile_picture, $status]);

            echo "<script>
                    window.location.href = 'adminMember.php?success_add=$member_id';
                </script>";
            exit;
        } catch (PDOException $e) {
            echo 'Database error: ' . $e->getMessage();
        }
    }
}

$search = req('search');
$startDate = req('startDate');
$endDate = req('endDate');
$page = max(1, (int)req('page', 1));
$perPage = req('rowsPerPage', 5);
$start = ($page - 1) * $perPage;

try {
    $query = 'SELECT * FROM member WHERE 1';
    
    if ($search) {
        $query .= ' AND (username LIKE :search OR email LIKE :search)';
    }
    
    if ($startDate && $endDate) {
        $query .= ' AND (birthdate BETWEEN :startDate AND :endDate)';
    }

    $query .= ' LIMIT :start, :perPage';
    
    $stm = $_db->prepare($query);
    
    if ($search) {
        $stm->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    if ($startDate && $endDate) {
        $stm->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $stm->bindValue(':endDate', $endDate, PDO::PARAM_STR);
    }
    $stm->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stm->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
    $stm->execute();
    
    $members = $stm->fetchAll();
    
    $totalQuery = 'SELECT COUNT(*) FROM member WHERE 1';
    if ($search) {
        $totalQuery .= ' AND (username LIKE :search OR email LIKE :search)';
    }
    if ($startDate && $endDate) {
        $totalQuery .= ' AND (birthdate BETWEEN :startDate AND :endDate)';
    }
    
    $totalStm = $_db->prepare($totalQuery);
    if ($search) {
        $totalStm->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    if ($startDate && $endDate) {
        $totalStm->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $totalStm->bindValue(':endDate', $endDate, PDO::PARAM_STR);
    }
    $totalStm->execute();
    $total = $totalStm->fetchColumn();
    
    $totalPages = ceil($total / $perPage);
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}

$sort = req('sort') ?? 'member_id';
$order = req('order') ?? 'asc';

$allowedSorts = ['member_id', 'username', 'birthdate'];
$allowedOrders = ['asc', 'desc'];

if (!in_array($sort, $allowedSorts)) {
    $sort = 'member_id';
}
if (!in_array($order, $allowedOrders)) {
    $order = 'asc';
}

try {
    $query = "SELECT * FROM member WHERE 1";
    
    if ($search) {
        $query .= " AND (username LIKE :search OR email LIKE :search)";
    }
    
    if ($startDate && $endDate) {
        $query .= " AND (birthdate BETWEEN :startDate AND :endDate)";
    }
    
    $query .= " ORDER BY $sort $order LIMIT :start, :perPage";
    
    $stm = $_db->prepare($query);
    
    if ($search) {
        $stm->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    if ($startDate && $endDate) {
        $stm->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $stm->bindValue(':endDate', $endDate, PDO::PARAM_STR);
    }
    $stm->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stm->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
    $stm->execute();
    
    $members = $stm->fetchAll();
    
    $totalQuery = "SELECT COUNT(*) FROM member WHERE 1";
    if ($search) {
        $totalQuery .= " AND (username LIKE :search OR email LIKE :search)";
    }
    if ($startDate && $endDate) {
        $totalQuery .= " AND (birthdate BETWEEN :startDate AND :endDate)";
    }
    
    $totalStm = $_db->prepare($totalQuery);
    if ($search) {
        $totalStm->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    if ($startDate && $endDate) {
        $totalStm->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $totalStm->bindValue(':endDate', $endDate, PDO::PARAM_STR);
    }
    $totalStm->execute();
    $total = $totalStm->fetchColumn();
    
    $totalPages = ceil($total / $perPage);
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Member Management</title>
    <link rel="stylesheet" href="../css/adminMember.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Member Management</h1>
        <div class="search-container">
            <form method="get" action="">
                <label for="searchInput">Search:</label>
                <input type="text" id="searchInput" name="search" placeholder="Username or Email..." value="<?= htmlspecialchars($search) ?>">
                <label for="startDate">Start Date:</label>
                <input type="date" id="startDate" name="startDate" value="<?= htmlspecialchars($startDate) ?>">
                <label for="endDate">End Date:</label>
                <input type="date" id="endDate" name="endDate" value="<?= htmlspecialchars($endDate) ?>">
                <button type="submit" class="edit-btn">Search</button>
                <a href="adminMemberAdd.php" class="add-btn">Add</a>
            </form>
        </div>
        <table id="memberTable">
            <thead>
                <tr>
                    <th>Profile Picture</th>
                    <th>
                        <a href="?search=<?= urlencode($search) ?>&startDate=<?= urlencode($startDate) ?>&endDate=<?= urlencode($endDate) ?>&rowsPerPage=<?= $perPage ?>&sort=member_id&order=<?= ($sort === 'member_id' && $order === 'asc') ? 'desc' : 'asc' ?>" class="sort-link">
                            ID <?= ($sort === 'member_id') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?search=<?= urlencode($search) ?>&startDate=<?= urlencode($startDate) ?>&endDate=<?= urlencode($endDate) ?>&rowsPerPage=<?= $perPage ?>&sort=username&order=<?= ($sort === 'username' && $order === 'asc') ? 'desc' : 'asc' ?>" class="sort-link">
                            Username <?= ($sort === 'username') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?search=<?= urlencode($search) ?>&startDate=<?= urlencode($startDate) ?>&endDate=<?= urlencode($endDate) ?>&rowsPerPage=<?= $perPage ?>&sort=birthdate&order=<?= ($sort === 'birthdate' && $order === 'asc') ? 'desc' : 'asc' ?>" class="sort-link">
                            Birth Date <?= ($sort === 'birthdate') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?>
                        </a>
                    </th>
                    <th>Contact Number</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                <tr>
                    <td class="profilepic-column">
                        <img src="../uploads/<?= htmlspecialchars($member->profilepic) ?>" width="70" height="70" alt="Profile">
                    </td>
                    <td><?= htmlspecialchars($member->member_id) ?></td>
                    <td><?= htmlspecialchars($member->username) ?></td>
                    <td><?= htmlspecialchars($member->birthdate) ?></td>
                    <td><?= htmlspecialchars($member->contactnumber) ?></td>
                    <td><?= htmlspecialchars($member->email) ?></td>
                    <td class="profilepic-column">
                        <?php if ($member->status == 'Unverified'): ?>
                            <span>Unverified</span>
                        <?php else: ?>
                            <label class="switch">
                                <input type="checkbox" class="toggle-status" data-id="<?= $member->member_id ?>" <?= $member->status == 'Verified' ? 'checked' : '' ?>>
                                <span class="slider round"></span>
                            </label>
                        <?php endif; ?>
                    </td>
                    <td class="profilepic-column">
                        <div class="action-buttons">
                            <a href="adminMemberEdit.php?id=<?= $member->member_id ?>" class="edit-btn">Edit</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($members)): ?>
                <tr><td colspan="8">No members found</td></tr>
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
                    <li class="<?= ($i == $page) ? '' : '' ?>">
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&startDate=<?= $startDate ?>&endDate=<?= $endDate ?>&rowsPerPage=<?= $perPage ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <script src="../JS/adminMember.js"></script>
</body>
</html>
