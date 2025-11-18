<?php
include '../includes/_base.php';
auth();

if (!isset($_SESSION['member'])) {
    redirect('login.php');
}

$member_id = $_SESSION['member'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productId'])) {
    $productId = $_POST['productId'];

    try {
        $Query = "DELETE FROM wishlist WHERE productId = ? AND userId = ?";
        $stmt = $_db->prepare($Query);
        $stmt->execute([$productId, $member_id]);

        if ($stmt->rowCount() > 0) {
            $message = "Item successfully removed from wishlist.";
        } else {
            $message = "Failed to remove item from wishlist. Please try again.";
        }

        // If this is an AJAX request, respond with a success message
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['success' => true, 'message' => $message]);
            exit;
        }
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
    }
}


// Fetch user's wishlist items after any possible removal
$stm = $_db->prepare('SELECT w.productId, p.name, p.oriPrice, p.photo 
                      FROM wishlist w 
                      JOIN product p ON w.productId = p.id 
                      WHERE w.userId = ? 
                      ORDER BY p.id ASC');
$stm->execute([$member_id]);
$wishlistItems = $stm->fetchAll(PDO::FETCH_ASSOC);
 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" >
    <title>Wishlist</title>
  
    <style>
        body {
          
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
       
        .message {
            text-align: center;
            color: green;
            font-size: 18px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #007BFF;
            color: white;
        }
        .product-image-wishlist {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .no-data {
            text-align: center;
            color: #999;
            margin-top: 20px;
        }
        .wishlist-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .remove-btn {
            padding: 5px 10px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .remove-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
<div id="wishlistContainer" class="wishlist-container">
        <h2>Your Wishlist</h2>
        
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if (count($wishlistItems) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Price (RM)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
    <?php foreach ($wishlistItems as $item): ?>
        <tr class="wishlist-item" data-product-id="<?php echo $item['productId']; ?>">
            <td>
                <!-- Make the image clickable -->
                <a href="productPage.php?productId=<?php echo htmlspecialchars($item['productId']); ?>">
                    <img src="../uploads/<?php echo htmlspecialchars($item['photo']); ?>" class="product-image-wishlist" alt="Product Image">
                </a>
            </td>
            <td>
                <!-- Make the product name clickable -->
                <a href="productPage.php?productId=<?php echo htmlspecialchars($item['productId']); ?>">
                    <?php echo htmlspecialchars($item['name']); ?>
                </a>
            </td>
            <td><?php echo htmlspecialchars(number_format($item['oriPrice'], 2)); ?></td>
            <td>
                <form method="POST" class="remove-wishlist-form" style="display: inline;">
                    <input type="hidden" name="productId" value="<?php echo $item['productId']; ?>">
                    <button type="button" class="remove-btn">Remove</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

            </table>
        <?php else: ?>
            <p class="no-data">Your wishlist is currently empty.</p>
        <?php endif; ?>
    </div>
</body>

 

</html>
