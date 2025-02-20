<?php
include 'db_connect.php'; // Include the database connection

$error_message = ""; // Variable to store signup errors
$success_message = ""; // Variable to store success messages

// Ensure Database Connection is Established
if (!$conn) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username']; 
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    // Check If the `users` Table Exists
    $table_check_sql = "SHOW TABLES LIKE 'users'";
    $table_check_result = $conn->query($table_check_sql);
    
    if ($table_check_result->num_rows == 0) {
        die("Error: The 'users' table does not exist. Please create the table in phpMyAdmin.");
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash password
        $role_id = 2; // Default role_id for normal users

        // Insert user into the database
        $sql = "INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        // Debug SQL statement
        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }

        $stmt->bind_param("sssi", $username, $email, $hashed_password, $role_id);

        if ($stmt->execute()) {
            $success_message = "Signup successful! <a href='signin.php'>Login here</a>";
        } else {
            $error_message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $conn->close();
}
?>

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

                <!-- Display success or error messages -->
                <?php if (!empty($error_message)): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>
                <?php if (!empty($success_message)): ?>
                    <p style="color: green;"><?php echo htmlspecialchars($success_message); ?></p>
                <?php endif; ?>

                <form action="signup.php" method="POST" id="signup-form">
                    <div class="input-group">
                        <label for="username">Full Name</label>
                        <input type="text" id="username" name="username" placeholder="Enter your full name" required>
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
