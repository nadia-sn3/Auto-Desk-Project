<?php 
session_start();
require_once 'db/connection.php';
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
                    if (!isset($_SESSION['first_name'])) {
                        $stmt = $pdo->prepare("SELECT first_name FROM users WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $user = $stmt->fetch();
                        $_SESSION['first_name'] = $user['first_name'] ?? 'User';
                    }
                ?>
                    <li><a href="create-project.php">Create</a></li>
                    <li><a href="project-home.php">Projects</a></li>
                    <?php if (isset($_SESSION['current_org_id'])): ?>
                        <li><a href="org-owner-home.php">Organisation Page</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Log Out</a></li>
                    <li class="welcome-message">
                        <a href="profile.php">
                            Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>
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