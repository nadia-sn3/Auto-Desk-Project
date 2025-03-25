<?php
session_start();
if (!isset($_SESSION['user_id']) != 1) {
    header("Location: signin.php");
    exit();
}
require 'db/connection.php';

$stmt = $pdo->prepare("SELECT COUNT(*) as org_count FROM organisations");
$stmt->execute();
$orgCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) as user_count FROM users");
$stmt->execute();
$userCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) as project_count FROM projects");
$stmt->execute();
$projectCount = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/system-home-admin.css">
    <title>System Admin Dashboard</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="dashboard-container">
        <h1>System Admin Dashboard</h1>

        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Organisations</h3>
                <p><?php echo $orgCount; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?php echo $userCount; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Projects</h3>
                <p><?php echo $projectCount; ?></p>
            </div>
        </div>

        <div class="admin-actions">
            <a href="manage-organisations.php" class="btn">Manage Organisations</a>
            <a href="manage-users.php" class="btn">Manage Users</a>
            <a href="manage-projects.php" class="btn">Manage Projects</a>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>
