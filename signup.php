<?php
require 'db/connection.php';
session_start();

// Redirect logged-in users based on their role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['system_role_id'] == 1) {
        header("Location: system-admin-home.php");
    } elseif (isset($_SESSION['org_memberships']) && !empty($_SESSION['org_memberships'])) {
        header("Location: org-dashboard.php");
    } else {
        header("Location: project-home.php");
    }
    exit();
}

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

function validate_password($password) {
    if (strlen($password) < 8) return "Password must be at least 8 characters";
    if (!preg_match('/[A-Z]/', $password)) return "Password needs at least one uppercase letter";
    if (!preg_match('/[a-z]/', $password)) return "Password needs at least one lowercase letter";
    if (!preg_match('/[0-9]/', $password)) return "Password needs at least one number";
    return true;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security error. Please refresh the page.");
    }

    $firstName = htmlspecialchars(trim($_POST['first_name']));
    $lastName = htmlspecialchars(trim($_POST['last_name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($firstName)) $errors[] = "First name is required";
    if (empty($lastName)) $errors[] = "Last name is required";
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already registered";
        }
    }

    $passwordCheck = validate_password($password);
    if ($passwordCheck !== true) {
        $errors[] = $passwordCheck;
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords don't match";
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $default_role = 2; 
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password_hash, system_role_id, created_at) 
                                  VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$firstName, $lastName, $email, $password_hash, $default_role]);
            
            $user_id = $pdo->lastInsertId();
            
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;
            $_SESSION['username'] = "$firstName $lastName";
            $_SESSION['system_role_id'] = $default_role;
            $_SESSION['login_time'] = time();
            
            $pdo->commit();
            
            header("Location: project-home.php");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Registration failed. Please try again.";
        }
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/signup.css">
</head>
<body>
    <?php include('include/header.php'); ?>
    
    <div class="page-container">
        <div class="signup-container">
            <div class="signup-box">
                <h2>Sign Up</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <?php foreach ($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form action="signup.php" method="POST" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    
                    <div class="input-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required
                               value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : '' ?>">
                    </div>
                    
                    <div class="input-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required
                               value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : '' ?>">
                    </div>
                    
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                    
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <small>Minimum 8 characters with uppercase, lowercase, and number</small>
                    </div>
                    
                    <div class="input-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="signup-btn">Sign Up</button>
                </form>
                
                <div class="signin-link">
                    <p>Already have an account? <a href="signin.php">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>