<?php
require_once '../includes/_base.php';
auth();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default response
$response = ['success' => false, 'message' => 'Action failed.'];

// Check if user is logged in
if (isset($_SESSION['member'])) {
    $member = $_SESSION['member'];

    // Check if required GET data is set
    if (!empty($_GET['productId']) && !empty($_GET['action'])) {
        $productId = htmlspecialchars($_GET['productId'], ENT_QUOTES, 'UTF-8');
        $action = htmlspecialchars($_GET['action'], ENT_QUOTES, 'UTF-8');

        if ($action === 'favorite') {
            // Add to wishlist
            $wId = 'w' . bin2hex(random_bytes(3)); // Secure unique ID
            $sql = "INSERT INTO wishlist (id, userId, productId) VALUES (?, ?, ?)";
            $stmt = $_db->prepare($sql);
            $stmt->execute([$wId, $member, $productId]);
            if ($stmt) {
                $response['success'] = true;
                $response['message'] = 'Added to wishlist!';
            } else {
                $response['message'] = 'Failed to add to wishlist.';
            }

        } elseif ($action === 'unfavorite') {
            // Remove from wishlist
            $sql = "DELETE FROM wishlist WHERE userId = ? AND productId = ?";
            $stmt = $_db->prepare($sql);

            if ($stmt->execute([$member, $productId])) {
                $response['success'] = true;
                $response['message'] = 'Removed from wishlist!';
            } else {
                $response['message'] = 'Failed to remove from wishlist.';
            }
        } else {
            $response['message'] = 'Invalid action.';
        }
    } else {
        $response['message'] = 'Missing required data.';
    }
} else {    
    $response['message'] = 'User not logged in.';
}

if ($response['success']) {
    header("Location: productPage.php?productId=$productId&message=" . urlencode($response['message']));
    exit;
} else {
    echo $response['message']; // Or handle the error display as needed
}
?>