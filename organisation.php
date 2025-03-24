<?php
require 'db/connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$orgName = "My Organisation"; 
$orgId = $_SESSION['current_org_id'] ?? null;

if ($orgId) {
    $stmt = $pdo->prepare("SELECT org_name FROM organisations WHERE org_id = ?");
    $stmt->execute([$orgId]);
    if ($stmt->rowCount() > 0) {
        $org = $stmt->fetch();
        $orgName = htmlspecialchars($org['org_name']);
    }
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}


$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_member'])) {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $roleId = (int)$_POST['role_id'];
    $orgId = (int)$_POST['org_id'];

    if (empty($firstName) || empty($lastName) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide valid information for all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            // Existing user - add to organization
            $user = $stmt->fetch();
            $userId = $user['user_id'];
            
            $stmt = $pdo->prepare("INSERT INTO organisation_members (org_id, user_id, role_id, joined_at, invited_by) 
                                  VALUES (?, ?, ?, NOW(), ?)");
            if ($stmt->execute([$orgId, $userId, $roleId, $_SESSION['user_id']])) {
                $success = "User added to organization successfully.";
                
                // Send notification email to existing user
                $subject = "You've been added to an organization on AutoDesk";
                $message = "
                <html>
                <head>
                    <title>Organization Invitation</title>
                </head>
                <body>
                    <h2>Hello $firstName!</h2>
                    <p>You've been added to an organization on AutoDesk. You can now access this organization's projects.</p>
                    <p>Login to your account at <a href='https://yourdomain.com/signin'>AutoDesk</a> to get started.</p>
                </body>
                </html>
                ";
                
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: AutoDesk <noreply@yourdomain.com>" . "\r\n";
                
                mail($email, $subject, $message, $headers);
            } else {
                $error = "Failed to add user to organization.";
            }
        } else {
            // New user - create account with default password
            $defaultPassword = 'Welcome123!'; // You might want to make this configurable
            $passwordHash = password_hash($defaultPassword, PASSWORD_BCRYPT);
            
            try {
                $pdo->beginTransaction();
                
                // Create user account
                $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password_hash, created_at) 
                                      VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$firstName, $lastName, $email, $passwordHash]);
                $userId = $pdo->lastInsertId();
                
                // Add to organization
                $stmt = $pdo->prepare("INSERT INTO organisation_members (org_id, user_id, role_id, joined_at, invited_by) 
                                      VALUES (?, ?, ?, NOW(), ?)");
                $stmt->execute([$orgId, $userId, $roleId, $_SESSION['user_id']]);
                
                $pdo->commit();
                
                // Send welcome email with password reset instructions
                $token = generateToken();
                $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                $stmt = $pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) 
                                      VALUES (?, ?, ?)");
                $stmt->execute([$userId, $token, $expiresAt]);
                
                $resetLink = "https://yourdomain.com/reset-password.php?token=" . urlencode($token);
                
                $subject = "Welcome to AutoDesk - Set Up Your Account";
                $message = "
                <html>
                <head>
                    <title>Welcome to AutoDesk</title>
                </head>
                <body>
                    <h2>Welcome to AutoDesk, $firstName!</h2>
                    <p>An account has been created for you with our organization. For security reasons, we require you to set a new password for your account.</p>
                    <p><a href='$resetLink'>Click here to set your password</a></p>
                    <p>This link will expire in 24 hours. If you didn't request this, please ignore this email.</p>
                    <p>If the link doesn't work, you can copy and paste this URL into your browser:</p>
                    <p>$resetLink</p>
                </body>
                </html>
                ";
                
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: AutoDesk <noreply@yourdomain.com>" . "\r\n";
                
                if (mail($email, $subject, $message, $headers)) {
                    $success = "User account created successfully. A password setup email has been sent to $email.";
                } else {
                    $error = "User account created but failed to send email. Please contact support.";
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Failed to create user: " . $e->getMessage();
            }
        }
    }
    
    if ($success) {
        $_SESSION['success'] = $success;
    }
    if ($error) {
        $_SESSION['error'] = $error;
    }
    
    header("Location: organisation.php");
    exit();
}
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/userhome.css">
    <link rel="stylesheet" href="style/preview.css">
    <link rel="stylesheet" href="style/organisation.css">

    <script src="js/sidebar-toggle.js" defer></script>
    <title>AutoDesk | Organisation</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="page-container">
        <aside class="sidebar">
            <h3><a href="organisation.php">Organisation</a></h3>
            <ul>
                <li><a href="#overview">Overview</a></li>
                <li><a href="#members">Members</a></li>
                <li><a href="#projects">Projects</a></li>
                <li><a href="#settings">Settings</a></li>
            </ul>

            <h3>Quick Actions</h3>
            <ul>
                <li><a href="#invite-member">Invite Member</a></li>
                <li><a href="#create-project">Create Project</a></li>
                <li><a href="#manage-roles">Manage Roles</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="org-header">
                <h1><?php echo $orgName; ?></h1> 
                <div class="org-actions">
                    <button class="btn-primary">Invite Members</button>
                    <button class="btn-secondary">Create Project</button>
                </div>
            </div>

            <section id="overview" class="org-section">
                <h2>Organisation Overview</h2>
                <div class="org-stats">
                    <div class="stat-card">
                        <h3>Total Members</h3>
                        <p>24</p>
                    </div>
                    <div class="stat-card">
                        <h3>Active Projects</h3>
                        <p>8</p>
                    </div>
                    <div class="stat-card">
                        <h3>Storage Used</h3>
                        <p>4.7 GB / 10 GB</p>
                    </div>
                </div>
            </section>

            <section id="roles" class="org-section">
                <h2>Organisation Roles & Permissions</h2>
                <div class="roles-container">
                    <div class="role-card">
                        <h3>Organisation Creator</h3>
                        <ul class="permissions-list">
                            <li>Full control over organisation settings</li>
                            <li>Create/edit/delete organisation</li>
                            <li>Transfer ownership</li>
                            <li>Manage all members and roles</li>
                            <li>Create organisation-level projects</li>
                        </ul>
                    </div>
                    
                    <div class="role-card">
                        <h3>Organisation Manager</h3>
                        <ul class="permissions-list">
                            <li>Manage organisation members</li>
                            <li>Invite new members</li>
                            <li>Assign roles to members</li>
                            <li>Create organisation projects</li>
                            <li>Receive project notifications</li>
                        </ul>
                    </div>
                    
                    <div class="role-card">
                        <h3>Organisation Member</h3>
                        <ul class="permissions-list">
                            <li>Participate in organisation projects</li>
                            <li>Access assigned projects</li>
                            <li>Receive project updates</li>
                            <li>Cannot create organisation projects</li>
                            <li>Cannot manage members</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="members" class="org-section">
                <h2>Organisation Members</h2>
                <div class="filter-bar">
                    <input type="text" placeholder="Search members...">
                    <select>
                        <option value="all">All Roles</option>
                        <option value="creator">Creator</option>
                        <option value="manager">Manager</option>
                        <option value="member">Member</option>
                    </select>
                    <button>Filter</button>
                </div>
                
                <div class="members-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>John Smith</td>
                                <td>john@example.com</td>
                                <td>Creator</td>
                                <td>Jan 15, 2024</td>
                                <td>
                                    <button class="btn-small">Edit</button>
                                    <button class="btn-small btn-danger">Remove</button>
                                </td>
                            </tr>
                            <tr>
                                <td>Sarah Johnson</td>
                                <td>sarah@example.com</td>
                                <td>Manager</td>
                                <td>Feb 2, 2024</td>
                                <td>
                                    <button class="btn-small">Edit</button>
                                    <button class="btn-small btn-danger">Remove</button>
                                </td>
                            </tr>
                            <tr>
                                <td>Michael Brown</td>
                                <td>michael@example.com</td>
                                <td>Member</td>
                                <td>Mar 10, 2024</td>
                                <td>
                                    <button class="btn-small">Edit</button>
                                    <button class="btn-small btn-danger">Remove</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="projects" class="org-section">
                <h2>Organisation Projects</h2>
                <div class="preview-projects">
                    <?php include('preview.php'); ?>
                    <?php include('preview.php'); ?>
                    <?php include('preview.php'); ?>
                </div>
            </section>
        </main>
    </div>

    <div id="addMemberModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Member</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php elseif (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form id="addMemberForm" method="POST" action="organisation.php">
                <input type="hidden" name="org_id" value="1">
                <input type="hidden" name="add_member" value="1">
                
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="role_id">Role</label>
                    <select id="role_id" name="role_id" required>
                        <option value="2">Organization Owner</option>
                        <option value="3">Organization Admin</option>
                        <option value="5" selected>Team Member</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary close-modal">Cancel</button>
                    <button type="submit" class="btn-primary">Add Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="js/sidebar-toggle.js" defer></script>
<script src="js/add-organisation-member.js" defer></script>

    <?php include('include/footer.php'); ?>
</body>
</html>