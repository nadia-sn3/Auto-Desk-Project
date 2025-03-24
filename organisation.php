<?php
require 'db/connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

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
            $user = $stmt->fetch();
            $userId = $user['user_id'];
            
            $stmt = $pdo->prepare("INSERT INTO organisation_members (org_id, user_id, role_id, joined_at, invited_by) 
                                  VALUES (?, ?, ?, NOW(), ?)");
            if ($stmt->execute([$orgId, $userId, $roleId, $_SESSION['user_id']])) {
                $success = "User added to organization successfully.";
            } else {
                $error = "Failed to add user to organization.";
            }
        } else {
            $tempPassword = bin2hex(random_bytes(8));
            $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT);
            
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password_hash, created_at) 
                                      VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$firstName, $lastName, $email, $passwordHash]);
                $userId = $pdo->lastInsertId();
                
                $stmt = $pdo->prepare("INSERT INTO organisation_members (org_id, user_id, role_id, joined_at, invited_by) 
                                      VALUES (?, ?, ?, NOW(), ?)");
                $stmt->execute([$orgId, $userId, $roleId, $_SESSION['user_id']]);
                
                $token = generateToken();
                $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                $stmt = $pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) 
                                      VALUES (?, ?, ?)");
                $stmt->execute([$userId, $token, $expiresAt]);
                
                $pdo->commit();
                
                $resetLink = "link/reset-password.php?token=" . urlencode($token);
                
                $subject = "Welcome to AutoDesk - Set Your Password";
                $message = "
                <html>
                <head>
                    <title>Welcome to AutoDesk</title>
                </head>
                <body>
                    <h2>Welcome to AutoDesk, $firstName!</h2>
                    <p>You've been added to our organisation. Please set your password by clicking the link below:</p>
                    <p><a href='$resetLink'>Set Your Password</a></p>
                    <p>This link will expire in 24 hours.</p>
                    <p>If you didn't request this, please ignore this email.</p>
                </body>
                </html>
                ";
                
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: AutoDesk <noreply@yourdomain.com>" . "\r\n";
                
                if (mail($email, $subject, $message, $headers)) {
                    $success = "User created successfully. A password setup email has been sent to $email.";
                } else {
                    $error = "User created but failed to send email. Please contact support.";
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Failed to create user: " . $e->getMessage();
            }
        }
    }
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
                <h1>Organisation Management</h1>
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

    <?php include('include/footer.php'); ?>
</body>
</html>