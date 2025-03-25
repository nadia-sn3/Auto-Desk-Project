<?php
require 'db/connection.php'; 

$error_message = ''; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name']; 
        $_SESSION['first_name'] = $user['first_name'];

        $stmt = $pdo->prepare("SELECT om.role_id, o.org_id 
                              FROM organisation_members om
                              JOIN organisations o ON om.org_id = o.org_id
                              WHERE om.user_id = ?");
        $stmt->execute([$user['user_id']]);
        $orgMembership = $stmt->fetch();

        if ($orgMembership) {
            $_SESSION['current_org_id'] = $orgMembership['org_id'];
            $_SESSION['org_role'] = $orgMembership['role_id'];
            
            switch ($orgMembership['role_id']) {
                case 3: // Org Owner
                    header("Location: org-owner-home.php");
                    break;
                case 4: // Org Admin
                    header("Location: org-admin-home.php");
                    break;
                case 6: // Org member
                    header("Location: org-member-home.php");
                    break;
                case 1: // System Admin
                    header("Location: system-admin-home.php");
                    break;
                default: // Regular User
                    header("Location: project-home.php");
            }
        } else {
            header("Location: project-home.php");
        }
        exit();
    } else {
        $error_message = "Invalid email or password."; 
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
