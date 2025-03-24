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