<?php
session_start();
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: signin.php");
    exit();
}
require 'db/connection.php';

$stmt = $pdo->prepare("SELECT id, username, email, role_id FROM users ORDER BY id DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/manage-users.css">
    <title>Manage Users</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="dashboard-container">
        <h1>Manage Users</h1>

        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo $user['role_id'] == 1 ? 'Admin' : 'User'; ?></td>
                    <td>
                        <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn-edit">Edit</a>
                        <a href="delete-user.php?id=<?php echo $user['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>
