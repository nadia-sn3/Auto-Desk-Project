<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/signup.css">
    <title>AutoDesk | Sign Up</title>
</head>
<body>
    <?php include('include/header.php'); ?>
    <div class="page-container">
        <div class="signup-container">
            <div class="signup-box">
                <h2>Sign Up</h2>
                <form action="signup-process.php" method="POST" id="signup-form">
                    <div class="input-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                    </div>
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Create a password" required>
                    </div>
                    <div class="input-group">
                        <label for="confirm-password">Confirm Password</label>
                        <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password" required>
                    </div>
                    <button type="submit" class="signup-btn">Sign Up</button>
                </form>
                <div class="signin-link">
                    <p>Already have an account? <a href="signin.php">Sign in here</a></p>
                </div>
            </div>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>
