<?php 
session_start(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/home.css">
    <title>Autodesk | Home</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="page-container">

        <section class="hero-section">
            <div class="hero-content">
                <h1>Explore the World of 3D Models</h1>
                <p>Discover, create, and share stunning 3D models with our powerful platform.</p>
                <div class="hero-buttons">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="create-project.php" class="btn">Get Started</a>
                    <?php else: ?>
                        <a href="signup.php" class="btn">Get Started</a>
                    <?php endif; ?>
                    <a href="#" class="btn">Learn More</a>
                </div>
            </div>
        </section>

        <section class="featured-models">
            <h2>Featured Models</h2>
            <div class="model-grid">
                <?php include('preview.php'); ?>
                <?php include('preview.php'); ?>
                <?php include('preview.php'); ?>
            </div>
        </section>

        <section class="cta-section">
            <div class="cta-content">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <h2>Time to Start Crafting Your 3D Creations!</h2>
                    <p>Start building amazing 3D projects today with our tools and community.</p>
                    <a href="create-project.php" class="cta-btn">Create Project</a>
                <?php else: ?>
                    <h2>Ready to Create Your Own 3D Models?</h2>
                    <p>Join our community and start building amazing 3D projects today.</p>
                    <a href="signup.php" class="cta-btn">Sign Up Now</a>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>
