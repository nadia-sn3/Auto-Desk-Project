<?php
session_start();
require 'db/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$stmt = $pdo->prepare("SELECT system_role_id FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['system_role_id'] != 1) {
    header("Location: unauthorized.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST['user_id'];

    $stmt = $pdo->prepare("SELECT email, first_name, last_name FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();

    if ($userData) {
        $_SESSION['success'] = "A password reset email has been sent to " . htmlspecialchars($userData['email']) . ".";
        header("Location: edit-user.php?id=$userId");
        exit();
    } else {
        $_SESSION['error'] = "User not found.";
        header("Location: manage-users.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <link rel="stylesheet" href="style/base.css">
</head>
<body>

    <?php include('include/header.php'); ?>

    <div class="page-container">
        <div class="reset-password-container">
            <h4>Password Reset Requested</h4>
            <p>An email with instructions to reset the password has been sent to the user. If the email is not received, please try again later.</p>
            
            <?php
            if (isset($_SESSION['success'])) {
                echo '<div class="alert success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo '<div class="alert error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            ?>
        </div>
    </div>

    <?php include('include/footer.php'); ?>

</body>
</html>
