<?php
require 'db/connection.php';
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['system_role_id'] == 1) {
        header("Location: system-admin-home.php");
    } elseif (!empty($_SESSION['org_memberships'])) {
        header("Location: org-dashboard.php");
    } else {
        header("Location: project-home.php");
    }
    exit();
}

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

function log_action($pdo, $user_id, $action) {
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action) VALUES (?, ?)");
    $stmt->execute([$user_id, $action]);
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security error. Please refresh the page.");
    }

    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }
    if ($_SESSION['login_attempts'] > 5) {
        die("Too many attempts. Try again later.");
    }

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {

            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = htmlspecialchars($user['first_name']." ".$user['last_name']);
            $_SESSION['system_role_id'] = $user['system_role_id'];
            $_SESSION['login_time'] = time();

            log_action($pdo, $user['user_id'], "User logged in");

            // Get organization memberships
            $stmt = $pdo->prepare("SELECT om.org_role_id, o.org_id, oroles.role_name 
                                  FROM organisation_members om
                                  JOIN organisations o ON om.org_id = o.org_id
                                  JOIN organisation_roles oroles ON om.org_role_id = oroles.org_role_id
                                  WHERE om.user_id = ?");
            $stmt->execute([$user['user_id']]);
            $orgMemberships = $stmt->fetchAll();

            if (!empty($orgMemberships)) {
                $_SESSION['org_memberships'] = $orgMemberships;
                $_SESSION['current_org_id'] = $orgMemberships[0]['org_id'];
                $_SESSION['current_org_role_id'] = $orgMemberships[0]['org_role_id'];
                $_SESSION['current_org_role_name'] = $orgMemberships[0]['role_name'];
            }

            // Redirect based on role
            switch ($user['system_role_id']) {
                case 1:
                    header("Location: system-admin-home.php");
                    break;
                case 2:
                default:
                    header("Location: ".(!empty($orgMemberships) ? "org-dashboard.php" : "project-home.php"));
            }
            exit();
        } else {
            $_SESSION['login_attempts']++;
            $error_message = "Invalid email or password";
            log_action($pdo, null, "Failed login attempt");
        }
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/signin.css">
</head>
<body>
    <?php include('include/header.php'); ?>
    
    <div class="page-container">
        <div class="signin-container">
            <div class="signin-box">
                <h2>Sign In</h2>

                <?php if ($error_message): ?>
                    <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>

                <form action="signin.php" method="POST" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required 
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                    
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="forgot-password">
                        <a href="forgot-password.php">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="signin-btn">Sign In</button>
                </form>

                <div class="signup-link">
                    <p>Don't have an account? <a href="signup.php">Sign up</a></p>
                </div>
            </div>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>