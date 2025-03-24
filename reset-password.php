<?php
require 'db/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($token) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM password_reset_tokens 
                              WHERE token = ? AND used = 0 AND expires_at > NOW()");
        $stmt->execute([$token]);
        $tokenData = $stmt->fetch();
        
        if ($tokenData) {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $stmt->execute([$passwordHash, $tokenData['user_id']]);
                
                $stmt = $pdo->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token_id = ?");
                $stmt->execute([$tokenData['token_id']]);
                
                $pdo->commit();
                
                $success = "Password updated successfully. You can now <a href='signin.php'>sign in</a>.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Failed to update password. Please try again.";
            }
        } else {
            $error = "Invalid or expired token. Please request a new password reset link.";
        }
    }
} elseif (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $pdo->prepare("SELECT * FROM password_reset_tokens 
                          WHERE token = ? AND used = 0 AND expires_at > NOW()");
    $stmt->execute([$token]);
    $tokenData = $stmt->fetch();
    
    if (!$tokenData) {
        $error = "Invalid or expired token. Please request a new password reset link.";
    }
} else {
    header("Location: forgot-password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/signup.css">
    <title>Reset Password | AutoDesk</title>
</head>
<body>
    <?php include('include/header.php'); ?>
    
    <div class="page-container">
        <div class="signup-container">
            <div class="signup-box">
                <h2>Reset Password</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php elseif (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php else: ?>
                    <form action="reset-password.php" method="POST" id="reset-form">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="input-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" placeholder="Enter new password" minlength="8" required>
                        </div>
                        <div class="input-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                        </div>
                        <button type="submit" class="signup-btn">Reset Password</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>