<?php
require 'db/connection.php';
require 'auth.php'; 

if ($_SESSION['system_role_id'] != 1) {
    header("Location: index.php");
    exit();
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$totalQuery = $pdo->query("SELECT COUNT(*) FROM audit_logs");
$total = $totalQuery->fetchColumn();

$query = "SELECT a.*, u.email, u.first_name, u.last_name, GROUP_CONCAT(p.project_name) AS projects
          FROM audit_logs a
          LEFT JOIN users u ON a.user_id = u.user_id
          LEFT JOIN Project p ON u.user_id = p.created_by
          GROUP BY a.user_id
          ORDER BY a.created_at DESC
          LIMIT :offset, :perPage";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalPages = ceil($total / $perPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs</title>
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/audit-logs.css">  
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="container">
    <h1>Audit Logs</h1>
    
    <div class="audit-log-container">
        <div class="audit-log-header">
            <h4>User Activity Log</h4>
        </div>
        
        <div class="table-container">
            <?php if (count($logs) > 0): ?>
                <table class="audit-log-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Projects Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr class="audit-log-row">
                                <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($log['created_at']))) ?></td>
                                <td>
                                    <?php if ($log['user_id']): ?>
                                        <?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name'] . ' (' . $log['email'] . ')') ?>
                                    <?php else: ?>
                                        System
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($log['projects']): ?>
                                        <?= htmlspecialchars($log['projects']) ?>
                                    <?php else: ?>
                                        No projects created
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>">&laquo; Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>" <?= $i === $page ? 'class="active"' : '' ?>><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="no-logs">
                    <p>No audit logs found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

    <?php include('include/footer.php'); ?>
</body>
</html>
