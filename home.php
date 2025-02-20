<?php 
session_start(); 
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/home.css">
    <title>AutoDesk | Home</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="page-container">

        <section class="hero-section">
            <div class="hero-content">
                <h1>Explore the World of 3D Models</h1>
                <p>Discover, create, and share stunning 3D models with our powerful platform.</p>
                <div class="hero-buttons">
                    <a href="signup.php" class="btn">Get Started</a>
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
                <h2>Ready to Create Your Own 3D Models?</h2>
                <p>Join our community and start building amazing 3D projects today.</p>
                <a href="signup.php" class="btn-primary">Sign Up Now</a>
            </div>
        </section>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>
