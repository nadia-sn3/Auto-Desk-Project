<?php
require 'db/connection.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role_id'] = $user['role_id'];
        header("Location: project-home.php");
        exit();
    } else {
        die("Invalid email or password.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/signin.css">
    <title>AutoDesk | Sign In</title>
</head>
<body>
    <?php include('include/header.php'); ?>
    
    <div class="page-container">
        <div class="signin-container">
            <div class="signin-box">
                <h2>Sign In</h2>

                <form action="signin.php" method="POST" id="signin-form">

                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <div class="forgot-password">
                        <a href="#">Forgot your password?</a>
                    </div>
                    <button type="submit" class="signin-btn">Sign In</button>
                </form>

                <div class="signup-link">
                    <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
                </div>
            </div>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>
