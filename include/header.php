<?php session_start(); ?>
<link rel="stylesheet" href="style/header.css">
<link rel="stylesheet" href="style/create-modal.css">

<header class="header">
    <div class="header-content">
        <a href="home.php"> 
            <img src="images/Autodesk-header-logo.png" alt="Logo" class="logo" />
        </a>                
        <div class="search-bar">
            <input type="text" placeholder="Search...">
        </div>
        <div class="nav-bar">
            <ul>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="create-project.php">Create</a></li>
                    <li><a href="project-home.php">Projects</a></li>
                    <li><a href="logout.php">Log Out</a></li>
                    <li><a href="project-home.php">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?></a></li>
                <?php else: ?>
                    <li><a href="signup.php" class="redirect-to-signup">Create</a></li>
                    <li><a href="signup.php" class="redirect-to-signup">Projects</a></li>
                    <li><a href="signin.php">Sign In</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</header>
