<?php
require 'db/connection.php';
session_start();

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $pdo->prepare("SELECT user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch();
        $_SESSION['reset_user_id'] = $user['user_id'];
        $_SESSION['reset_token'] = $token;
        header("Location: set-password.php"); 
        exit();
    } else {
        echo "Invalid or expired token.";
    }
} else {
    echo "No token provided.";
}
?>
