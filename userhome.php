<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/userhome.css">
    <link rel="stylesheet" href="style/preview.css">

    <script src="js/sidebar-toggle.js" defer></script>

    <title>AutoDesk | Home</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="page-container">
        <aside class="sidebar">
            <h3>Pre-Manufacturing Models</h3>
            <ul>
                <li><a href="#">Model 1</a></li>
                <li><a href="#">Model 2</a></li>
                <li><a href="#">Model 3</a></li>
            </ul>

            <h3>Asset Library</h3>
            <ul>
                <li><a href="#">Asset 1</a></li>
                <li><a href="#">Asset 2</a></li>
                <li><a href="#">Asset 3</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="projects-header">
                <h2>My Projects</h2>
                <div class="filter-bar">
                    <select>
                        <option value="creation-date">Creation Date</option>
                        <option value="last-modified">Last Modified</option>
                        <option value="alphabetical">Alphabetical</option>
                    </select>
                    <input type="text" placeholder="Search projects...">
                    <button>Filter</button>
                </div>
            </div>

            <div class="preview-projects">
                <?php include('preview.php'); ?>
                <?php include('preview.php'); ?>
                <?php include('preview.php'); ?>
                <?php include('preview.php'); ?>
                <?php include('preview.php'); ?>
            </div>
        </main>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>