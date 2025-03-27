<?php
require_once 'backend/Business_Logic/Function/config.php';
require_once 'db/connection.php';

try {
    $project_id = $_GET['project_id'] ?? null;
    if (!$project_id) {
        die("Project ID missing!");
    }

    $sql = "SELECT * FROM Project WHERE project_id = :project_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->execute();
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, pr.role_name 
            FROM project_members pm
            JOIN users u ON pm.user_id = u.user_id
            JOIN project_roles pr ON pm.project_role_id = pr.project_role_id
            WHERE pm.project_id = :project_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->execute();
    $collaborators = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdo = null;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/collaborators.css">
    <title>Autodesk | Collaborators</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="page-container">
        <div class="collaborators-page-container">
            <div class="project-header">
                <h1 class="project-title"><?php echo htmlspecialchars($project['project_name']); ?></h1>
                <p class="project-description"><?php echo htmlspecialchars($project['description']); ?></p>
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
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($collaborators as $collaborator): ?>
                                <tr data-user-id="<?php echo $collaborator['user_id']; ?>">
                                    <td><div class="profile-circle"><?php echo strtoupper(substr($collaborator['first_name'], 0, 1) . substr($collaborator['last_name'], 0, 1)); ?></div></td>
                                    <td><?php echo htmlspecialchars($collaborator['first_name'] . ' ' . $collaborator['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($collaborator['email']); ?></td>
                                    <td><?php echo htmlspecialchars($collaborator['role_name']); ?></td>
                                    <td>
                                        <button class="action-btn edit-btn">Edit</button>
                                        <button class="action-btn remove-btn">Remove</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($collaborators)): ?>
                            <tr>
                                <td colspan="5">No collaborators found for this project.</td>
                            </tr>
                            <?php endif; ?>
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
            <form id="collaborator-form" method="POST" action="backend/add_collaborator.php">
                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">

                <div class="form-group">
                    <label for="collaborator-email">Add by Email</label>
                    <input type="email" id="collaborator-email" name="collaborator-email" placeholder="Enter email">
                    <div id="email-suggestions" class="suggestions"></div>
                </div>
                <div class="form-group">
                    <label for="collaborator-role">Role</label>
                    <select id="collaborator-role" name="collaborator-role">
                        <option value="2">Project Manager</option>
                        <option value="3">Editor</option>
                        <option value="4">Viewer</option>
                        <option value="5">Contractor</option>
                    </select>
                </div>
                <button type="submit" class="submit-btn">Invite</button>
            </form>
        </div>
    </div>

    <?php include('include/footer.php'); ?>


    <script src="js/remove-collaborator.js"></script>
    <script src="js/add-collaborator-modal.js"></script>
    <script src="js/user-search.js"></script>
</body>
</html>