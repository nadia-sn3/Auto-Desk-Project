<?php
session_start();
require 'db/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$stmt = $pdo->prepare("SELECT system_role_id FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

if (!$currentUser || $currentUser['system_role_id'] != 1) {
    header("Location: unauthorized.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage-users.php");
    exit();
}

$userIdToDelete = $_GET['id'];

if ($userIdToDelete == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account!";
    header("Location: manage-users.php");
    exit();
}

$stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
$deleted = $stmt->execute([$userIdToDelete]);

if ($deleted) {
    $_SESSION['success'] = "User deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete user.";
}

header("Location: manage-users.php");
exit();
?>
