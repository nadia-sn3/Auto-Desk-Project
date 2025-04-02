<?php
session_start();
require 'db/connection.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: signin.php");
    exit;
}

try {
    $stmt = $pdo->query("SELECT 1");
    $dbStatus = "Database is connected.";
} catch (PDOException $e) {
    $dbStatus = "Database connection failed: " . $e->getMessage();
}

$serverStatus = "Server is running normally.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/system-status.css">
    <title>System Status</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="container">
        <h1>System Health Monitoring</h1>

        <div class="status-card">
            <h3>Database Status</h3>
            <p><?php echo htmlspecialchars($dbStatus); ?></p>
        </div>

        <div class="status-card">
            <h3>Server Status</h3>
            <p><?php echo htmlspecialchars($serverStatus); ?></p>
        </div>

        <a href="system-admin-home.php" class="btn">Back to Dashboard</a>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>
