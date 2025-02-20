<?php
include 'backend/php/config.php';
include 'backend/php/functions.php';
include 'backend/php/upload.php';
// Get the URN from the URL
$urn = isset($_GET['urn']) ? htmlspecialchars($_GET['urn']) : '';

// Include your backend logic to get an Autodesk Forge Access Token
$access_token = getAccessToken($client_id, $client_secret);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/viewproject.css">
    <link rel="stylesheet" href="style/upload-button.css">
    <link rel="stylesheet" href="backend/css/main.css">
    <link href="https://developer.api.autodesk.com/viewingservice/v1/viewers/style.css" rel="stylesheet">
    <script src="https://developer.api.autodesk.com/modelderivative/v2/viewers/7.*/viewer3D.min.js"></script>
    
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
            <div class="project-model-viewer" id="forgeViewer"></div> 
                <div id="viewables_dropdown" style="display: none;">
                    <select id="viewables"></select>
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

            </div>
        </div>
    </div>

    <?php include('include/footer.php'); ?>

    <div id="shareModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Share Project</h2>
        <form id="share-form">
            <div class="form-group">
                <label for="share-username">Search by Username</label>
                <input type="text" id="share-username" name="share-username" placeholder="Enter username">
            </div>
            <div class="form-group">
                <label for="share-email">Or Invite by Email</label>
                <input type="email" id="share-email" name="share-email" placeholder="Enter email">
            </div>
            <div class="form-group">
                <label for="share-role">Role</label>
                <select id="share-role" name="share-role">
                    <option value="viewer">Viewer</option>
                </select>
            </div>
            <div class="form-group">
                <label for="share-duration">Access Duration (days)</label>
                <input type="number" id="share-duration" name="share-duration" placeholder="Enter number of days">
            </div>
            <button type="submit" class="submit-btn">Share</button>
        </form>
    </div>
</div>

<script src="js/share.js"></script>
<script>
        // Only output the token if it exists
        <?php if ($access_token): ?>
            var accessToken = "<?php echo htmlspecialchars($access_token, ENT_QUOTES, 'UTF-8'); ?>";
            console.log('Access Token:', accessToken);
        <?php else: ?>
            console.log('Error: Access token not retrieved.');
        <?php endif; ?>
        // Ensure URN is passed correctly from PHP
        var urn = "<?php echo htmlspecialchars($urn, ENT_QUOTES, 'UTF-8'); ?>"; 
        
        // Check if URN is valid
        if (!urn || urn === 'undefined') {
            console.log("Error: URN is undefined or invalid.");
        } else {
            console.log("URN passed successfully:", urn);
        }
        </script>
<script src= "backend/js/main.js"></script>

</body>
</html>