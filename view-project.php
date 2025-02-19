<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/viewproject.css">
    <link rel="stylesheet" href="style/upload-button.css">
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
            </div>

            <nav class="project-nav-bar">
                <ul>
                    <li><a href="collaborators.php" class="nav-link">Collaborators</a></li>
                    <li><a href="issues.php" class="nav-link">Issues</a></li>
                    <li><button id="uploadBtn" class="nav-link">Upload Files</button></li>
                </ul>
            </nav>

            <div id="uploadModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Upload Files</h2>
                    <div class="upload-area" id="dropArea">
                        <p>Drag & Drop files here</p>
                        <p>or</p>
                        <input type="file" id="fileInput" multiple>
                        <label for="fileInput" class="browse-btn">Browse Files</label>
                    </div>
                    <div id="fileList"></div>
                    <button id="confirmUploadBtn" class="browse-btn" style="display: none;">Upload</button>
                </div>
            </div>

            <div class="project-model">
                <div class="project-model-viewer"></div>
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
                <div class="project-model-timeline-header">
                    <h3>Model Timeline</h3>
                    <span class="total-commits">Total Commits: 12</span>
                </div>
                <div class="project-model-timeline-versions">
                    <div class="timeline-version">
                        <div class="commit-header">
                            <span class="commit-message">Changes to featured models</span>
                            <span class="commit-date">yesterday</span>
                        </div>
                        <div class="commit-details">
                            <span class="username">misbxh</span>
                            <span class="commit-location">Image</span>
                        </div>
                    </div>
                    <div class="timeline-version">
                        <div class="commit-header">
                            <span class="commit-message">Created new header links</span>
                            <span class="commit-date">yesterday</span>
                        </div>
                        <div class="commit-details">
                            <span class="username">include</span>
                            <span class="commit-location">User display</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('include/footer.php'); ?>

    <script src="js/upload.js"></script>
</body>
</html>