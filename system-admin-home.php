<?php
session_start();
require 'db/connection.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['org_role'] != 1) {
    header("Location: signin.php");
    exit();
}

$stmt = $pdo->query("SELECT user_id, first_name, last_name, email, is_active FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/admin.css">
    <title>System Admin Dashboard</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="admin-container">
        <h2>System Admin Dashboard</h2>

        <div class="admin-section">
            <h3>Manage Users</h3>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?></td>
                            <td>
                                <a href="edit-user.php?user_id=<?php echo $user['user_id']; ?>">Edit</a> |
                                <a href="delete-user.php?user_id=<?php echo $user['user_id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-section">
            <h3>System Settings</h3>
            <p>Coming soon...</p>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>
