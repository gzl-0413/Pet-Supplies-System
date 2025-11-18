<?php
require_once '../includes/_base.php';

$member = null;


if (isset($_SESSION['member']) && $_SESSION['member'] !== null) {
    $member = $_SESSION['member'];
}

$message = req('message');

$productId = req('productId');
$productHiddenStatus  = getProductById($productId);
 


function getProductById($productId) {
    global $_db;

  
    $stmt = $_db->prepare("SELECT hidden FROM product WHERE id = ?");
    $stmt->execute([$productId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
   
 
    return $result ? (int)$result['hidden'] : 0;
}


if ($productHiddenStatus === 1) {
    echo "<script>
        alert('This product is currently unavailable. Redirecting back to the product page.');
        window.location.href = 'product.php'; 
    </script>";
    exit;
}


if (!empty($message)) {
  echo "<div id='popup-message' class='popup-message'>
    <p>" . urldecode($message) . "</p>
  </div>";
  ?>

  <script>
    let timer = null;
    const popupMessage = document.getElementById('popup-message');
    const popupMessageP = popupMessage.querySelector('p');
    const radius = 20;

    function closePopup() {
      popupMessage.classList.remove('show');
      popupMessage.style.display = 'none';  
      clearTimeout(timer);  

      
      const url = new URL(window.location);
      url.searchParams.delete('message');
      window.history.replaceState({}, document.title, url);
    }

    function timerFunction() {
      closePopup();
    }

    popupMessage.addEventListener('click', (e) => {
      if (e.target === popupMessage || e.target === popupMessageP) {
        closePopup();
      }
    });

    timer = setTimeout(closePopup, 1000);  
    window.addEventListener('scroll', () => {
      const rect = popupMessage.getBoundingClientRect();
      if (rect.top < 0 || rect.bottom > window.innerHeight) {
        closePopup();
      }
    });

    window.addEventListener('resize', () => {
      const rect = popupMessage.getBoundingClientRect();
      if (rect.top < 0 || rect.bottom > window.innerHeight) {
        closePopup();
      }
    });

    function togglePopup() {
      popupMessage.style.display = 'block';  
      popupMessage.classList.add('show');
    }

    togglePopup(); 

    setTimeout(() => {
      popupMessage.style.width = `calc(100% - ${radius * 2}px)`;
      popupMessage.style.padding = `${radius}px`;
      popupMessage.style.borderRadius = `${radius}px`;
    }, 10);
  </script>

  <?php
}

$sql = "SELECT * FROM product WHERE id =?";
$stmt = $_db->prepare($sql);  
$stmt->execute([$productId]);
$product = $stmt->fetch();


$_err = false;
if (!$product) {
    echo "<script>
        alert('No Such Product Exists. Redirecting back to the product page.');
        window.location.href = 'product.php'; 
    </script>";
    exit;
}

$sql = "SELECT id FROM wishlist WHERE userId = ? AND productId = ?";
$stmt = $_db->prepare($sql);
$stmt->execute([$member, $productId]);

 
$wishlist = $stmt->fetch();
$isInWishlist = $wishlist ? true : false;


if (is_post()) {
    if (checkLogin()) {

    $action = req('action');
    $productId = req('product_id');
    $quantity = req('quantity');
    $sessionId = session_id();

    if ($action == 'add_to_cart') {
         
        $stmt = $_db->prepare('SELECT * FROM temp_cart WHERE member_Id = ? AND product_id = ?');
        $stmt->execute([$member, $productId]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cartItem) {
         
            $stmt = $_db->prepare('UPDATE temp_cart SET quantity = quantity + ? WHERE member_Id = ? AND product_id = ?');
            $stmt->execute([$quantity,$member, $productId]);
            temp('info', 'Quantity updated for existing product in cart.');
        } else {
           
            $stmt = $_db->prepare('INSERT INTO temp_cart (session_id,member_Id,product_id, quantity) VALUES (?, ? , ?, ?)');
            $stmt->execute([$sessionId,$member, $productId, $quantity]);
            temp('info', 'New product added to cart.');
        }
        echo "<script>alert('Successfully added to cart!');
          window.location.href = 'product.php';
        </script>";
        
        exit;

    } elseif ($action == 'buy_now') {
 
        echo "<script>
                alert('Proceeding to checkout!');
                window.location.href = 'checkout.php?product_id={$productId}&quantity={$quantity}';
              </script>";
        exit;
    }
}else {
    $productId = req('product_id');
    $quantity = req('quantity');
    $sessionId = session_id();

    $stmt = $_db->prepare('SELECT * FROM temp_cart WHERE session_id = ? AND product_id = ?');
        $stmt->execute([$sessionId, $productId]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($cartItem) {
         
        $stmt = $_db->prepare('UPDATE temp_cart SET quantity = quantity + ? WHERE session_id = ? AND product_id = ?');
        $stmt->execute([$quantity,$sessionId, $productId]);
        temp('info', 'Quantity updated for existing product in cart.');
    }else{
        
    $stmt = $_db->prepare('INSERT INTO temp_cart (session_id,product_id, quantity) VALUES (? , ?, ?)');
    $stmt->execute([$sessionId, $productId, $quantity]);
    temp('info', 'New product added to cart.');
    }
}
}


try {
    // Total reviews
    $sqlTotal = "SELECT COUNT(*) AS total_reviews, AVG(r.rate) AS avg_rating FROM review r WHERE r.product_id = :productId";
    $stmtTotal = $_db->prepare($sqlTotal);
    $stmtTotal->bindParam(':productId', $productId);
    $stmtTotal->execute();
    $totalData = $stmtTotal->fetch(PDO::FETCH_ASSOC);
    
    $totalReviews = $totalData['total_reviews'];
    $raverageRating = number_format($totalData['avg_rating'], 1);

    // Rating breakdown
    $sqlBreakdown = "SELECT rate, COUNT(rate) AS count FROM review WHERE product_id = :productId GROUP BY rate";
    $stmtBreakdown = $_db->prepare($sqlBreakdown);
    $stmtBreakdown->bindParam(':productId', $productId);
    $stmtBreakdown->execute();
    $breakdown = $stmtBreakdown->fetchAll(PDO::FETCH_KEY_PAIR);

     
    for ($i = 1; $i <= 5; $i++) {
        if (!isset($breakdown[$i])) {
            $breakdown[$i] = 0;
        }
    }
} catch (Exception $e) {
    // Error handling
    echo "Error: " . htmlspecialchars($e->getMessage());
}
include 'header.php';

?>
 <head><title>Product Details Page</title></head>
<body>
    <div class="product-container">
       
        <div class="product-image">
            <img class="image-large" src="/uploads/<?php echo htmlspecialchars($product->photo); ?>" width="100" alt="<?php echo htmlspecialchars($product->name); ?>">
            <div class="share-favorite-section">
                <p>Share:</p>
                <a href="https://www.facebook.com/"><img src="../uploads/facebook.webp" alt="Facebook" class="social-icon"></a>
                <a href="https://x.com/?lang=en-my"><img src="../uploads/twitter.jpg" alt="Twitter" class="social-icon"></a>
                <a href="https://www.xiaohongshu.com/explore?language=en-US"><img src="../uploads/xhs.jpg" alt="XHS" class="social-icon"></a>
                
                 <br>
                 <button id="wishlistButton_<?= htmlspecialchars($productId); ?>" class="favorite-icon" 
    onclick="window.location.href='wishlist_action.php?productId=<?= htmlspecialchars($productId); ?>&action=<?= $isInWishlist ? 'unfavorite' : 'favorite'; ?>';"
    style="color: <?= $isInWishlist ? 'red' : 'black'; ?>; cursor: pointer; background: none;margin-left:5px ;margin-top:10px ;border: none; font-size: 22px;">
    ❤  
</button>
<?php
  $productId = req('productId'); 
$wishlistSql = "SELECT COUNT(*) AS wishlistCount FROM wishlist WHERE productId = :productId";
$wishlistStmt = $_db->prepare($wishlistSql);
$wishlistStmt->bindParam(':productId', $productId);
$wishlistStmt->execute();

$wishlistCount = $wishlistStmt->fetchColumn();
?>
<p><span>Favorite (<?php echo number_format($wishlistCount); ?>)</span></p>
        </div>
        </div>
        
        <div class="product-details">
            <h1><?php echo htmlspecialchars($product->name); ?></h1>
            <div class="price-display">
                <span class="original-price">Price: RM<?php echo number_format($product->oriPrice, 2); ?></span>
            </div>
            <?php
                $productId = req('productId');
                try {
                    
                    $sql = "SELECT rate FROM review WHERE product_id = :productId";
                    $stmt = $_db->prepare($sql);
                    $stmt->bindParam(':productId', $productId);
                    $stmt->execute();

                    $ratings = $stmt->fetchAll(PDO::FETCH_COLUMN);  

                    $totalReviews = count($ratings);
                    $averageRating = 0;

                   
                    if ($totalReviews > 0) {
                        $sumRatings = array_sum($ratings);
                        $averageRating = $sumRatings / $totalReviews;
                    }

                   
                    ?>
                    <p>Rating: 
                        <?php 
                    
                        $fullStars = floor($averageRating);
                        $halfStars = ($averageRating - $fullStars >= 0.5) ? 1 : 0;
                        $emptyStars = 5 - ($fullStars + $halfStars);

                        echo str_repeat('&#9733;', $fullStars);  
                        echo str_repeat('&#9734;', $halfStars);  
                        echo str_repeat('&#9734;', $emptyStars);  
                        ?>
                        (<?php echo $totalReviews; ?> reviews)
                    </p>
                   
                    <?php
                      $soldSql = "SELECT SUM(quantity) AS totalSold FROM orders WHERE productId = :productId";
                      $soldStmt = $_db->prepare($soldSql);
                      $soldStmt->bindParam(':productId', $productId);
                      $soldStmt->execute();
          
                      $soldResult = $soldStmt->fetch(PDO::FETCH_ASSOC);
                      $totalSold = $soldResult['totalSold'] ?? 0; 
          
                      ?>
                      <p>Sold: <?php echo htmlspecialchars($totalSold); ?> units</p>
                      <?php
                } catch (Exception $e) {
                    echo "<div class='error-message'>Error fetching ratings: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            ?>


            <br><br>
            
            <br><br>

            <form method="post" class="form">
    
    <div class="product-quantity">
    <label for="quantity">Quantity:</label>
    <?php if ($product->quantity > 0): ?>
        <?= html_number('quantity', 1, htmlspecialchars($product->quantity), 1, 1, ['id' => 'quantity']) ?>
        <span id="stock-message" style="color: red; display: none;">Reached the stock limit</span>
        <?= err('quantity') ?>
    <?php else: ?>
        <p style="color: red;">Out of Stock</p>
    <?php endif; ?>
    </div>
    
    <br><br>
  
    <div class="div-class">
    <?= html_text('product_id', 'hidden', htmlspecialchars($product->id)) ?>

<input type="hidden" name="action" id="action" value="">
<button class="add-to-cart-button" type="submit" 
    <?php if ($product->quantity <= 0): ?> 
       
        onclick="showOutOfStockMessage(event);"
    <?php else: ?> 
        onclick="document.getElementById('action').value = 'add_to_cart';"
    <?php endif; ?>>
    Add to Cart
</button>

<button class="buy-now-button" type="submit" 
    <?php if ($product->quantity <= 0): ?> 
       
        onclick="showOutOfStockMessage(event);"
    <?php else: ?> 
        onclick="document.getElementById('action').value = 'buy_now';"
    <?php endif; ?>>
    Buy Now
</button>
</form>

            </div>      
           
            <br>
            <br>
           
            <div class="additional-info">
                <span>15 Days Return </span>
                <span>Everyday Low Prices</span>
                <span>Fulfilled by GuangRong</span>
            </div>
        </div>
    </div>
   
    <div class="product-description">
        <div class="product-description-header">
            <h2>Product Description</h2>
        </div>
        <br>
        
        <p class="product-description-text"><a href="#" class="strike"><?php echo htmlspecialchars($product->description); ?>  </a> </p>
       </div>
   
<!-- Product Review Container -->
<div class="product-review-container">

<div class="product-description-header">
    <?php 
    $productId = htmlspecialchars($_GET['productId'] ?? ''); 
    $rating = isset($_GET['rating']) ? $_GET['rating'] : 'all'; 
    ?>

    <div id="reviewsContainer">
    <?php
        try {
            $sqlTotalReviews = "SELECT COUNT(*) as total FROM review WHERE product_id = :productId";
            $stmtTotal = $_db->prepare($sqlTotalReviews);
            $stmtTotal->bindParam(':productId', $productId);
            $stmtTotal->execute();
            $totalReviewsResult = $stmtTotal->fetch(PDO::FETCH_ASSOC);
            $totalReviews = $totalReviewsResult['total'];
            
            $sql = "SELECT r.*, m.username, m.profilepic FROM review r
                    JOIN member m ON r.member_id = m.member_id
                    WHERE r.product_id = :productId";

            if ($rating !== 'all') {
                $sql .= " AND r.rate = :rating";
            }

            $sql .= " ORDER BY r.date_time DESC";
            
            $stmt = $_db->prepare($sql);
            $stmt->bindParam(':productId', $productId);

            if ($rating !== 'all') {
                $stmt->bindParam(':rating', $rating);
            }

            $stmt->execute();
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filteredReviewsCount = count($reviews);

            $breakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
            $sqlBreakdown = "SELECT rate, COUNT(*) as count FROM review WHERE product_id = :productId GROUP BY rate";
            $stmtBreakdown = $_db->prepare($sqlBreakdown);
            $stmtBreakdown->bindParam(':productId', $productId);
            $stmtBreakdown->execute();
            $ratingResults = $stmtBreakdown->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ratingResults as $row) {
                $breakdown[$row['rate']] = $row['count'];
            }
            ?>

            <h2>Product Reviews (<?php echo $totalReviews; ?>)</h2>
            <div class="product-rating-summary">
                <div class="average-rating">
                    <span class="rating-score" style="background-color: <?php echo $raverageRating > 3 ? '#78d110' : '#d11010'; ?>;">
                        <?php echo $raverageRating; ?>
                    </span>
                    <span class="total-reviews">(<?php echo $totalReviews; ?> reviews)</span>
                </div>

                <div class="rating-breakdown">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <div class="rating-bar">
                            <span class="rating-star"><?php echo $i; ?> ★</span>
                            <div class="bar-container">
                                <div class="bar" style="width: <?php echo $totalReviews > 0 ? ($breakdown[$i] / $totalReviews) * 100 : 0; ?>%;"></div>
                            </div>
                            <span class="rating-count"><?php echo $breakdown[$i]; ?></span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="review-filter">
                <label for="ratingFilter">Filter by Rating:</label>
                <select id="ratingFilter">
                    <option value="all" <?php echo $rating == 'all' ? 'selected' : ''; ?>>All Ratings</option>
                    <option value="5" <?php echo $rating == '5' ? 'selected' : ''; ?>>5 Stars</option>
                    <option value="4" <?php echo $rating == '4' ? 'selected' : ''; ?>>4 Stars</option>
                    <option value="3" <?php echo $rating == '3' ? 'selected' : ''; ?>>3 Stars</option>
                    <option value="2" <?php echo $rating == '2' ? 'selected' : ''; ?>>2 Stars</option>
                    <option value="1" <?php echo $rating == '1' ? 'selected' : ''; ?>>1 Star</option>
                </select>
            </div>

            <?php if ($filteredReviewsCount > 0) {
                foreach ($reviews as $review) { ?>
                    <div class="review">
                        <div class="reviewer-info">
                            <img src="../uploads/<?php echo htmlspecialchars($review['profilepic']); ?>" alt="User Photo" class="user-photo">
                            <strong><?php echo htmlspecialchars($review['username']); ?></strong>
                        </div>
                        <div class="rating">
                            <?php echo str_repeat('&#9733;', $review['rate']) . str_repeat('&#9734;', 5 - $review['rate']); ?>
                        </div>
                        <p><?php echo htmlspecialchars($review['comment']); ?></p>

                        <?php if (!empty($review['photos'])): ?>
                            <div class="review-photos">
                                <?php 
                                $photosArray = explode(',', $review['photos']);
                                foreach ($photosArray as $photo): ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($photo); ?>" alt="Review Photo" class="review-photo">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <p><em><?php echo htmlspecialchars($review['date_time']); ?></em></p>

                        <?php if (!empty($review['reply'])): ?>
                            <hr class="reply-separator">
                            <p><strong>Reply:</strong> <?php echo htmlspecialchars($review['reply']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php }
            } else { ?>
                <div class="no-reviews" style="color:white;"><h3>No reviews available for this rating.</h3></div>
            <?php } ?>

        <?php
        } catch (Exception $e) {
            echo '<div class="error-message"><h3>Error fetching reviews: ' . htmlspecialchars($e->getMessage()) . '</h3></div>';
        }
        ?>
    </div>
</div>
</div>

<script src="../JS/productPage.js"></script>
<script>
var productId = <?php echo json_encode($productId); ?>;
var rating = <?php echo json_encode($rating); ?>;
</script>

<div id="outOfStockPopups" class="popups-overlay">
    <div class="popups-message">
        <p>This product is out of stock and cannot be added to the cart or purchased.</p>
        <button class="popups-button" onclick="closeOutOfStockMessage()">OK</button>
    </div>
</div>
</body>

<script>
    function showOutOfStockMessage(event) {
        event.preventDefault(); // Prevent the form from submitting
        document.getElementById('outOfStockPopups').style.display = 'flex';
    }

    function closeOutOfStockMessage() {
        document.getElementById('outOfStockPopups').style.display = 'none';
    }
</script>
<script>
   
    function addToCart(productId) {
    var quantity = document.getElementById('quantity').value;
    alert('Product ' + productId + ' with quantity ' + quantity + ' added to cart!');
        }


    document.getElementById('quantity').addEventListener('input', function() {
    document.getElementById('cart-quantity').value = this.value;
    });


 
        function buyNow(productId) {
     
    var quantity = document.getElementById('quantity').value;
    
    
    var form = document.createElement('form');
    form.method = 'post';  
    form.action = 'checkout.php';  
    
     
    var inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'product_id';
    inputId.value = productId;
    
    
    var inputQuantity = document.createElement('input');
    inputQuantity.type = 'hidden';
    inputQuantity.name = 'quantity';
    inputQuantity.value = quantity;
    
    
    form.appendChild(inputId);
    form.appendChild(inputQuantity);
    
    
    document.body.appendChild(form);
    form.submit();
}
 
    </script>
    <script>

document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.querySelector('input[name="quantity"]');
    const stockMessage = document.getElementById('stock-message');
    const maxStock = <?= htmlspecialchars($product->quantity) ?>;

    if (quantityInput) {
        quantityInput.addEventListener('input', function() {
            if (parseInt(this.value) >= maxStock) {
                stockMessage.style.display = 'inline'; 
            } else {
                stockMessage.style.display = 'none'; 
            }
        });
    }
});
</script>

<?php
 
include 'footer.php';
?>
