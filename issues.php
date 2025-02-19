<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/collaborators.css">
    <title>AutoDesk | Issues</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="page-container">
        <div class="issues-page-container">
            <div class="project-header">
                <h1 class="project-title">Project Name</h1>
                <p class="project-description">A brief description of the project.</p>
            </div>

            <div class="issues-container">
                <div class="issues-container-header">
                    <h4>Collaborators</h4>
                    <button id="add-issues-btn" class="add-issues-btn">Add Collaborator</button>
                </div>
                <div class="issues-container-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Profile</th>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php include('issues-row.php'); ?>
                            <?php include('issues-row.php'); ?>
                            <?php include('issues-row.php'); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="add-issues-modal" class="modal">
    <div class="issues-content">
        <span class="close-btn">&times;</span>
        <h2>Add Issues</h2>
        <form id="add-issues-form">
            <div class="form-group">
                <label for="username">Search by Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username">
            </div>
            <div class="form-group">
                <label for="email">Or Invite by Email</label>
                <input type="email" id="email" name="email" placeholder="Enter email">
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="administrator">Administrator</option>
                    <option value="project_manager">Project Manager</option>
                    <option value="editor">Editor</option>
                    <option value="viewer">Viewer</option>
                    <option value="contractor">Contractor</option>
                </select>
            </div>
            <div class="form-group" id="access-duration-group" style="display: none;">
                <label for="access-duration">Access Duration (days)</label>
                <input type="number" id="access-duration" name="access-duration" placeholder="Enter number of days">
            </div>
            <div class="form-group" id="file-access-group" style="display: none;">
                <label for="file-access">Select Files</label>
                <select id="file-access" name="file-access" multiple>

                <option value="file1">File 1</option>
                    <option value="file2">File 2</option>
                    <option value="file3">File 3</option>
                </select>
            </div>
            <button type="submit" class="submit-btn">Invite</button>
        </form>
    </div>
</div>

    <?php include('include/footer.php'); ?>

    <script src="js/add-collaborator.js"></script>
</body>
</html>

