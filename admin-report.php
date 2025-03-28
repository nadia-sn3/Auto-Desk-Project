<?php
session_start();
require 'db/connection.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: signin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $report_id = filter_input(INPUT_POST, 'report_id', FILTER_SANITIZE_NUMBER_INT);
    $action = $_POST['action'];
    
    try {
        if ($action == 'resolve') {
            $stmt = $pdo->prepare("UPDATE reports SET status = 'resolved', resolved_by = ?, resolved_at = NOW() WHERE report_id = ?");
            $stmt->execute([$_SESSION['user_id'], $report_id]);
            $_SESSION['message'] = "Report #$report_id has been marked as resolved.";
        } elseif ($action == 'delete') {
            $stmt = $pdo->prepare("DELETE FROM reports WHERE report_id = ?");
            $stmt->execute([$report_id]);
            $_SESSION['message'] = "Report #$report_id has been deleted.";
        }
        header("Location: admin-report.php".(isset($_GET['filter']) ? "?filter=".$_GET['filter'] : ""));
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error processing report: " . $e->getMessage();
    }
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

try {
    $query = "
        SELECT r.*, 
               reporter.first_name AS reporter_name, 
               reporter.email AS reporter_email,
               CASE 
                 WHEN r.reported_type = 'user' THEN reported_user.first_name
                 WHEN r.reported_type = 'project' THEN reported_project.project_name
               END AS reported_name,
               CASE 
                 WHEN r.reported_type = 'user' THEN reported_user.email
                 WHEN r.reported_type = 'project' THEN NULL
               END AS reported_email,
               resolver.first_name AS resolved_by_name
        FROM reports r
        JOIN users reporter ON r.reporter_id = reporter.user_id
        LEFT JOIN users reported_user ON r.reported_type = 'user' AND r.reported_id = reported_user.user_id
        LEFT JOIN Project reported_project ON r.reported_type = 'project' AND r.reported_id = reported_project.project_id
        LEFT JOIN users resolver ON r.resolved_by = resolver.user_id
        WHERE 
            (:filter = 'all') OR
            (:filter = 'pending' AND r.status = 'pending') OR
            (:filter = 'resolved' AND r.status = 'resolved')
        ORDER BY r.status ASC, r.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['filter' => $filter]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching reports: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports Management</title>
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/admin-report.css">
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="container">
        <h1>Report Management</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <div class="report-filters">
            <a href="admin-report.php?filter=all" class="btn <?= $filter == 'all' ? 'active' : '' ?>">All Reports</a>
            <a href="admin-report.php?filter=pending" class="btn <?= $filter == 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="admin-report.php?filter=resolved" class="btn <?= $filter == 'resolved' ? 'active' : '' ?>">Resolved</a>
        </div>

        <div class="reports-list">
            <?php if (empty($reports)): ?>
                <p>No reports found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Reporter</th>
                            <th>Reported</th>
                            <th>Type</th>
                            <th>Reason</th>
                            <th>Details</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                        <tr class="<?= $report['status'] ?>">
                            <td><?= $report['report_id'] ?></td>
                            <td>
                                <?= htmlspecialchars($report['reporter_name']) ?><br>
                                <small><?= htmlspecialchars($report['reporter_email']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($report['reported_name']) ?>
                                <?php if ($report['reported_email']): ?>
                                    <br><small><?= htmlspecialchars($report['reported_email']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= ucfirst($report['reported_type']) ?></td>
                            <td><?= htmlspecialchars($report['report_reason']) ?></td>
                            <td><?= htmlspecialchars($report['report_details']) ?></td>
                            <td>
                                <span class="status-badge <?= $report['status'] ?>">
                                    <?= ucfirst($report['status']) ?>
                                </span>
                                <?php if ($report['resolved_by_name']): ?>
                                    <br><small>by <?= $report['resolved_by_name'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M j, Y g:i a', strtotime($report['created_at'])) ?></td>
                            <td class="actions">
                                <?php if ($report['status'] == 'pending'): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">
                                        <button type="submit" name="action" value="resolve" class="btn small">Resolve</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">
                                    <button type="submit" name="action" value="delete" class="btn small danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>