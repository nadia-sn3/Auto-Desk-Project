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

    <?php include('include/footer.php'); ?>
</body>
</html>