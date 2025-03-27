<?php
require 'db/connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

if (!isset($_SESSION['current_org_id'])) {
    header("Location: userhome.php");
    exit();
}

$userId = $_SESSION['user_id'];
$orgId = $_SESSION['current_org_id'];

$stmt = $pdo->prepare("SELECT org_name, description FROM organisations WHERE org_id = ?");
$stmt->execute([$orgId]);
$org = $stmt->fetch();
$orgName = $org['org_name'];
$orgDescription = $org['description'];

$membersQuery = "
    SELECT u.user_id, u.first_name, u.last_name, u.email, 
           orm.joined_at, orm.org_role_id, or.role_name
    FROM organisation_members orm
    JOIN users u ON orm.user_id = u.user_id
    JOIN organisation_roles or ON orm.org_role_id = or.org_role_id
    WHERE orm.org_id = ?
    ORDER BY orm.org_role_id, orm.joined_at DESC
";
$stmt = $pdo->prepare($membersQuery);
$stmt->execute([$orgId]);
$members = $stmt->fetchAll();

$totalMembers = count($members);
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
    <title>Autodesk | <?= htmlspecialchars($orgName) ?> Members</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="page-container">
        <aside class="sidebar">
            <h3><a href="org-dashboard.php"><?= htmlspecialchars($orgName) ?></a></h3>
            <ul>
                <li><a href="org-dashboard.php#overview">Overview</a></li>
                <li><a href="org-members.php">Members</a></li>
                <li><a href="org-dashboard.php#projects">Projects</a></li>
                <?php if ($_SESSION['current_org_role_id'] == 1): ?>
                    <li><a href="org-dashboard.php#settings">Settings</a></li>
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
                    <h1><?= htmlspecialchars($orgName) ?></h1>
                    <?php if (!empty($orgDescription)): ?>
                        <p class="org-description"><?= htmlspecialchars($orgDescription) ?></p>
                    <?php endif; ?>
                </div>
                <div class="org-actions">
                    <?php if ($_SESSION['current_org_role_id'] <= 2): ?>
                        <button class="btn-primary open-modal">Invite Members</button>
                    <?php endif; ?>
                    <a href="create-project.php" class="btn-secondary">Create Project</a>
                </div>
            </div>

            <section class="org-section">
                <div class="section-header">
                    <h2>All Members (<?= $totalMembers ?>)</h2>
                    <?php if ($_SESSION['current_org_role_id'] <= 2): ?>
                        <button class="btn-small open-modal">Invite New Member</button>
                    <?php endif; ?>
                </div>

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
                                <td><?= htmlspecialchars($member['first_name'] . ' ' . htmlspecialchars($member['last_name']) ?></td>
                                <td><?= htmlspecialchars($member['email']) ?></td>
                                <td><?= htmlspecialchars($member['role_name']) ?></td>
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
            </section>
        </main>
    </div>


    <?php if ($_SESSION['current_org_role_id'] <= 2): ?>
    <div id="invite-member" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Invite New Member</h2>
            <form id="invite-member-form" action="process_invite.php" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <?php 
                        $rolesQuery = "SELECT * FROM organisation_roles WHERE org_role_id >= ? ORDER BY org_role_id";
                        $stmt = $pdo->prepare($rolesQuery);
                        $minRole = $_SESSION['current_org_role_id'];
                        $stmt->execute([$minRole]);
                        $roles = $stmt->fetchAll();
                        
                        foreach ($roles as $role): 
                        ?>
                            <option value="<?= $role['org_role_id'] ?>"><?= $role['role_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Send Invitation</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div id="edit-member" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Edit Member Role</h2>
            <form id="edit-member-form" action="process_edit_member.php" method="POST">
                <input type="hidden" id="edit-user-id" name="user_id">
                <div class="form-group">
                    <label for="edit-role">Role</label>
                    <select id="edit-role" name="role" required>
                        <?php 
                        foreach ($roles as $role): 
                            if ($_SESSION['current_org_role_id'] == 1 || $role['org_role_id'] > $_SESSION['current_org_role_id']):
                        ?>
                            <option value="<?= $role['org_role_id'] ?>"><?= $role['role_name'] ?></option>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

    <?php include('include/footer.php'); ?>

    <script>
        document.querySelectorAll('.open-modal').forEach(button => {
            button.addEventListener('click', function() {
                const modalId = this.getAttribute('href') || 'invite-member';
                document.getElementById(modalId).style.display = 'block';
            });
        });

        document.querySelectorAll('.close-modal').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.modal').style.display = 'none';
            });
        });

        document.querySelectorAll('.edit-member').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                document.getElementById('edit-user-id').value = userId;
                document.getElementById('edit-member').style.display = 'block';
            });
        });

        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        });
    </script>
</body>
</html>