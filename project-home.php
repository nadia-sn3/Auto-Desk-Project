<?php
require_once 'db/connection.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: signin.php");
    exit;
}

$filter_type = $_GET['filter'] ?? 'creation-date';
$search_query = $_GET['search'] ?? '';

$query = "SELECT DISTINCT p.* FROM Project p 
          LEFT JOIN project_members pm ON p.project_id = pm.project_id 
          WHERE p.created_by = :user_id OR pm.user_id = :user_id";

if (!empty($search_query)) {
    $search_term = "%$search_query%";
    $query .= " AND (p.project_name LIKE :search OR p.description LIKE :search)";
}

switch ($filter_type) {
    case 'last-modified':
        $order_by = "ORDER BY p.project_id DESC"; 
        break;
    case 'alphabetical':
        $order_by = "ORDER BY p.project_name ASC";
        break;
    case 'creation-date':
    default:
        $order_by = "ORDER BY p.project_id DESC";
        break;
}

$query .= " $order_by";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
if (!empty($search_query)) {
    $stmt->bindParam(':search', $search_term, PDO::PARAM_STR);
}
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/userhome.css">
    <link rel="stylesheet" href="style/preview.css">
    <script src="js/sidebar-toggle.js" defer></script>
    <title>Autodesk | Home</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="page-container">
        <aside class="sidebar">
            <h3><a href="project-home.php">Pre-Manufacturing Models</a></h3>
            <ul>
                <?php foreach ($projects as $project): ?>
                    <li><a href="view-project.php?project_id=<?= $project['project_id'] ?>"><?= htmlspecialchars($project['project_name']) ?></a></li>
                <?php endforeach; ?>
            </ul>

            <h3><a href="asset-library.php">Asset Library</a></h3>
            <ul>
                <li><a href="view-asset-model.php">Asset 1</a></li>
                <li><a href="view-asset-model.php">Asset 2</a></li>
                <li><a href="view-asset-model.php">Asset 3</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="projects-header">
                <h2>Projects</h2>
                <form class="filter-bar" method="get" action="">
                    <select name="filter">
                        <option value="creation-date" <?= $filter_type === 'creation-date' ? 'selected' : '' ?>>Creation Date</option>
                        <option value="last-modified" <?= $filter_type === 'last-modified' ? 'selected' : '' ?>>Last Modified</option>
                        <option value="alphabetical" <?= $filter_type === 'alphabetical' ? 'selected' : '' ?>>Alphabetical</option>
                    </select>
                    <input type="text" name="search" placeholder="Search projects..." value="<?= htmlspecialchars($search_query) ?>">
                    <button type="submit">Filter</button>
                </form>
            </div>

            <div class="preview-projects">
                <?php if (empty($projects)): ?>
                    <p>No projects found matching your criteria.</p>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <?php 
                            $project_name = $project['project_name'];
                            $description = $project['description'];
                            $project_id = $project['project_id'];
                            include('preview.php'); 
                        ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>