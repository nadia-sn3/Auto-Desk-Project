<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autodesk | Create Project</title>
    <link rel="stylesheet" href="style/create-project.css">
</head>
<body>
<?php include('include/header.php'); ?>

    <div class="page-container">
        <div class="create-project-box">
            <h2>Create Project</h2>
            <form action="view-project.php" method="POST" enctype="multipart/form-data" id="create-project-form">
            <input type="hidden" name="project_id" value="<?= $project_id; ?>" />

            <div class="input-group">
                    <label for="project-name">Project Name</label>
                    <input type="text" id="project-name" name="project-name" placeholder="Enter project name" required>
                </div>

                <div class="input-group">
                    <label for="project-description">Description</label>
                    <textarea id="project-description" name="project-description" placeholder="Enter a short description" rows="3" required></textarea>
                </div>

                <div class="input-group">
                    <label for="invite-users">Invite Users</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="invite-users" name="invite-users" placeholder="Enter username or email">
                        <button type="button" class="add-user-btn">Add</button>
                    </div>
                </div>

                <div id="user-roles-section" class="input-group">
                </div>

                <button type="submit" class="create-btn">Create Project</button>
            </form>
        </div>
    </div>

    <script>
        document.querySelector('.add-user-btn').addEventListener('click', function() {
            const userInput = document.getElementById('invite-users').value.trim();
            if (userInput) {
                const userRolesSection = document.getElementById('user-roles-section');
                const newUserDiv = document.createElement('div');
                newUserDiv.className = 'user-role-group';
                newUserDiv.innerHTML = `
                    <label>${userInput}</label>
                    <select name="role-${userInput}">
                        <option value="project-manager">Project Manager</option>
                        <option value="editor">Editor</option>
                        <option value="viewer">Viewer</option>
                    </select>
                `;
                userRolesSection.appendChild(newUserDiv);
                document.getElementById('invite-users').value = '';
            }
        });
    </script>
    <?php include('include/footer.php'); ?>

</body>
</html>