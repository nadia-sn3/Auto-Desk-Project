<?php
session_start();
require 'db/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT user_id, first_name, password, system_role_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['is_admin'] = ($user['system_role_id'] == 1);

        if ($_SESSION['is_admin']) {
            header("Location: system-admin-home.php");
            exit;
        } else {
            header("Location: home.php"); 
            exit;
        }
    } else {
        $_SESSION['login_error'] = "Invalid email or password.";
        header("Location: signin.php");
        exit;
    }
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM organisations");
    $orgCount = $stmt->fetchColumn();
} catch (PDOException $e) {
    echo "Error fetching organisations: " . $e->getMessage();
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
} catch (PDOException $e) {
    echo "Error fetching users: " . $e->getMessage();
}
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM project");
    $projectCount = $stmt->fetchColumn();
} catch (PDOException $e) {
    echo "Error fetching projects: " . $e->getMessage();
}
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'reports'");
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'");
        $reportedIssues = $stmt->fetchColumn();
    } else {
        $reportedIssues = 0; 
    }
} catch (PDOException $e) {
    $reportedIssues = 0; 
}
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
                <p><?php echo htmlspecialchars($orgCount); ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?php echo htmlspecialchars($userCount); ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Projects</h3>
                <p><?php echo htmlspecialchars($projectCount); ?></p>
            </div>
            <div class="stat-card">
                <h3>Pending Reports</h3>
                <p><?php echo htmlspecialchars($reportedIssues); ?></p>
            </div>
        </div>

        <div class="admin-actions">
            <h2>User Management</h2>
            <a href="manage-users.php" class="btn">Manage Users</a>

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