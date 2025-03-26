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

$stmt = $pdo->prepare("SELECT user_id, email, first_name, last_name, system_role_id FROM users ORDER BY user_id DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

                <div class="search-container">
                    <input type="text" id="userSearch" placeholder="Search users by name or email...">
                </div>

                <a href="create-user.php" class="btn-create">Create New User</a>
            </div>

            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr class="user-row">
                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                        <td class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td class="user-email"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo $user['system_role_id'] == 1 ? 'System Admin' : 'Regular User'; ?></td>
                        <td class="actions">
                            <a href="edit-user.php?id=<?php echo $user['user_id']; ?>" class="btn-edit">Edit</a>
                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                <a href="delete-user.php?id=<?php echo $user['user_id']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this user?');">
                                    Delete
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include('include/footer.php'); ?>

    <script src="js/search-users.js"></script>

</body>
</html>
