<?php
require 'db/connection.php'; 
session_start(); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        die("All fields are required.");
    }

    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    if (strlen($password) < 8) {
        die("Password must be at least 8 characters long.");
    }

    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        die("Email already registered.");
    }

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $default_system_role_id = 2;
    
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password_hash, system_role_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    if ($stmt->execute([$firstName, $lastName, $email, $password_hash, $default_system_role_id])) {
        $user_id = $pdo->lastInsertId();
        
        $_SESSION['user_id'] = $user_id;
        $_SESSION['email'] = $email;
        $_SESSION['first_name'] = $firstName;
        $_SESSION['system_role_id'] = $default_system_role_id;

        header("Location: project-home.php");
        exit();
    } else {
        die("Signup failed. Please try again.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/signup.css">
    <title>Autodesk | Sign Up</title>
</head>
<body>
    <?php include('include/header.php'); ?>
    
    <div class="page-container">
        <div class="signup-container">
            <div class="signup-box">
                <h2>Sign Up</h2>
                <form action="signup.php" method="POST" id="signup-form">
                    <div class="input-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" placeholder="Enter your first name" required>
                    </div>
                    <div class="input-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Enter your last name" required>
                    </div>
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="input-group">
                        <label for="password">Password (min 8 characters)</label>
                        <input type="password" id="password" name="password" placeholder="Create a password" minlength="8" required>
                    </div>
                    <div class="input-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
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