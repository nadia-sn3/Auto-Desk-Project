<?php
session_start();
require 'db/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reportType = $_POST['report_type'] ?? '';
    $targetId = $_POST['target_id'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $description = $_POST['description'] ?? '';
    $reporterId = $_SESSION['user_id'];
    
    if (empty($reportType) || empty($targetId) || empty($reason)) {
        $error = "Please fill all required fields.";
    } else {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'reports'");
            if ($stmt->rowCount() == 0) {
                $pdo->exec("CREATE TABLE reports (
                    report_id INT AUTO_INCREMENT PRIMARY KEY,
                    reporter_id INT NOT NULL,
                    report_type ENUM('user', 'project') NOT NULL,
                    target_id INT NOT NULL,
                    reason VARCHAR(255) NOT NULL,
                    description TEXT,
                    status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (reporter_id) REFERENCES users(user_id)
                )");
            }
            
            $stmt = $pdo->prepare("INSERT INTO reports 
                                  (reporter_id, report_type, target_id, reason, description, status) 
                                  VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$reporterId, $reportType, $targetId, $reason, $description]);
            
            $success = "Your report has been submitted successfully. Our team will review it shortly.";
        } catch (PDOException $e) {
            $error = "Error submitting report: " . $e->getMessage();
        }
    }
}

$projects = [];
try {
    $stmt = $pdo->query("SELECT project_id, title FROM project");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching projects: " . $e->getMessage();
}

$users = [];
try {
    $stmt = $pdo->prepare("SELECT user_id, first_name, last_name FROM users WHERE user_id != ?");
    $stmt->execute([$_SESSION['user_id']]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching users: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report an Issue</title>
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/report.css">
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="report-container">
        <h1>Submit a Report</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form action="report.php" method="POST">
            <div class="form-group">
                <label for="report_type">What are you reporting?*</label>
                <select name="report_type" id="report_type" required>
                    <option value="">-- Select --</option>
                    <option value="user">A User</option>
                    <option value="project">A Project</option>
                </select>
            </div>
            
            <div class="form-group" id="target-user-group">
                <label for="target_user">Select User to Report*</label>
                <select name="target_id" id="target_user" disabled>
                    <option value="">-- Select User --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo htmlspecialchars($user['user_id']); ?>">
                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" id="target-project-group">
                <label for="target_project">Select Project to Report*</label>
                <select name="target_id" id="target_project" disabled>
                    <option value="">-- Select Project --</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?php echo htmlspecialchars($project['project_id']); ?>">
                            <?php echo htmlspecialchars($project['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="reason">Reason for Report*</label>
                <select name="reason" id="reason" required>
                    <option value="">-- Select Reason --</option>
                    <option value="Inappropriate Content">Inappropriate Content</option>
                    <option value="Harassment">Harassment</option>
                    <option value="Spam or Scam">Spam or Scam</option>
                    <option value="Violation of Terms">Violation of Terms</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="description">Additional Details</label>
                <textarea name="description" id="description" rows="5" 
                          placeholder="Please provide any additional details that might help us understand the issue..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-submit">Submit Report</button>
        </form>
    </div>

    <?php include('include/footer.php'); ?>

    <script>
        document.getElementById('report_type').addEventListener('change', function() {
            const reportType = this.value;
            const userGroup = document.getElementById('target-user-group');
            const userSelect = document.getElementById('target_user');
            const projectGroup = document.getElementById('target-project-group');
            const projectSelect = document.getElementById('target_project');
            
            userGroup.style.display = 'none';
            userSelect.disabled = true;
            userSelect.required = false;
            projectGroup.style.display = 'none';
            projectSelect.disabled = true;
            projectSelect.required = false;
            
            if (reportType === 'user') {
                userGroup.style.display = 'block';
                userSelect.disabled = false;
                userSelect.required = true;
            } else if (reportType === 'project') {
                projectGroup.style.display = 'block';
                projectSelect.disabled = false;
                projectSelect.required = true;
            }
        });
    </script>
</body>
</html>