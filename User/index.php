<?php
include '../includes/_base.php';


// Include the header
include "header.php";

$member = null;

if (isset($_SESSION['member']) && $_SESSION['member'] !== null) {
    $member = $_SESSION['member'];
}

try {
    $stm = $_db->prepare('
        SELECT * FROM product
        ORDER BY name 
        LIMIT 10
    ');
    $stm->execute();
    $products = $stm->fetchAll();
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Store Homepage</title>
    <link rel="stylesheet" href="../CSS/index.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>
    <div class="main">
        <div class="main-contents">
            <h1>Welcome to PetStore</h1>
            <p>All You Need for Your Furry Friends</p>
            <a href="product.php" class="cta-button">Explore Products</a>
        </div>
    </div>

    <div class="testimonial-section">
        <h2>What Our Customers Say</h2>
        <div class="testimonial-container">
            <div class="testimonial-track">
                <div class="testimonial">
                    <img src="../Images/test1.jpg" alt="Customer 1">
                    <p>"The quality of pet food here is outstanding. My dog's coat has never looked better!"</p>
                    <h4>John Doe</h4>
                </div>
                <div class="testimonial">
                    <img src="../Images/test2.jpg" alt="Customer 2">
                    <p>"I love the variety of eco-friendly pet products. It's great to shop sustainably for my furry friends."</p>
                    <h4>Jane Smith</h4>
                </div>
                <div class="testimonial">
                    <img src="../Images/test3.jpg" alt="Customer 3">
                    <p>"The pet grooming services are top-notch. My cat always comes back looking and feeling great!"</p>
                    <h4>Mike Johnson</h4>
                </div>
                <div class="testimonial">
                    <img src="../Images/test4.jpg" alt="Customer 4">
                    <p>"I found the perfect accessories for my aquarium here. The fish expert's advice was invaluable!"</p>
                    <h4>Emily Brown</h4>
                </div>
                <div class="testimonial">
                    <img src="../Images/test5.jpg" alt="Customer 5">
                    <p>"The pet training classes have been a game-changer for me and my puppy. Highly recommended!"</p>
                    <h4>Sarah Wilson</h4>
                </div>
                <div class="testimonial">
                    <img src="../Images/test6.jpg" alt="Customer 6">
                    <p>"I appreciate the wide selection of organic treats. My pets love them, and I feel good about what they're eating."</p>
                    <h4>David Lee</h4>
                </div>
                <div class="testimonial">
                    <img src="../Images/test7.jpg" alt="Customer 7">
                    <p>"The staff went above and beyond to help me choose the right supplements for my aging dog. Thank you!"</p>
                    <h4>Robert Taylor</h4>
                </div>
                <div class="testimonial">
                    <img src="../Images/test8.jpg" alt="Customer 8">
                    <p>"I'm impressed by the range of durable toys. They've survived my energetic Labrador's play sessions!"</p>
                    <h4>Thomas Anderson</h4>
                </div>
                <div class="testimonial">
                    <img src="../Images/test9.jpg" alt="Customer 9">
                    <p>"The online ordering and home delivery service has been a lifesaver. Convenient and always on time!"</p>
                    <h4>Lisa Martinez</h4>
                </div>
            </div>
        </div>
    </div>
    <br><br><br><br>
    <?php $products = getTop10HotSalesProducts(); 
          $Fproducts = getTop5MostWishedProducts();    
     ?>

<div class="product-list">
    <h2>Top 10 Hot Sales Product</h2>
    <div class="product-slider-wrapper">
        <div class="product-slider" id="product-slider">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <p class="total-sold">Total Sold: <?= htmlspecialchars($product->totalSold) ?></p>
                    <img src="/uploads/<?= htmlspecialchars($product->photo) ?>" alt="<?= htmlspecialchars($product->name) ?>" width="100">
                    <p class="product-title"><?= htmlspecialchars($product->name) ?></p>
                    <p class="price">RM<?= number_format($product->oriPrice, 2) ?></p>
                    <button class="buy-now" onclick="window.location.href='productPage.php?productId=<?= htmlspecialchars($product->id) ?>'">View</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="prev" onclick="slideLeft()">&#10094;</button>
        <button class="next" onclick="slideRight()">&#10095;</button>
    </div>
</div>

<div class="product-list">
    <h2>Featured Products</h2>
    <div class="product-grid">
        <?php foreach ($Fproducts as $product): ?>
            <div class="product-card">
                <img src="/uploads/<?= htmlspecialchars($product->photo) ?>" alt="<?= htmlspecialchars($product->name) ?>" width="100">
                <p class="product-title"><?= htmlspecialchars($product->name) ?></p>
                <p class="price">RM<?= number_format($product->oriPrice, 2) ?></p>
                <button class="buy-now" onclick="window.location.href='productPage.php?productId=<?= htmlspecialchars($product->id) ?>'">Buy Now</button>
                <p class="wish-count"><?= htmlspecialchars($product->wishCount) ?> People add to Favourite</p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

    <div class="about-section">
        <p>About The Shop</p>
        <h1>Watch Our Story</h1>
        <p>There is no magic formula to write perfect ad copy. It is based on a number of factors, including ad placement, demographic, even the consumer's mood.</p>
        <button class="play-button">▶</button>
    </div>

    <div class="faq-section">
        <h2>Frequently Asked Questions</h2>
        <div class="faq-container">
            <div class="faq-item">
                <h3 class="faq-question">How do I place an order?</h3>
                <p class="faq-answer">To place an order, simply browse our products, add items to your cart, and proceed to checkout. Follow the prompts to enter your shipping and payment information.</p>
            </div>
            <div class="faq-item">
                <h3 class="faq-question">What payment methods do you accept?</h3>
                <p class="faq-answer">We accept major credit cards, PayPal, and bank transfers. All transactions are secure and encrypted.</p>
            </div>
            <div class="faq-item">
                <h3 class="faq-question">How long does shipping take?</h3>
                <p class="faq-answer">Shipping times vary depending on your location. Typically, orders are processed within 1-2 business days and delivered within 3-7 business days.</p>
            </div>
            <div class="faq-item">
                <h3 class="faq-question">Do you offer pet care advice?</h3>
                <p class="faq-answer">Yes, we provide basic pet care information with each pet purchase. For more detailed advice, we recommend consulting with a veterinarian or a professional pet care specialist.</p>
            </div>
            <div class="faq-item">
                <h3 class="faq-question">Are your pets vaccinated and health-checked?</h3>
                <p class="faq-answer">All our pets undergo thorough health checks and receive necessary vaccinations before being made available for sale. We provide detailed health records and vaccination certificates with each pet purchase.</p>
            </div>
        </div>
    </div>

    <div class="map-section">
        <h2>Find Us</h2>
        <div class="contact-map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15934.105627389297!2d101.72549101799783!3d3.218174071680438!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cc3843bfb6a031%3A0x2dc5e067aae3ab84!2sTunku%20Abdul%20Rahman%20University%20of%20Management%20and%20Technology%20(TAR%20UMT)!5e0!3m2!1sen!2smy!4v1722667429477!5m2!1sen!2smy" width="100%" height="auto" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                
        </div>
    </div>

    <script src="../JS/initializer.js"></script>
    <script>
        $(document).ready(function() {
            if ($('.faq-item')) {
                initializeFAQ();
            }
            if ($('.testimonial-track').length) {
                initializeTestimonials();
            }
        });
    </script>

</body>
<script>
// Select the necessary elements
const slider = document.querySelector('.product-slider');
const prevButton = document.querySelector('.prev');
const nextButton = document.querySelector('.next');

// Function to update button states
function updateButtons() {
    // Disable the "prev" button if at the start
    if (slider.scrollLeft <= 0) {
        prevButton.disabled = true;
        prevButton.style.opacity = 0.5; // Optional: Dim the button when disabled
    } else {
        prevButton.disabled = false;
        prevButton.style.opacity = 1;
    }

    // Disable the "next" button if at the end
    if (slider.scrollLeft + slider.offsetWidth >= slider.scrollWidth) {
        nextButton.disabled = true;
        nextButton.style.opacity = 0.5; // Optional: Dim the button when disabled
    } else {
        nextButton.disabled = false;
        nextButton.style.opacity = 1;
    }
}

// Attach event listeners for the buttons
prevButton.addEventListener('click', function() {
    slider.scrollLeft -= 250; // Adjust based on product card width + margin
    updateButtons(); // Update the button states after scrolling
});

nextButton.addEventListener('click', function() {
    slider.scrollLeft += 250; // Adjust based on product card width + margin
    updateButtons(); // Update the button states after scrolling
});

// Initialize button states on page load
updateButtons();

</script>
</html>

<?php
include 'footer.php';
?>