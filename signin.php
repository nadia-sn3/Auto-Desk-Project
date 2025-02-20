<?php
session_start(); // Start the session at the top
include 'db_connect.php'; // Include the database connection

$error_message = ""; // Variable to store login errors

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Retrieve user from the database
    $sql = "SELECT user_id, username, password, role_id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $username, $hashed_password, $role_id);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        // Store user session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role_id'] = $role_id; // Store role_id for access control

        header("Location: home.php"); // Redirect to home page
        exit();
    } else {
        $error_message = "Invalid email or password.";
    }

    $stmt->close();
    $conn->close();
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

                <?php if (!empty($error_message)): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>

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
