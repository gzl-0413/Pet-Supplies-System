<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Pet Supply Store</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 90%;
            margin: auto;
            overflow: hidden;
        }

        /* Header and Footer */
        header, footer {
            background-color: #4CAF50; /* Main green color */
            color: white;
            padding: 15px 0;
            text-align: center;
            position: relative;
        }

        header h1 {
            font-family: 'Comic Sans MS', sans-serif;
            font-size: 3rem;
            display: inline-block;
            vertical-align: middle;
        }

        header .logo {
            position: absolute;
            left: 15px;
            top: 10px;
            width: 60px;
        }

        footer {
            padding: 25px 0;
        }

        /* Main Content */
        .about-section {
            background: #ffffff;
            padding: 50px 30px;
            border-radius: 15px;
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
            margin: 50px 0;
            position: relative;
            overflow: hidden;
        }

        /* Decorative Background Elements */
        .about-section::before {
            content: '';
            position: absolute;
            top: -100px;
            left: -100px;
            width: 300px;
            height: 300px;
            background: url('https://img.icons8.com/emoji/48/dog-face.png') no-repeat center;
            opacity: 0.2;
            z-index: -1;
        }

        .about-section::after {
            content: '';
            position: absolute;
            bottom: -100px;
            right: -100px;
            width: 200px;
            height: 200px;
            background: url('https://img.icons8.com/emoji/48/paw-prints.png') no-repeat center;
            opacity: 0.3;
            z-index: -1;
        }

        .about-section h1 {
            color: #4CAF50;
            text-align: center;
            font-size: 2.8rem;
            margin-bottom: 20px;
            font-family: 'Comic Sans MS', sans-serif;
        }

        .about-content {
            display: flex;
            justify-content: space-around;
            align-items: center;
            flex-wrap: wrap;
        }

        .about-image {
    width: 300px; /* Set a fixed width */
    height: 300px; /* Set a fixed height */
    border-radius: 15px;
    overflow: hidden;
}

.about-image img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Ensures the image covers the container without stretching */
    transition: transform 0.3s;
}

.about-image img:hover {
    transform: scale(1.05);
}

        .about-text {
            width: 50%;
            padding: 20px;
        }

        .about-text h2 {
            color: #4CAF50;
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-family: 'Comic Sans MS', sans-serif;
        }

        .about-text p {
            font-size: 1.2rem;
            line-height: 1.8;
            margin-bottom: 25px;
        }

        /* Pet-themed icons */
        .icon-row {
            text-align: center;
            margin: 40px 0;
        }

        .icon-row img {
            width: 70px;
            margin: 0 20px;
        }

        /* Testimonial Section */
        .testimonial {
            background-color: #e0f7e9;
            padding: 30px;
            border-radius: 15px;
            margin: 30px 0;
            text-align: center;
            position: relative;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .testimonial::before {
            content: '';
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 50px;
            background: url('https://img.icons8.com/emoji/48/puppy-face.png') no-repeat center;
            z-index: -1;
        }

        .testimonial p {
            font-style: italic;
            color: #555;
            font-size: 1.2rem;
        }

        .testimonial span {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #333;
        }

        /* Footer Links */
        .footer-content {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }

        .footer-content div {
            margin: 10px;
        }

        .footer-content a {
            color: white;
            text-decoration: none;
        }

    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <img class="logo" src="https://img.icons8.com/emoji/48/dog.png" alt="Pet Store Logo">
        <h1>Pet Supply Store</h1>
    </header>

    <!-- About Section -->
    <div class="container">
        <section class="about-section">
            <h1>About Us</h1>
            <div class="about-content">
                <div class="about-text">
                    <h2>Providing the Highest Quality Products for Pets & Their Humans</h2>
                    <p>At Pet Supply Store, we believe that pets deserve the best. Our carefully curated products ensure only the finest materials are used. From healthy food to durable toys, we’ve got everything your pet needs to thrive.</p>
                    <p>Our dedicated team shares your love for animals, which is why we provide high-quality products that are designed for both pets and their owners.</p>
                </div>
                <div class="about-image">
                    <img src="../uploads/img3.jpg" alt="Pets and Products">
                </div>
            </div>

            <div class="icon-row">
                <img src="https://img.icons8.com/?size=100&id=KPIDQ0XH7LAI&format=png&color=000000" alt="Dog Icon">
                <img src="https://img.icons8.com/?size=100&id=121371&format=png&color=000000" alt="Cat Icon">
                <img src="https://img.icons8.com/?size=100&id=3973&format=png&color=000000" alt="Bone Icon">
                <img src="https://img.icons8.com/?size=100&id=2740&format=png&color=000000" alt="Paw Icon">
            </div>

            <!-- Handmade Products Section -->
            <div class="about-content">
                <div class="about-image">
                    <img src="../uploads/img1.jpg" alt="Handmade Products">
                </div>
                <div class="about-text">
                    <h2>Handmade with the Best Materials</h2>
                    <p>We offer handmade pet products crafted from premium materials. Whether it's a cozy bed or a custom leash, our products are designed for your pet's comfort and durability. Treat your pet to something special today!</p>
                </div>
            </div>

            <!-- Testimonial Section -->
            <div class="testimonial">
                <p>"Pet Supply Store is my go-to for all my pet needs. The products are amazing and the service is always top-notch. Highly recommend!"</p>
                <span>- Sarah, Happy Customer</span>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div>
                <h3>Pet Supply Store</h3>
                <p>123 Pet Street, Pet City, PC 12345</p>
                <p>Email: contact@petsupply.com</p>
                <p>Phone: (123) 456-7890</p>
            </div>
            <div>
                <h3>Opening Hours</h3>
                <p>Mon - Fri: 9:00 AM - 7:00 PM</p>
                <p>Sat - Sun: 10:00 AM - 5:00 PM</p>
            </div>
            <div>
                <h3>Customer Service</h3>
                <p><a href="#">FAQs</a></p>
                <p><a href="#">Shipping & Returns</a></p>
                <p><a href="#">Contact Us</a></p>
            </div>
        </div>
    </footer>

</body>
</html>