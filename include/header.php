<?php 
session_start();
require_once 'db/connection.php';

if (isset($_SESSION['user_id']) && (!isset($_SESSION['first_name']) || !isset($_SESSION['is_admin']))) {
    $stmt = $pdo->prepare("SELECT first_name, system_role_id FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['is_admin'] = ($user['system_role_id'] == 1);
    }
}
?>

<link rel="stylesheet" href="style/header.css">
<link rel="stylesheet" href="style/create-modal.css">

<header class="header">
    <div class="header-content">
        <a href="home.php"> 
            <img src="images/Autodesk-header-logo.png" alt="Logo" class="logo" />
        </a>                
        <div class="nav-bar">
            <ul>
                <?php if (isset($_SESSION['user_id'])): 
                    if (!isset($_SESSION['first_name']) || !isset($_SESSION['is_admin'])) {
                        $stmt = $pdo->prepare("SELECT first_name, system_role_id FROM users WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $user = $stmt->fetch();
                        $_SESSION['first_name'] = $user['first_name'] ?? 'User';
                        $_SESSION['is_admin'] = ($user['system_role_id'] == 1);
                    }
                    
                    $isAdmin = $_SESSION['is_admin'] ?? false;
                ?>
                    <?php if (!$isAdmin): ?>
                        <li><a href="create-project.php">Create</a></li>
                        <li><a href="project-home.php">Projects</a></li>

                    <?php else: ?>
                        <li><a href="system-admin-home.php">Admin Dashboard</a></li>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['current_org_id'])): ?>
                        <li><a href="org-owner-home.php">Organisation Dashboard</a></li>
                    <?php endif; ?>
                    
                    <?php if ($isAdmin): ?>
                        <li><a href="manage-users.php">Manage Users</a></li>
                    <?php endif; ?>

                    <li><a href="logout.php">Log Out</a></li>

                    <li class="welcome-message">
                        <a href="#">
                            Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>
                            <?php if ($isAdmin): ?>
                                <span class="admin-badge">(Admin)</span>
                            <?php endif; ?>
                        </a>
                    </li>

                <?php else: ?>
                    <li><a href="signup.php" class="redirect-to-signup">Create</a></li>
                    <li><a href="signup.php" class="redirect-to-signup">Projects</a></li>
                    <li><a href="signin.php">Sign In</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</header>
