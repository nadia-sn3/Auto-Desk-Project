<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/collaborators.css">
    <title>AutoDesk | Collaborators</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="page-container">
        <div class="collaborators-page-container">
            <div class="project-header">
                <h1 class="project-title">Project Name</h1>
                <p class="project-description">A brief description of the project.</p>
            </div>

            <div class="collaborators-container">
                <div class="collaborators-container-header">
                    <h4>Collaborators</h4>
                    <button id="add-collaborator-btn" class="add-collaborator-btn">Add Collaborator</button>
                </div>
                <div class="collaborators-container-table">
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
                            <?php include('collaborator-row.php'); ?>
                            <?php include('collaborator-row.php'); ?>
                            <?php include('collaborator-row.php'); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<div id="collaboratorModal" class="modal">

    <div class="modal-content">

        <span class="close-btn">&times;</span>
        <h2>Add Collaborator</h2>

        <form id="collaborator-form">
            <div class="form-group">
                <label for="collaborator-username">Search by Username</label>
                <input type="text" id="collaborator-username" name="collaborator-username" placeholder="Enter username">
            </div>
            <div class="form-group">
                <label for="collaborator-email">Or Invite by Email</label>
                <input type="email" id="collaborator-email" name="collaborator-email" placeholder="Enter email">
            </div>
            <div class="form-group">
                <label for="collaborator-role">Role</label>
                <select id="collaborator-role" name="collaborator-role">
                    <option value="administrator">Administrator</option>
                    <option value="project_manager">Project Manager</option>
                    <option value="editor">Editor</option>
                    <option value="viewer">Viewer</option>
                    <option value="contractor">Contractor</option>
                </select>
            </div>

            <div class="form-group" id="collaborator-access-duration-group" style="display: none;">
                <label for="collaborator-access-duration">Access Duration (days)</label>
                <input type="number" id="collaborator-access-duration" name="collaborator-access-duration" placeholder="Enter number of days">
            </div>

            <div class="form-group" id="collaborator-file-access-group" style="display: none;">
                <label for="collaborator-file-access">Select Files</label>
                <select id="collaborator-file-access" name="collaborator-file-access" multiple>
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

