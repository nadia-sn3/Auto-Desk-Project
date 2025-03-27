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

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, email, system_role_id FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();

    if (!$userData) {
        header("Location: manage-users.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $systemRoleId = $_POST['system_role_id']; 

    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, system_role_id = ? WHERE user_id = ?");
    $stmt->execute([$firstName, $lastName, $email, $systemRoleId, $userId]);

    header("Location: manage-users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/edit-user.css">
</head>
<body>

    <?php include('include/header.php'); ?>
    <div class="page-container">
    <div class="manage-users-container">
        <div class="manage-users-header">
            <h4>Edit User</h4>
            <a href="manage-users.php" class="btn-back">Back to Users</a>
        </div>

        <form method="POST" action="edit-user.php?id=<?php echo $userData['user_id']; ?>" class="edit-user-form">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($userData['first_name']); ?>" required>

            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($userData['last_name']); ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>

            <label for="system_role_id">Role</label>
            <select id="system_role_id" name="system_role_id">
                <option value="1" <?php echo $userData['system_role_id'] == 1 ? 'selected' : ''; ?>>Administrator</option>
                <option value="2" <?php echo $userData['system_role_id'] == 2 ? 'selected' : ''; ?>>Regular User</option>
            </select>

            <button type="submit" class="btn-save">Save Changes</button>
        </form>

        <div class="reset-password-container">
            <h3>Reset Password</h3>
            <p>If you wish to reset the password for this user, click the button below. A password reset link will be sent to their email address.</p>
            <form action="reset-password.php?id=<?php echo $userData['user_id']; ?>" method="POST" class="reset-password-form">
                <button type="submit" class="btn-reset-password">Send Reset Link</button>
            </form>
        </div>

    </div>
</div>

    <?php include('include/footer.php'); ?>
    <script src="js/manage-users.js"></script>

</body>
</html>
