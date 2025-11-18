<?php 

require_once '../includes/_base.php';
include 'adminHeader.php';
include 'sideNav.php'; 


$search = req('search');
$startDate = req('startDate');
$endDate = req('endDate');
$page = max(1, (int)req('page', 1));
$perPage = req('rowsPerPage', 5);
$start = ($page - 1) * $perPage;

try {
    $query = '
        SELECT r.rating_id, r.comment, r.rate, r.reply, r.date_time, p.name, r.member_id, r.photos
        FROM review r
        JOIN product p ON r.product_id = p.id
        WHERE 1';
    
    if ($search) {
        $query .= ' AND (r.comment LIKE :search OR p.name LIKE :search)';
    }
    
    if ($startDate && $endDate) {
        $query .= ' AND (r.date_time BETWEEN :startDate AND :endDate)';
    }

    $sort = req('sort') ?? 'r.rating_id';
    $order = req('order') ?? 'asc';

    $allowedSorts = ['r.rating_id', 'r.comment', 'r.rate', 'r.reply', 'r.date_time', 'p.name', 'r.member_id'];
    $allowedOrders = ['asc', 'desc'];

    if (!in_array($sort, $allowedSorts)) {
        $sort = 'r.rating_id';
    }
    if (!in_array($order, $allowedOrders)) {
        $order = 'asc';
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
    
    $reviews = $stm->fetchAll(PDO::FETCH_ASSOC);

    $totalQuery = '
        SELECT COUNT(*)
        FROM review r
        JOIN product p ON r.product_id = p.id
        WHERE 1';
    
    if ($search) {
        $totalQuery .= ' AND (r.comment LIKE :search OR p.name LIKE :search)';
    }
    if ($startDate && $endDate) {
        $totalQuery .= ' AND (r.date_time BETWEEN :startDate AND :endDate)';
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
    <title>Admin Review Management</title>
    <link rel="stylesheet" href="../css/adminMember.css">
    <link rel="stylesheet" href="../css/adminReview.css">
</head>
<body>
    <div class="container">
        <h1>Review Management</h1>
        <div class="search-container">
            <form method="get" action="">
                <label for="searchInput">Search:</label>
                <input type="text" id="searchInput" name="search" placeholder="Comment or Product Name..." value="<?= htmlspecialchars($search) ?>" style="width: 250px;">
                <label for="startDate">Start Date:</label>
                <input type="date" id="startDate" name="startDate" value="<?= htmlspecialchars($startDate) ?>">
                <label for="endDate">End Date:</label>
                <input type="date" id="endDate" name="endDate" value="<?= htmlspecialchars($endDate) ?>">
                <button type="submit" class="edit-btn" id="searchButton">Search</button>
            </form>
        </div>
        <table id="reviewTable">
            <thead>
                <tr>
                    <th>
                        <a href="?search=<?= urlencode($search) ?>&startDate=<?= urlencode($startDate) ?>&endDate=<?= urlencode($endDate) ?>&rowsPerPage=<?= $perPage ?>&sort=r.rating_id&order=<?= ($sort === 'r.rating_id' && $order === 'asc') ? 'desc' : 'asc' ?>" class="sort-link">
                            ID <?= ($sort === 'r.rating_id') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?search=<?= urlencode($search) ?>&startDate=<?= urlencode($startDate) ?>&endDate=<?= urlencode($endDate) ?>&rowsPerPage=<?= $perPage ?>&sort=r.comment&order=<?= ($sort === 'r.comment' && $order === 'asc') ? 'desc' : 'asc' ?>" class="sort-link">
                            Comment <?= ($sort === 'r.comment') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?search=<?= urlencode($search) ?>&startDate=<?= urlencode($startDate) ?>&endDate=<?= urlencode($endDate) ?>&rowsPerPage=<?= $perPage ?>&sort=r.rate&order=<?= ($sort === 'r.rate' && $order === 'asc') ? 'desc' : 'asc' ?>" class="sort-link">
                            Rating <?= ($sort === 'r.rate') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?>
                        </a>
                    </th>
                    <th>Photos</th>
                    <th>
                        <a href="?search=<?= urlencode($search) ?>&startDate=<?= urlencode($startDate) ?>&endDate=<?= urlencode($endDate) ?>&rowsPerPage=<?= $perPage ?>&sort=r.reply&order=<?= ($sort === 'r.reply' && $order === 'asc') ? 'desc' : 'asc' ?>" class="sort-link">
                            Reply <?= ($sort === 'r.reply') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?search=<?= urlencode($search) ?>&startDate=<?= urlencode($startDate) ?>&endDate=<?= urlencode($endDate) ?>&rowsPerPage=<?= $perPage ?>&sort=r.date_time&order=<?= ($sort === 'r.date_time' && $order === 'asc') ? 'desc' : 'asc' ?>" class="sort-link">
                            Date and Time <?= ($sort === 'r.date_time') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?search=<?= urlencode($search) ?>&startDate=<?= urlencode($startDate) ?>&endDate=<?= urlencode($endDate) ?>&rowsPerPage=<?= $perPage ?>&sort=p.name&order=<?= ($sort === 'p.name' && $order === 'asc') ? 'desc' : 'asc' ?>" class="sort-link">
                            Product Name <?= ($sort === 'p.name') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?search=<?= urlencode($search) ?>&startDate=<?= urlencode($startDate) ?>&endDate=<?= urlencode($endDate) ?>&rowsPerPage=<?= $perPage ?>&sort=r.member_id&order=<?= ($sort === 'r.member_id' && $order === 'asc') ? 'desc' : 'asc' ?>" class="sort-link">
                            Member ID <?= ($sort === 'r.member_id') ? (($order === 'asc') ? '&#9650;' : '&#9660;') : '' ?>
                        </a>
                    </th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $review): ?>
                <tr class="table-row">
                    <td><?= htmlspecialchars($review['rating_id']) ?></td>
                    <td><?= htmlspecialchars($review['comment']) ?></td>
                    <td><?= htmlspecialchars($review['rate']) ?></td>
                    <td style="width: 200px;">
                        <?php if (!empty($review['photos'])): ?>
                            <?php $photos = explode(',', $review['photos']); ?>
                            <div class="photo-slider">
                                <div id="slider-container-<?= htmlspecialchars($review['rating_id']) ?>" class="slider-container">
                                    <?php foreach ($photos as $photo): ?>
                                        <img src="../uploads/<?= htmlspecialchars($photo) ?>" alt="Review Photo" class="review-photo" onclick="openModal('../uploads/<?= htmlspecialchars($photo) ?>')">
                                    <?php endforeach; ?>
                                </div>
                                <button class="prev" onclick="changeSlide(-1, '<?= htmlspecialchars($review['rating_id']) ?>')">&#10094;</button>
                                <button class="next" onclick="changeSlide(1, '<?= htmlspecialchars($review['rating_id']) ?>')">&#10095;</button>
                            </div>
                        <?php else: ?>
                            No Photos
                        <?php endif; ?>
                    </td>
                    <div id="photoModal" class="pmodal">
                        <span class="pclose" onclick="closeModal()">&times;</span>
                        <img class="pmodal-content" id="modalImage">
                    </div>
                    <td><?= htmlspecialchars($review['reply']) ?></td>
                    <td><?= htmlspecialchars($review['date_time']) ?></td>
                    <td><?= htmlspecialchars($review['name']) ?></td>
                    <td><?= htmlspecialchars($review['member_id']) ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="edit-btn" data-id="<?= htmlspecialchars($review['rating_id']) ?>">Reply</button>
                            <a href="#" class="delete-btn" onclick="confirmDelete('adminReviewDelete.php?id=<?= htmlspecialchars($review['rating_id']) ?>')">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($reviews)): ?>
                <tr><td colspan="9">No reviews found</td></tr>
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
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&startDate=<?= $startDate ?>&endDate=<?= $endDate ?>&rowsPerPage=<?= $perPage ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <div id="replyModal" class="rmodal">
        <div class="rmodal-content">
            <span class="rclose">&times;</span>
            <h2>Reply to Review</h2>
            <form id="replyForm" method="post" action="adminReviewReply.php">
                <input type="hidden" id="reviewId" name="reviewId">
                <p>Comment: <br><span id="commentText"></span></p>
                <textarea id="replyMessage" name="replyMessage" rows="4" cols="50" placeholder="Type your reply here..." class="reply-message"></textarea>
                <br>
                <button type="submit" class="send-reply-btn">Send Reply</button>
            </form>
        </div>
    </div>

    <script src="../JS/adminReview.js"></script>
</body>
</html>
