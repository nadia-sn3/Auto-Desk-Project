<?php
require 'db/connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $orgName = trim($_POST['org_name']);
    $description = trim($_POST['description'] ?? '');

    if (empty($orgName)) {
        $error = "Organisation name is required.";
    } elseif (strlen($orgName) > 200) {
        $error = "Organisation name must be less than 200 characters.";
    } else {
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO organisations (org_name, description) VALUES (?, ?)");
    $stmt->execute([$orgName, $description]);
    $orgId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO organisation_members (org_id, user_id, role_id, invited_by) 
                          VALUES (?, ?, 2, ?)");
    $stmt->execute([$orgId, $userId, $userId]);

    $pdo->commit();
    
    $_SESSION['current_org_id'] = $orgId;
    $_SESSION['org_role'] = 2;
    
    $stmt = $pdo->prepare("SELECT role_id FROM organisation_members WHERE org_id = ? AND user_id = ?");
    $stmt->execute([$orgId, $userId]);
    $role = $stmt->fetchColumn();
    
    if ($role != 2) {
        throw new Exception("Failed to assign owner role to organization creator");
    }
    
    header("Location: org-owner-home.php?new=1");
    exit();
} catch (PDOException $e) {
    $pdo->rollBack();
    $error = "Error creating organisation: " . $e->getMessage();
}
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Organisation | AutoDesk</title>
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/setup-organisation.css">
</head>
<body>
    <?php include('include/header.php'); ?>
    
    <div class="setup-container">
        <div class="setup-card">
            <div class="setup-header">
                <h1>Create Your Organisation</h1>
                <p>Get started by setting up your team workspace</p>
                
                <?php if ($error): ?>
                    <div class="alert error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
            </div>
            
            <form method="POST" class="setup-form">
                <div class="form-section">
                    <h2>Basic Information</h2>
                    
                    <div class="form-group">
                        <label for="org_name">Organisation Name*</label>
                        <input type="text" id="org_name" name="org_name" required 
                               placeholder="e.g. Acme Corp" maxlength="200"
                               value="<?= htmlspecialchars($_POST['org_name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description (Optional)</label>
                        <textarea id="description" name="description" 
                                 placeholder="Briefly describe your organisation"
                                 rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn primary">Create Organisation</button>
                    <a href="dashboard.php" class="btn secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>