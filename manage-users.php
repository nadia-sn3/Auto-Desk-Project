<?php
session_start();
require 'db/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$stmt = $pdo->prepare("SELECT system_role_id FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['system_role_id'] != 1) {
    header("Location: unauthorized.php");
    exit();
}

$stmt = $pdo->prepare("SELECT user_id, email, first_name, last_name, system_role_id FROM users ORDER BY system_role_id DESC, user_id DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$regularUsers = array_filter($users, function($user) {
    return $user['system_role_id'] != 1;
});

$adminUsers = array_filter($users, function($user) {
    return $user['system_role_id'] == 1;
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/manage-users.css">
</head>
<body>

    <?php include('include/header.php'); ?>


    <div class="page-container">
        <div class="manage-users-container">
            <div class="manage-users-header">
                <h4>Manage Users</h4>
                <a href="create-user.php" class="btn-create">Create New User</a>
            </div>

            <div class="user-section admin-section">
                <div class="section-header">
                    <h3 class="section-title">Administrators</h3>
                    <div class="search-container">
                        <input type="text" id="adminSearch" placeholder="Search administrators..." class="search-input">
                    </div>
                </div>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adminTableBody">
                        <?php foreach ($adminUsers as $user): ?>
                        <tr class="user-row">
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td class="user-email"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="actions">
                                <a href="edit-user.php?id=<?php echo $user['user_id']; ?>" class="btn-edit">Edit</a>
                                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                    <a href="#" class="btn-delete" onclick="confirmDelete(event, <?php echo $user['user_id']; ?>)">
                                        Delete
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="user-section regular-section">
                <div class="section-header">
                    <h3 class="section-title">Regular Users</h3>
                    <div class="search-container">
                        <input type="text" id="regularSearch" placeholder="Search regular users..." class="search-input">
                    </div>
                </div>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="regularTableBody">
                        <?php foreach ($regularUsers as $user): ?>
                        <tr class="user-row">
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td class="user-email"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="actions">
                                <a href="edit-user.php?id=<?php echo $user['user_id']; ?>" class="btn-edit">Edit</a>
                                <a href="#" class="btn-delete" onclick="confirmDelete(event, <?php echo $user['user_id']; ?>)">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3>Are you sure you want to delete this user?</h3>
            <p>This action cannot be undone!</p>
            <div class="modal-buttons">
                <button id="confirmDelete" class="confirm-btn">Yes, delete it!</button>
                <button id="cancelDelete" class="cancel-btn">Cancel</button>
            </div>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
    <script src="js/search-users.js"></script>
    <script src="js/manage-users.js"></script>



</body>

<?php
if (isset($_SESSION['success'])) {
    echo '<div class="alert success">'.htmlspecialchars($_SESSION['success']).'</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert error">'.htmlspecialchars($_SESSION['error']).'</div>';
    unset($_SESSION['error']);
}
?>



</html>