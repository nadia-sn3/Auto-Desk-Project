<?php
require 'db/connection.php'; 
session_start();

function log_action($pdo, $user_id, $action) {
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action) VALUES (?, ?)");
    $stmt->execute([$user_id, $action]);
}

$error_message = ''; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['username'] = $user['first_name']." ". $user['last_name'];
        $_SESSION['system_role_id'] = $user['system_role_id'];

        log_action($pdo, $user['user_id'], "User logged in");

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

        $stmt = $pdo->prepare("SELECT pm.project_role_id, p.project_id, proles.role_name 
                              FROM project_members pm
                              JOIN project p ON pm.project_id = p.project_id
                              JOIN project_roles proles ON pm.project_role_id = proles.project_role_id
                              WHERE pm.user_id = ?");
        $stmt->execute([$user['user_id']]);
        $projectMemberships = $stmt->fetchAll();

        if (!empty($projectMemberships)) {
            $_SESSION['project_memberships'] = $projectMemberships;
        }

        switch ($user['system_role_id']) {
            case 1:
                header("Location: system-admin-home.php");
                break;
            case 2: 
            default:
                if (!empty($orgMemberships)) {
                    header("Location: org-dashboard.php");
                } else {
                    header("Location: project-home.php");
                }
        }
        exit();
    } else {
        $error_message = "Invalid email or password."; 
        log_action($pdo, null, "Failed login attempt for email: $email");
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
    <title>Autodesk | Sign In</title>
</head>
<body>
    <?php include('include/header.php'); ?>
    
    <div class="page-container">
        <div class="signin-container">
            <div class="signin-box">
                <h2>Sign In</h2>

                <?php if ($error_message): ?>
                    <div class="error-message">
                        <p><?php echo $error_message; ?></p>
                    </div>
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
                        <a href="forgot-password.php">Forgot your password?</a>
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