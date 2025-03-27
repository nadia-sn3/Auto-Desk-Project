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
    header("Location: home.php");
    exit();
}

$stmt = $pdo->prepare("SELECT COUNT(*) as org_count FROM organisations");
$stmt->execute();
$orgCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) as user_count FROM users");
$stmt->execute();
$userCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) as project_count FROM projects");
$stmt->execute();
$projectCount = $stmt->fetchColumn();

$reportedIssues = 0;
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
            <div class="stat-card">
                <h3>Pending Reports</h3>
                <p><?php echo $reportedIssues; ?></p>
            </div>
        </div>

        <div class="admin-actions">
            <h2>User Management</h2>
            <a href="manage-users.php" class="btn">Manage Users</a>
            <a href="approve-users.php" class="btn">Approve User Requests</a>

            <h2>Content Moderation</h2>
            <a href="review-reports.php" class="btn">Review User Reports</a>
            <a href="moderate-content.php" class="btn">Moderate Content</a>

            <h2>System Maintenance</h2>
            <a href="system-status.php" class="btn">Monitor System Health</a>

            <h2>Policy Enforcement</h2>
            <a href="audit-logs.php" class="btn">View Audit Logs</a>

            <h2>Reports & Analytics</h2>
            <a href="generate-reports.php" class="btn">Generate Reports</a>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>