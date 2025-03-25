<?php
require 'db/connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$userId = $_SESSION['user_id'];
$orgId = $_SESSION['current_org_id'] ?? null;

if ($orgId) {
    $stmt = $pdo->prepare("SELECT role_id FROM organisation_members WHERE org_id = ? AND user_id = ?");
    $stmt->execute([$orgId, $userId]);
    $userRole = $stmt->fetchColumn();

    if ($userRole == 3) { 
        header("Location: org-admin-home.php");
        exit();
    } elseif ($userRole == 5) { 
        header("Location: org-member-home.php");
        exit();
    } elseif ($userRole != 2) {
        header("Location: project-home.php");
        exit();
    }
    
    $_SESSION['org_role'] = $userRole;
} else {
    header("Location: project-home.php");
    exit();
}

$orgName = "My Organisation"; 
$stmt = $pdo->prepare("SELECT org_name FROM organisations WHERE org_id = ?");
$stmt->execute([$orgId]);
if ($stmt->rowCount() > 0) {
    $org = $stmt->fetch();
    $orgName = htmlspecialchars($org['org_name']);
}

$totalMembers = 0;
$activeProjects = 0;

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM organisation_members WHERE org_id = ?");
$stmt->execute([$orgId]);
$result = $stmt->fetch();
$totalMembers = $result['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM projects WHERE org_id = ?");
$stmt->execute([$orgId]);
$result = $stmt->fetch();
$activeProjects = $result['count'];


$success = '';
$error = '';

$stmt = $pdo->prepare("
    SELECT u.user_id, u.first_name, u.last_name, u.email, r.role_name, om.joined_at 
    FROM organisation_members om
    JOIN users u ON om.user_id = u.user_id
    JOIN roles r ON om.role_id = r.role_id
    WHERE om.org_id = ?
    ORDER BY om.role_id, u.last_name, u.first_name
");
$stmt->execute([$orgId]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
    try {
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $roleId = (int)$_POST['role_id'];
        $password = $_POST['password'] ?? '';
        
        if (empty($firstName) || empty($lastName) || empty($email)) {
            throw new Exception("All fields are required");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("A user with this email already exists");
        }
        
        $stmt = $pdo->prepare("SELECT om.org_member_id 
                              FROM organisation_members om
                              JOIN users u ON om.user_id = u.user_id
                              WHERE om.org_id = ? AND u.email = ?");
        $stmt->execute([$orgId, $email]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("This user is already a member of your organization");
        }
        
        if (empty($password)) {
            $password = bin2hex(random_bytes(4));
        } else {
            if (strlen($password) < 8) {
                throw new Exception("Password must be at least 8 characters");
            }
        }
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO users (role_id, email, password_hash, first_name, last_name) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([2, $email, $passwordHash, $firstName, $lastName]);
        $userId = $pdo->lastInsertId();
        
        // Add to organization
        $stmt = $pdo->prepare("INSERT INTO organisation_members (org_id, user_id, role_id, invited_by) 
                              VALUES (?, ?, ?, ?)");
        $stmt->execute([$orgId, $userId, $roleId, $_SESSION['user_id']]);
        
        $pdo->commit();
        
        // Send welcome email (function to be implemented)
        
        $_SESSION['success'] = "Member added successfully!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
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
    <title>Autodesk | Organisation</title>
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
            <p><?php echo $totalMembers; ?></p>
        </div>
        <div class="stat-card">
            <h3>Active Projects</h3>
            <p><?php echo $activeProjects; ?></p>
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
                <?php foreach ($members as $member): ?>
                <tr>
                    <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                    <td><?php echo htmlspecialchars($member['role_name']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($member['joined_at'])); ?></td>
                    <td>
                        <?php if ($userRole == 2 || ($userRole == 3 && $member['role_id'] > 3)): ?>
                            <button class="btn-small">Edit</button>
                            <button class="btn-small btn-danger">Remove</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
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
            <h3>Add New Staff Member</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php elseif (!empty($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form id="addMemberForm" method="POST" action="">
                <input type="hidden" name="org_id" value="<?php echo $orgId; ?>">
                <input type="hidden" name="add_member" value="1">
                
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required 
                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" required
                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    <small class="form-hint">The staff member will use this to log in</small>
                </div>
                
                <div class="form-group">
                    <label for="role_id">Role *</label>
                    <select id="role_id" name="role_id" required>
                        <option value="3" <?php echo (isset($_POST['role_id']) && $_POST['role_id'] == 3) ? 'selected' : ''; ?>>Organisation Admin</option>
                        <option value="4" <?php echo (isset($_POST['role_id']) && $_POST['role_id'] == 4) ? 'selected' : ''; ?>>Organisation Manager</option>
                        <option value="5" <?php echo (!isset($_POST['role_id']) || $_POST['role_id'] == 5) ? 'selected' : ''; ?>>Team Member</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="set_custom_password" name="set_custom_password" 
                               <?php echo isset($_POST['set_custom_password']) ? 'checked' : ''; ?>>
                        Set custom password (optional)
                    </label>
                    <small class="form-hint">If unchecked, a random password will be generated</small>
                </div>
                
                <div class="form-group password-field" style="display: <?php echo isset($_POST['set_custom_password']) ? 'block' : 'none'; ?>">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" 
                           placeholder="At least 8 characters" 
                           minlength="8"
                           value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>">
                    <small class="form-hint">Leave blank to generate a random password</small>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary close-modal">Cancel</button>
                    <button type="submit" class="btn-primary">Create Staff Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
    
<script>
    document.getElementById('set_custom_password').addEventListener('change', function() {
        const passwordField = document.querySelector('.password-field');
        passwordField.style.display = this.checked ? 'block' : 'none';
    });
</script>

<script src="js/sidebar-toggle.js" defer></script>
<script src="js/add-organisation-member.js" defer></script>

    <?php include('include/footer.php'); ?>
</body>
</html>