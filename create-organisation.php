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

$stmt = $pdo->prepare("SELECT COUNT(*) FROM organisation_members WHERE user_id = ?");
$stmt->execute([$userId]);
if ($stmt->fetchColumn() > 0) {
    $error = "You are already a member or owner of an organisation. You cannot create a new one.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error)) {
    $orgName = trim($_POST['org_name']);
    $description = trim($_POST['description'] ?? '');
    $inviteEmails = isset($_POST['invite_emails']) ? explode(',', $_POST['invite_emails']) : [];
    $inviteEmails = array_map('trim', $inviteEmails);
    $inviteEmails = array_filter($inviteEmails);

    if (empty($orgName)) {
        $error = "Organisation name is required.";
    } elseif (strlen($orgName) > 200) {
        $error = "Organisation name must be less than 200 characters.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM organisations WHERE org_name = ?");
            $stmt->execute([$orgName]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("An organisation with this name already exists.");
            }

            $stmt = $pdo->prepare("INSERT INTO organisations (org_name, description) VALUES (?, ?)");
            $stmt->execute([$orgName, $description]);
            $orgId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO organisation_members (org_id, user_id, role_id, invited_by) 
                                  VALUES (?, ?, 2, ?)");
            $stmt->execute([$orgId, $userId, $userId]);

            if (!empty($inviteEmails)) {
                $inviteStmt = $pdo->prepare("INSERT INTO invitations 
                                            (org_id, email, token, role_id, invited_by, status) 
                                            VALUES (?, ?, ?, 3, ?, 'pending')");
                
                foreach ($inviteEmails as $email) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $token = bin2hex(random_bytes(32));
                        $inviteStmt->execute([$orgId, $email, $token, $userId]);
                    }
                }
            }

            $pdo->commit();
            
            $_SESSION['current_org_id'] = $orgId;
            $_SESSION['org_role'] = 2;
            
            header("Location: org-owner-home.php?new=1");
            exit();
        } catch (Exception $e) {
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
    <title>Setup Your Organisation | Autodesk</title>
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
            </div>
            
            <?php if ($error): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
                <?php if (strpos($error, 'already a member') !== false): ?>
                    <div class="form-actions">
                        <a href="org-owner-home.php" class="btn primary">Return to Dashboard</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
            
            <form method="POST" class="setup-form">
                <div class="form-section">
                    <h2>Basic Information</h2>
                    
                    <div class="form-group">
                        <label for="org_name">Organisation Name*</label>
                        <input type="text" id="org_name" name="org_name" required 
                               placeholder="e.g. Acme Corporation" maxlength="200"
                               value="<?= htmlspecialchars($_POST['org_name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description (Optional)</label>
                        <textarea id="description" name="description" 
                                 placeholder="What does your organisation do?"
                                 rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Invite Team Members</h2>
                    <p class="hint">Add colleagues by email (comma separated)</p>
                    
                    <div class="form-group">
                        <label for="invite_emails">Email Addresses</label>
                        <textarea id="invite_emails" name="invite_emails" 
                                 placeholder="team@example.com, member@example.com"
                                 rows="2"></textarea>
                    </div>
                    
                    <div class="role-info">
                        <strong>Note:</strong> Invited members will join as <strong>Organisation Admins</strong> 
                        and can be promoted to Owners later.
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn primary">Create Organisation</button>
                    <a href="dashboard.php" class="btn secondary">Cancel</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>