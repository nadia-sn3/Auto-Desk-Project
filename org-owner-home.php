<?php
session_start();
require_once 'db/connection.php';
require_once 'backend/Business_Logic/Function/send_email.php' ;


$success = '';
$error = '';
if (!isset($_SESSION['current_org_id'])) {
    header("Location: home.php");
    exit();
}
$orgId = $_SESSION['current_org_id'];

$members = [];
$totalMembers = 0;
try {
    $stmt = $pdo->prepare("SELECT org_name, description FROM organisations WHERE org_id = ?");
    $stmt->execute([$orgId]);
    if ($org = $stmt->fetch()) {
        $orgName = $org['org_name'];
        $orgDescription = $org['description'] ?? '';
    } else {
        throw new Exception("Organisation not found");
    }
} catch (Exception $e) {
    $error = "Error loading organisation: " . $e->getMessage();
}

$activeProjects = 0;
$projects = [];

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM organisation_members WHERE org_id = ?");
    $stmt->execute([$orgId]);
    $totalMembers = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT u.user_id, u.first_name, u.last_name, u.email, 
               om.joined_at, oroles.role_name, oroles.org_role_id
        FROM organisation_members om
        JOIN users u ON om.user_id = u.user_id
        JOIN organisation_roles oroles ON om.org_role_id = oroles.org_role_id
        WHERE om.org_id = ?
        ORDER BY oroles.org_role_id, om.joined_at DESC
    ");
    $stmt->execute([$orgId]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Project WHERE created_by = ?");
    $stmt->execute([$orgId]);
    $activeProjects = $stmt->fetchColumn();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
    try {
        if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) || empty($_POST['org_role_id'])) {
            throw new Exception("All fields are required");
        }

        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $roleId = (int)$_POST['org_role_id'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            $userId = $user['user_id'];
            
            $stmt = $pdo->prepare("SELECT org_member_id FROM organisation_members WHERE org_id = ? AND user_id = ?");
            $stmt->execute([$orgId, $userId]);
            
            if ($stmt->rowCount() > 0) {
                throw new Exception("User already in organisation");
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO organisation_members 
                (org_id, user_id, org_role_id, joined_at, invited_by) 
                VALUES (?, ?, ?, NOW(), ?)
            ");
            $invitedBy = $_SESSION['user_id'] ?? 1;
            $stmt->execute([$orgId, $userId, $roleId, $invitedBy]);
            
            $success = "User added to organisation";
            $message = "Hello $firstName $lastName,\n\nYou have been added to the organisation. Please log in using your existing credentials.";
        } else {
            $password = isset($_POST['set_custom_password']) && !empty($_POST['password']) 
                ? $_POST['password'] 
                : bin2hex(random_bytes(4));
            
            if (strlen($password) < 8) {
                throw new Exception("Password must be 8+ characters");
            }
            
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $pdo->beginTransaction();
            

            $stmt = $pdo->prepare("
                INSERT INTO users 
                (system_role_id, email, password_hash, first_name, last_name, created_at, is_active) 
                VALUES (2, ?, ?, ?, ?, NOW(), 1)
            ");
            $stmt->execute([$email, $passwordHash, $firstName, $lastName]);
            $userId = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("
                INSERT INTO organisation_members 
                (org_id, user_id, org_role_id, joined_at, invited_by) 
                VALUES (?, ?, ?, NOW(), ?)
            ");
            $invitedBy = $_SESSION['user_id'] ?? 1;
            $stmt->execute([$orgId, $userId, $roleId, $invitedBy]);
            
            $pdo->commit();
            $success = "New user created and added to organisation";
            $stmt = $pdo->prepare("SELECT role_name FROM organisation_roles WHERE org_role_id = ?");
            $stmt->execute([$roleId]);
            $roleName = $stmt->fetchColumn() ?: "Member";
            $message = "Hello $firstName $lastName,\n\nYou have been added to the organisation as a $roleName. Please use the following credentials to log in:\n\nEmail: $email\nPassword: $password\n\nYou are able to reset your password after logging in.\n\nBest regards,\nTeam.";
        }

        $subject = "Welcome to the Organisation!";
        $headers = "From: your_email@example.com"; 
        if (send_email($email, $subject, $message)) {
            
        } else {
            echo "Failed to send welcome email.";
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM organisation_members WHERE org_id = ?");
        $stmt->execute([$orgId]);
        $totalMembers = $stmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT u.user_id, u.first_name, u.last_name, u.email, 
                   om.joined_at, oroles.role_name, oroles.org_role_id
            FROM organisation_members om
            JOIN users u ON om.user_id = u.user_id
            JOIN organisation_roles oroles ON om.org_role_id = oroles.org_role_id
            WHERE om.org_id = ?
            ORDER BY oroles.org_role_id, om.joined_at DESC
        ");
        $stmt->execute([$orgId]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_member'])) {
    try {
        $currentUserRole = null;
        foreach ($members as $member) {
            if ($member['user_id'] == ($_SESSION['user_id'] ?? null)) {
                $currentUserRole = $member['org_role_id'];
                break;
            }
        }

        if (!$currentUserRole || $currentUserRole > 2) {
            throw new Exception("Only admins can remove members");
        }

        $userIdToRemove = (int)$_POST['user_id'];
        $userToRemoveRole = null;
        $userToRemoveName = '';

        foreach ($members as $member) {
            if ($member['user_id'] == $userIdToRemove) {
                $userToRemoveRole = $member['org_role_id'];
                $userToRemoveName = $member['first_name'] . ' ' . $member['last_name'];
                break;
            }
        }

        if (!$userToRemoveRole) {
            throw new Exception("User not found in organisation");
        }

        if ($userToRemoveRole <= 2 && $userIdToRemove != ($_SESSION['user_id'] ?? null)) {
            throw new Exception("Cannot remove other admins or owner");
        }

        if ($userToRemoveRole == 1 && $userIdToRemove == ($_SESSION['user_id'] ?? null)) {
            throw new Exception("Owner cannot remove themselves. Transfer ownership first.");
        }

        $stmt = $pdo->prepare("DELETE FROM organisation_members WHERE org_id = ? AND user_id = ?");
        $stmt->execute([$orgId, $userIdToRemove]);

        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$_SESSION['user_id'] ?? 1, "Removed user $userToRemoveName from organisation"]);

        $success = "User removed successfully";
        
        $stmt = $pdo->prepare("
            SELECT u.user_id, u.first_name, u.last_name, u.email, 
                   om.joined_at, oroles.role_name, oroles.org_role_id
            FROM organisation_members om
            JOIN users u ON om.user_id = u.user_id
            JOIN organisation_roles oroles ON om.org_role_id = oroles.org_role_id
            WHERE om.org_id = ?
            ORDER BY oroles.org_role_id, om.joined_at DESC
        ");
        $stmt->execute([$orgId]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $error = $e->getMessage();
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
    <title>Autodesk | Organisation Owner Dashboard</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="page-container">
        <aside class="sidebar">
            <h3><a href="org-dashboard.php"><?= $orgName ?></a></h3>
            <ul>
                <li><a href="#overview">Overview</a></li>
                <li><a href="#members">Members</a></li>
                <li><a href="#projects">Projects</a></li>
                <li><a href="#settings">Settings</a></li>
            </ul>

            <h3>Quick Actions</h3>
            <ul>
                <li><a href="#invite-member" class="open-modal">Invite Member</a></li>
                <li><a href="create-project.php">Create Project</a></li>
                <li><a href="#manage-roles">Manage Roles</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="org-header">
                <div>
                    <h1><?= $orgName ?></h1>
                    <?php if (!empty($orgDescription)): ?>
                        <p class="org-description"><?= $orgDescription ?></p>
                    <?php endif; ?>
                </div>
                <div class="org-actions">
                    <button class="btn-primary open-modal">Invite Members</button>
                    <a href="create-project.php" class="btn-secondary">Create Project</a>
                </div>
            </div>

            <section id="overview" class="org-section">
                <h2>Organisation Overview</h2>
                <div class="org-stats">
                    <div class="stat-card">
                        <h3>Total Members</h3>
                        <p><?= $totalMembers ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Active Projects</h3>
                        <p><?= $activeProjects ?></p>
                    </div>
                </div>
            </section>

            <section id="roles" class="org-section">
                <h2>Organisation Roles & Permissions</h2>
                <div class="roles-container">
                    <div class="role-card">
                        <h3>Organisation Owner</h3>
                        <ul class="permissions-list">
                            <li>Full control over organisation settings</li>
                            <li>Create/edit/delete organisation</li>
                            <li>Transfer ownership</li>
                            <li>Manage all members and roles</li>
                            <li>Create organisation-level projects</li>
                        </ul>
                    </div>
                    
                    <div class="role-card">
                        <h3>Organisation Admin</h3>
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
                    <input type="text" placeholder="Search members..." id="memberSearch">
                    <select id="roleFilter">
                        <option value="all">All Roles</option>
                        <option value="1">Owner</option>
                        <option value="2">Admin</option>
                        <option value="3">Member</option>
                    </select>
                    <button id="filterButton">Filter</button>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <div class="members-table">
                    <table id="membersTable">
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
                            <?php if (!empty($members)): ?>
                                <?php foreach ($members as $member): ?>
                                    <tr data-role-id="<?= $member['role_id'] ?>">
                                        <td><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></td>
                                        <td><?= htmlspecialchars($member['email']) ?></td>
                                        <td><?= htmlspecialchars($member['role_name']) ?></td>
                                        <td><?= date('M j, Y', strtotime($member['joined_at'])) ?></td>
                                        <td>
                                            <button class="btn-small edit-member" 
                                                    data-user-id="<?= $member['user_id'] ?>"
                                                    data-current-role="<?= $member['role_name'] ?>">Edit</button>
                                            <button class="btn-small btn-danger remove-member"
                                                    data-user-id="<?= $member['user_id'] ?>">Remove</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No members found in this organisation</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>


            <section id="projects" class="org-section">
                <h2>Recent Projects</h2>
                <div class="preview-projects">
                    <?php if (!empty($projects)): ?>
                        <?php foreach ($projects as $project): ?>
                            <div class="project-card">
                                <h3><?= htmlspecialchars($project['project_name']) ?></h3>
                                <p><?= htmlspecialchars($project['description']) ?></p>
                                <div class="project-meta">
                                    <span>Created: <?= date('M j, Y', strtotime($project['created_at'])) ?></span>
                                    <span>Members: <?= $project['member_count'] ?></span>
                                </div>
                                <a href="project.php?id=<?= $project['project_id'] ?>" class="btn-small">View Project</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No projects yet. <a href="create-project.php">Create your first project</a></p>
                    <?php endif; ?>
                </div>
                <div class="view-all">
                    <a href="org-projects.php" class="btn-secondary">View All Projects</a>
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
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php elseif (!empty($error)): ?>
                    <div class="alert alert-error"><?= $error ?></div>
                <?php endif; ?>
                
                <form id="addMemberForm" method="POST" action="">
                    <input type="hidden" name="add_member" value="1">
                    
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required 
                               value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required
                               value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        <small class="form-hint">The member will use this to log in</small>
                    </div>

                    
                    
                    <div class="form-group">
                        <label for="org_role_id">Role *</label>
                        <select id="org_role_id" name="org_role_id" required>
                            <option value="1" disabled>Organisation Owner</option>
                            <option value="2">Organisation Admin</option>
                            <option value="3" selected>Organisation Member</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="set_custom_password" name="set_custom_password" 
                                   <?= isset($_POST['set_custom_password']) ? 'checked' : '' ?>>
                            Set custom password (optional)
                        </label>
                        <small class="form-hint">If unchecked, a random password will be generated</small>
                    </div>
                    
                    <div class="form-group password-field" style="display: <?= isset($_POST['set_custom_password']) ? 'block' : 'none' ?>">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" 
                               placeholder="At least 8 characters" 
                               minlength="8"
                               value="<?= htmlspecialchars($_POST['password'] ?? '') ?>">
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-secondary close-modal">Cancel</button>
                        <button type="submit" class="btn-primary">Add Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
                document.getElementById('filterButton').addEventListener('click', function() {
            const searchTerm = document.getElementById('memberSearch').value.toLowerCase();
            const roleFilter = document.getElementById('roleFilter').value;
            const rows = document.querySelectorAll('#membersTable tbody tr');
            
            rows.forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                const email = row.cells[1].textContent.toLowerCase();
                const role = row.cells[2].textContent.toLowerCase();
                const roleId = row.getAttribute('data-role-id');
                
                const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
                const matchesRole = roleFilter === 'all' || roleId === roleFilter;
                
                row.style.display = (matchesSearch && matchesRole) ? '' : 'none';
            });
        });


        document.querySelectorAll('.open-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('addMemberModal').style.display = 'block';
            });
        });

        document.querySelector('.close-modal').addEventListener('click', () => {
            document.getElementById('addMemberModal').style.display = 'none';
        });

        document.getElementById('set_custom_password').addEventListener('change', function() {
            const passwordField = document.querySelector('.password-field');
            passwordField.style.display = this.checked ? 'block' : 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target === document.getElementById('addMemberModal')) {
                document.getElementById('addMemberModal').style.display = 'none';
            }
        });

document.querySelectorAll('.remove-member').forEach(btn => {
    btn.addEventListener('click', function() {
        const userId = this.getAttribute('data-user-id');
        if (confirm('Are you sure you want to remove this member from the organisation?')) {
            document.getElementById('removeUserId').value = userId;
            document.getElementById('removeMemberForm').submit();
        }
    });
});

    </script>

<form id="removeMemberForm" method="POST" style="display: none;">
    <input type="hidden" name="remove_member" value="1">
    <input type="hidden" name="user_id" id="removeUserId" value="">
</form>

    <?php include('include/footer.php'); ?>
</body>
</html> 