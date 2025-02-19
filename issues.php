<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/issues.css">
    <title>AutoDesk | Issues</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="page-container">
        <div class="issues-page-container">
            <div class="project-header">
                <h1 class="project-title">Project Name</h1>
                <p class="project-description">A brief description of the project.</p>
            </div>

            <div class="issues-container">
                <div class="issues-container-header">
                    <h4>Issues</h4>
                    <button id="add-issues-btn" class="add-issues-btn">Add Issue</button>
                </div>
                <div class="issues-container-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Issue Raised</th>
                                <th>File</th>
                                <th>Username</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php include('issues-row.php'); ?>
                            <?php include('issues-row.php'); ?>
                            <?php include('issues-row.php'); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <?php include('include/footer.php'); ?>

</body>
</html>

