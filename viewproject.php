<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <title>AutoDesk | Project Name</title>
</head>
<body>

<div class="page-container">   

<div class = "project-container">
    <div class = "project-header">
        <div class = "project-title"></div>
        <div class = "project-buttons"></div>
    </div>
    <div class = "project-model">
        <div class = "project-model-viewer">

        </div>
        <div class = "project-model-data">

        </div>
    </div>
    <div class = "project-model-timeline">

    </div>
</div>
</div>

</body>
</html> -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/viewproject.css">
    
    <title>AutoDesk | Project Name</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="page-container">
        <div class="project-container">

            <div class="project-header">
                <div class="project-title">
                    <h1>Project Name</h1>
                    <p class="project-description">A brief description of the project.</p>
                </div>
                <div class="project-top-buttons">
                    <button class="btn">Collaborators</button>
                    <button class="btn">Issues</button>
                </div>
            </div>


            <div class="project-model">
                <div class="project-model-viewer">

                </div>
                <div class="project-model-buttons">
                    <button class="btn">Share</button>
                    <button class="btn">Download</button>
                </div>
                <div class="project-model-data">
                    <h3>Model Details</h3>
                    <ul>
                        <li><strong>File Type:</strong> GLB</li>
                        <li><strong>File Size:</strong> 5.2 MB</li>
                        <li><strong>Created:</strong> 2023-10-01</li>
                        <li><strong>Last Updated:</strong> 2023-10-15</li>
                    </ul>
                </div>
            </div>
            

            <div class="project-model-timeline">
                <h3>Project Timeline</h3>
                <div class="timeline">
                    <div class="timeline-event">
                        <span class="timeline-date">2023-10-01</span>
                        <span class="timeline-description">Project Created</span>
                    </div>
                    <div class="timeline-event">
                        <span class="timeline-date">2023-10-10</span>
                        <span class="timeline-description">First Model Uploaded</span>
                    </div>
                    <div class="timeline-event">
                        <span class="timeline-date">2023-10-15</span>
                        <span class="timeline-description">Model Updated</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('include/footer.php'); ?>
</body>
</html>