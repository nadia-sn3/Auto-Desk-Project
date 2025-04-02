<?php
require_once 'backend/Business_Logic/Function/config.php';
require_once 'db/connection.php';

session_start();

try {
    $project_id = $_GET['project_id'] ?? null;
    if (!$project_id) {
        die("Project ID missing!");
    }

    $current_user_id = $_SESSION['user_id'] ?? null;
    if (!$current_user_id) {
        die("User not logged in!");
    }

    $sql = "SELECT * FROM Project WHERE project_id = :project_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->execute();
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT pr.role_name, pr.project_role_id 
            FROM project_members pm
            JOIN project_roles pr ON pm.project_role_id = pr.project_role_id
            WHERE pm.project_id = :project_id AND pm.user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();
    $current_user_role = $stmt->fetch(PDO::FETCH_ASSOC);

    $is_manager_or_admin = ($current_user_role['project_role_id'] == 1 || $current_user_role['project_role_id'] == 2);

    $rows_per_page = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $rows_per_page;

    $role_filter = $_GET['role_filter'] ?? '';
    $search_term = $_GET['search'] ?? '';

    $sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, pr.role_name, pr.project_role_id 
            FROM project_members pm
            JOIN users u ON pm.user_id = u.user_id
            JOIN project_roles pr ON pm.project_role_id = pr.project_role_id
            WHERE pm.project_id = :project_id";

    if (!empty($role_filter)) {
        $sql .= " AND pr.project_role_id = :role_filter";
    }

    if (!empty($search_term)) {
        $sql .= " AND (u.first_name LIKE :search_term OR u.last_name LIKE :search_term OR u.email LIKE :search_term)";
        $search_term = "%$search_term%";
    }

    $count_sql = str_replace("SELECT u.user_id, u.first_name, u.last_name, u.email, pr.role_name, pr.project_role_id", 
                            "SELECT COUNT(*) as total", $sql);
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
    
    if (!empty($role_filter)) {
        $count_stmt->bindValue(':role_filter', $role_filter, PDO::PARAM_INT);
    }
    
    if (!empty($search_term)) {
        $count_stmt->bindValue(':search_term', $search_term, PDO::PARAM_STR);
    }
    
    $count_stmt->execute();
    $total_rows = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_rows / $rows_per_page);

    $sql .= " LIMIT :offset, :rows_per_page";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':rows_per_page', $rows_per_page, PDO::PARAM_INT);
    
    if (!empty($role_filter)) {
        $stmt->bindValue(':role_filter', $role_filter, PDO::PARAM_INT);
    }
    
    if (!empty($search_term)) {
        $stmt->bindValue(':search_term', $search_term, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $collaborators = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql_roles = "SELECT * FROM project_roles";
    $stmt_roles = $pdo->query($sql_roles);
    $all_roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

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
                    <div class="table-controls">
                        <form method="GET" class="search-form">
                            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                            <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            <button type="submit">Search</button>
                        </form>
                        <select id="role-filter" class="role-filter">
                            <option value="">All Roles</option>
                            <?php foreach ($all_roles as $role): ?>
                                <option value="<?php echo $role['project_role_id']; ?>" <?php echo ($role_filter == $role['project_role_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($is_manager_or_admin): ?>
                            <button id="add-collaborator-btn" class="add-collaborator-btn">Add Collaborator</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="collaborators-container-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Profile</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <?php if ($is_manager_or_admin): ?>
                                    <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($collaborators as $collaborator): ?>
                                <tr data-user-id="<?php echo $collaborator['user_id']; ?>">
                                    <td><div class="profile-circle"><?php echo strtoupper(substr($collaborator['first_name'], 0, 1) . substr($collaborator['last_name'], 0, 1)); ?></div></td>
                                    <td><?php echo htmlspecialchars($collaborator['first_name'] . ' ' . $collaborator['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($collaborator['email']); ?></td>
                                    <td><?php echo htmlspecialchars($collaborator['role_name']); ?></td>
                                    <?php if ($is_manager_or_admin): ?>
                                        <td>
                                            <button class="action-btn edit-btn">Edit</button>
                                            <?php 
                                            $hide_remove = ($collaborator['project_role_id'] == 1) || 
                                                        ($collaborator['user_id'] == $current_user_id) ||
                                                        ($collaborator['project_role_id'] == 2 && $current_user_role['project_role_id'] != 1);
                                            
                                            if (!$hide_remove): ?>
                                                <button class="action-btn remove-btn" data-user-id="<?php echo $collaborator['user_id']; ?>">Remove</button>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($collaborators)): ?>
                            <tr>
                                <td colspan="<?php echo $is_manager_or_admin ? 5 : 4; ?>">No collaborators found for this project.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <!-- Pagination controls -->
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?project_id=<?php echo $project_id; ?>&page=<?php echo $page-1; ?><?php echo !empty($role_filter) ? '&role_filter='.$role_filter : ''; ?><?php echo !empty($search_term) ? '&search='.urlencode($_GET['search']) : ''; ?>">&laquo; Previous</a>
                        <?php endif; ?>
                        
                        <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?project_id=<?php echo $project_id; ?>&page=<?php echo $page+1; ?><?php echo !empty($role_filter) ? '&role_filter='.$role_filter : ''; ?><?php echo !empty($search_term) ? '&search='.urlencode($_GET['search']) : ''; ?>">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($is_manager_or_admin): ?>
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
    <?php endif; ?>

    <?php include('include/footer.php'); ?>

    <script src="js/remove-collaborator.js"></script>
    <script src="js/add-collaborator-modal.js"></script>
    <script src="js/user-search.js"></script>
    
    <script>

document.getElementById('role-filter').addEventListener('change', function() {
            const roleFilter = this.value;
            const searchTerm = "<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>";
            
            let url = `?project_id=<?php echo $project_id; ?>`;
            if (roleFilter) url += `&role_filter=${roleFilter}`;
            if (searchTerm) url += `&search=${encodeURIComponent(searchTerm)}`;
            
            window.location.href = url;
        });
        
        document.addEventListener('keydown', function(e) {
            const currentPage = <?php echo $page; ?>;
            const totalPages = <?php echo $total_pages; ?>;
            
            if (e.key === 'ArrowLeft' && currentPage > 1) {
                window.location.href = `?project_id=<?php echo $project_id; ?>&page=${currentPage - 1}<?php echo !empty($role_filter) ? '&role_filter='.$role_filter : ''; ?><?php echo !empty($search_term) ? '&search='.urlencode($_GET['search']) : ''; ?>`;
            } else if (e.key === 'ArrowRight' && currentPage < totalPages) {
                window.location.href = `?project_id=<?php echo $project_id; ?>&page=${currentPage + 1}<?php echo !empty($role_filter) ? '&role_filter='.$role_filter : ''; ?><?php echo !empty($search_term) ? '&search='.urlencode($_GET['search']) : ''; ?>`;
            }
        });
    </script>
</body>
</html>