<?php
require 'db/connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}
$userId = $_SESSION['user_id'];

$successMessage = '';
if (isset($_GET['new']) && $_GET['new'] == 1) {
    $successMessage = "Congratulations! Your organisation has been created successfully.";
}

$stmt = $pdo->prepare("SELECT org_name, description FROM organisations WHERE org_id = ?");
$stmt->execute([$_SESSION['current_org_id']]);
$org = $stmt->fetch();
$orgName = $org['org_name'];
$orgDescription = $org['description'];

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
                throw new Exception("User already in organization");
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO organisation_members 
                (org_id, user_id, org_role_id, joined_at, invited_by) 
                VALUES (?, ?, ?, NOW(), ?)
            ");
            $invitedBy = $_SESSION['user_id'] ?? 1;
            $stmt->execute([$orgId, $userId, $roleId, $invitedBy]);
            
            $success = "User added to organization";
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
            $success = "New user created and added to organization";
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
    <title>Autodesk | <?= $orgName ?> Dashboard</title>
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
                <?php if ($_SESSION['current_org_role_id'] == 1): ?>
                    <li><a href="#settings">Settings</a></li>
                <?php endif; ?>
            </ul>

            <h3>Quick Actions</h3>
            <ul>
                <?php if ($_SESSION['current_org_role_id'] <= 2): ?>
                    <li><a href="#invite-member" class="open-modal">Invite Member</a></li>
                    <?php endif; ?>
                <li><a href="create-project.php">Create Project</a></li>
                <?php if ($_SESSION['current_org_role_id'] == 1): ?>
                    <li><a href="manage-roles.php">Manage Roles</a></li>
                <?php endif; ?>
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
                    <?php if ($_SESSION['current_org_role_id'] <= 2): ?>
                        <button class="btn-primary open-modal">Invite Members</button>
                    <?php endif; ?>
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
                    <div class="stat-card">
                        <h3>Your Role</h3>
                        <p><?= $_SESSION['current_org_role_name'] ?></p>
                    </div>
                </div>
            </section>

            <section id="roles" class="org-section">
                <h2>Organisation Roles & Permissions</h2>
                <div class="roles-container">
                    <?php foreach ($orgRoles as $role): ?>
                    <div class="role-card">
                        <h3><?= $role['role_name'] ?></h3>
                        <ul class="permissions-list">
                            <?php 
                            $permissions = json_decode($role['permissions'], true);
                            foreach ($permissions as $permission): 
                            ?>
                                <li><?= ucfirst(str_replace('.', ' ', $permission)) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section id="members" class="org-section">
                <h2>Recent Members</h2>
                <div class="members-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <?php if ($_SESSION['current_org_role_id'] <= 2): ?>
                                    <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                            <tr>
                                <td><?= $member['first_name'] . ' ' . $member['last_name'] ?></td>
                                <td><?= $member['email'] ?></td>
                                <td><?= $member['role_name'] ?></td>
                                <td><?= date('M j, Y', strtotime($member['joined_at'])) ?></td>
                                <?php if ($_SESSION['current_org_role_id'] <= 2): ?>
                                    <td>
                                        <?php if ($_SESSION['current_org_role_id'] == 1 || $member['org_role_id'] > $_SESSION['current_org_role_id']): ?>
                                            <button class="btn-small edit-member" 
                                                    data-user-id="<?= $member['user_id'] ?>">Edit</button>
                                            <button class="btn-small btn-danger remove-member"
                                                    data-user-id="<?= $member['user_id'] ?>">Remove</button>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="view-all">
                    <a href="org-members.php" class="btn-secondary">View All Members</a>
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


    </script>

    <?php include('include/footer.php'); ?>
</body>
</html>