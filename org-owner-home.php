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
                    <input type="text" placeholder="Search members...">
                    <select>
                        <option value="all">All Roles</option>
                        <option value="owner">Owner</option>
                        <option value="admin">Admin</option>
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
                                <td><?= $member['first_name'] . ' ' . $member['last_name'] ?></td>
                                <td><?= $member['email'] ?></td>
                                <td><?= $member['role_name'] ?></td>
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