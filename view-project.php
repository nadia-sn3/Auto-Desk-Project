<?php
require_once 'backend/Business_Logic/Function/config.php';
require_once 'backend/Business_Logic/Function/createBucket.php';
require_once 'backend/Business_Logic/Function/getAccessToken.php';
require_once 'backend/Business_Logic/Function/functions.php';
require_once 'backend/Business_Logic/Function/Download_Functions.php';
require_once 'backend/Business_Logic/Function/create_project.php';
require_once 'backend/Business_Logic/Function/upload-projectfile.php';
require_once 'db/connection.php';
$urn = isset($_GET['urn']) ? htmlspecialchars($_GET['urn']) : '';

$access_token = getAccessToken($client_id, $client_secret);

if(isset($_GET['downloadFile']))
{
    include("backend/php/Download_Functions.php");
    
    $objectkey =  $_GET['objectKey'];

    $signedUrl = ObtainSignedURL($access_token, $bucket_key, $objectkey);

    $downloadURL = $signedUrl["url"];

    $fileNameSaveAs = $objectkey;

    $fileData = DownloadFile($downloadURL, $fileNameSaveAs);
    
    header("Cache-Control: public");
    header("Content-Description: FIle Transfer");
    header("Content-Disposition: attachment; filename=$objectkey");
    header("Content-Type: application/zip");
    header("Content-Transfer-Emcoding: binary");

    echo $fileData;
    exit;
}

$project_id = $_GET['project_id'] ?? null;
if (!$project_id) {
    die("Project ID missing!");
}

$stmt = $pdo->prepare("SELECT * FROM Project WHERE project_id = :project_id");
$stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);


$stmt->execute();

$project = $stmt->fetch(PDO::FETCH_ASSOC);

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
    <title>Autodesk | Project Name</title>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="page-container">
        <div class="project-container">
            <div class="project-header">
                <div class="project-title">
                    <h2>Project: <?php echo htmlspecialchars($project['project_name']); ?></h2>
                    <p><?php echo htmlspecialchars($project['description']); ?></p>
                </div>
            </div>

            <nav class="project-nav-bar">
                <ul>
                <li><a href="collaborators.php?project_id=<?php echo $project_id; ?>" class="nav-link">Collaborators</a></li>                 
                    <li><a href="javascript:void(0);" id="uploadBtn" class="nav-link">Create a Commit</a></li>
                    <li>
            <?php if ($is_admin): ?>
                <div class="dropdown">
                    <a href="javascript:void(0);" class="nav-link dropdown-toggle">Manage Project</a>
                    <div class="dropdown-content">
                        <a href="#" id="archiveProject">Archive Project</a>
                        <a href="#" id="deleteProject">Delete Project</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="javascript:void(0);" class="nav-link">Manage Project</a>
            <?php endif; ?>
        </li>
                    </ul>
            </nav>

            <div class="file-dropdown-wrapper">
    <div class="file-dropdown">
        <button class="dropdown-header">
            <span class="dropdown-title">Project Files</span>
            <svg class="dropdown-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 9l6 6 6-6"/>
            </svg>
        </button>
        <div class="dropdown-menu">
            <div class="file-search">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" placeholder="Search files..." class="search-input">
            </div>
            <ul class="file-list">
            </ul>
        </div>
    </div>
</div>

        <form method="POST" enctype="multipart/form-data" id="upload-form"> 

        <div id="uploadModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Upload Files</h2>
                        <div class="upload-area" id="dropArea">
                            <p>Drag & Drop files here</p>
                            <p>or</p>
                            <input type="file" id="file-upload" name="file-upload[]" multiple>
                            <label for="file-upload" class="browse-btn">Browse Files</label>
                        </div>
                        <div id="fileList"></div>
                        <div id="commitMessageContainer" style="display: none;">
                            <label for="commitMessage">Initial Commit Message:</label>
                            <input type="text" id="commitMessage" name= "commitMessage" placeholder="Enter commit message" required>
                        </div>
                        <button type="submit" class="browse-btn">Upload</button>
                    </div>
                </div>
                
        </form> 

            <div class="project-model">
                <?php if (empty($urn)): ?>

                    <div class="empty-viewer-upload">
                        <div class="upload-area-large" id="dropAreaLarge">
                            <div class="upload-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                            </div>
                            <h3>Upload Your First Model</h3>
                            <p>Drag & drop your 3D model files here</p>
                            <p class="or-text">or</p>
                            <input type="file" id="fileInputLarge" multiple style="display: none;">
                            <label for="fileInputLarge" class="browse-btn-large">Select Files</label>
                        </div>
                    </div>
                <?php else: ?>

                    <div class="project-model-viewer" id="forgeViewer"></div> 
                    <div id="viewables_dropdown" style="display: none;">
                        <select id="viewables"></select>
                    </div>
                <?php endif; ?>
                
                <div class="project-model-buttons">
                    <button class="btn">Share</button>
                    <button class="btn">Download</button>
                </div>
                <div class="project-model-data">
                    <h3>Model Details</h3>
                    <ul>
                        <li><strong>File Type:</strong> <?php echo empty($urn) ? 'No file uploaded' : 'GLB'; ?></li>
                        <li><strong>File Size:</strong> <?php echo empty($urn) ? '0 MB' : '5.2 MB'; ?></li>
                        <li><strong>Created:</strong> <?php echo empty($urn) ? 'N/A' : '2023-10-01'; ?></li>
                        <li><strong>Last Updated:</strong> <?php echo empty($urn) ? 'N/A' : '2023-10-15'; ?></li>
                    </ul>
                </div>
            </div>

            <div class="project-model-timeline">
                <div class="project-model-timeline-header">
                    <h3>Model Timeline</h3>
                    <span class="total-commits">Total Commits: <?php echo empty($urn) ? '0' : '12'; ?></span>
                    <div class="filter-container">
                        <input type="date" id="filterDate" name="filterDate">
                        <button id="filterBeforeBtn" class="btn">Before</button>
                        <button id="filterAfterBtn" class="btn">After</button>
                    </div>
                </div>
                <div class="project-model-timeline-versions">
                    <?php include('version.php'); ?>
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

    <script>
        <?php if ($access_token): ?>
                var accessToken = "<?php echo htmlspecialchars($access_token, ENT_QUOTES, 'UTF-8'); ?>";
                console.log('Access Token:', accessToken);
        <?php else: ?>
                console.log('Error: Access token not retrieved.');
        <?php endif; ?>
            var urn = "<?php echo htmlspecialchars($urn, ENT_QUOTES, 'UTF-8'); ?>"; 
            
            if (!urn || urn === 'undefined') {
                console.log("Error: URN is undefined or invalid.");
            } else {
                console.log("URN passed successfully:", urn);
            }
    </script>
     <script>  
      var accessToken = "<?php echo $access_token; ?>";
    </script>
    <script src="backend/Business_Logic/js/main.js"></script>
    <script src="js/share.js"></script>
    <script src="js/upload.js"></script>
    <script src="js/issues-dropdown.js"></script>
    <script src="js/file-list-dropdown.js"></script>

    <script>

        document.addEventListener('DOMContentLoaded', function() {
            const dropAreaLarge = document.getElementById('dropAreaLarge');
            const fileInputLarge = document.getElementById('fileInputLarge');
            
            if (dropAreaLarge && fileInputLarge) {

                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropAreaLarge.addEventListener(eventName, preventDefaults, false);
                });
                
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropAreaLarge.addEventListener(eventName, highlight, false);
                });
                
                ['dragleave', 'drop'].forEach(eventName => {
                    dropAreaLarge.addEventListener(eventName, unhighlight, false);
                });
                
                dropAreaLarge.addEventListener('drop', handleDrop, false);
                
                fileInputLarge.addEventListener('change', function(e) {
                    handleFiles(e.target.files);
                });
            }
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            function highlight() {
                dropAreaLarge.classList.add('highlight');
            }
            
            function unhighlight() {
                dropAreaLarge.classList.remove('highlight');
            }
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles(files);
            }
            
            function handleFiles(files) {
                if (files.length > 0) {
                    document.getElementById('uploadModal').style.display = 'block';
                    document.getElementById('file-upload').files = files;

                    document.getElementById('commitMessageContainer').style.display = 'block';
                    document.querySelector('#upload-form button[type="submit"]').style.display = 'block';
                    const event = new Event('change');
                    document.getElementById('file-upload').dispatchEvent(event);
                }
            }
        });
    </script>
</body>
</html>