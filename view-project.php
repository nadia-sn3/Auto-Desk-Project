<?php
    // Database and configuration includes
    require_once 'backend/Business_Logic/Function/config.php';
    require_once 'backend/Business_Logic/Function/createBucket.php';
    require_once 'backend/Business_Logic/Function/getAccessToken.php';
    require_once 'backend/Business_Logic/Function/functions.php';
    require_once 'backend/Business_Logic/Function/Download_Functions.php';
    require_once 'backend/Business_Logic/Function/create_project.php';
    require_once 'backend/Business_Logic/Function/upload-projectfile.php';
    require_once 'db/connection.php';

    // Get URN from URL
    $urn = isset($_GET['urn']) ? htmlspecialchars($_GET['urn']) : '';

    // Authentication and project setup
    $access_token = getAccessToken($client_id, $client_secret);
    $project_id = $_GET['project_id'] ?? null;
    if (!$project_id) {
        die("Project ID missing!");
    }
    
    // Session and user information
    session_start();
    $user_id = $_SESSION['user_id'] ?? null;
    
    // Get project details from database
    $stmt = $pdo->prepare("SELECT * FROM Project WHERE project_id = :project_id");
    $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->execute();
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    // User role and permissions
    $user_role = null;
    $is_admin = false;
    $is_manager = false;
    $is_editor = false;
    $is_viewer = false;
    
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT pr.role_name, pr.permissions 
                              FROM project_members pm
                              JOIN project_roles pr ON pm.project_role_id = pr.project_role_id
                              WHERE pm.project_id = :project_id AND pm.user_id = :user_id");
        $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($role) {
            $user_role = $role['role_name'];
            $permissions = json_decode($role['permissions'], true);
            
            $is_admin = $user_role === 'Project Admin';
            $is_manager = $user_role === 'Project Manager';
            $is_editor = $user_role === 'Project Editor';
            $is_viewer = $user_role === 'Project Viewer';
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autodesk | Project Name</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/viewproject.css">
    <link rel="stylesheet" href="style/upload-button.css">
    <link rel="stylesheet" href="backend/css/main.css">
    <link rel="stylesheet" href="style/version+issues.css">
    <link href="https://developer.api.autodesk.com/viewingservice/v1/viewers/style.css" rel="stylesheet">
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="page-container">
        <div class="project-container">
            <!-- Project Header Section -->
            <div class="project-header">
                <div class="project-title">
                    <h2><?php echo htmlspecialchars($project['project_name']); ?></h2>
                    <p><?php echo htmlspecialchars($project['description']); ?></p>
                </div>
            </div>

            <!-- Navigation Bar -->
            <nav class="project-nav-bar">
                <ul>
                    <li><a href="collaborators.php?project_id=<?php echo $project_id; ?>" class="nav-link">Collaborators</a></li>    
                    <?php if ($is_admin || $is_manager || $is_editor): ?>
                        <li><a href="javascript:void(0);" id="uploadBtn" class="nav-link">Create a Commit</a></li>
                    <?php endif; ?>
                    <?php if ($is_admin || $is_manager): ?>
                        <li>
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="nav-link dropdown-toggle">Manage Project</a>
                                <div class="dropdown-content">
                                    <?php if ($is_admin): ?>
                                        <a href="#" id="deleteProject">Delete Project</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endif; ?>    
                </ul>
            </nav>

            <!-- File Dropdown Section -->
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

            <!-- Upload Modal (Conditional) -->
            <?php if ($is_admin || $is_manager || $is_editor): ?>
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
            <?php endif; ?>

            <!-- Main Model Viewer Section -->
            <div class="project-model">
                <?php if (empty($urn)): ?>
                    <?php if ($is_admin || $is_manager || $is_editor): ?>
                        <!-- Empty viewer with upload option -->
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
                                <p class="file-types">Supported formats: .rvt, .dwg, .ifc, .obj, .glb, .fbx</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Empty viewer for non-editors -->
                        <div class="empty-viewer-upload">
                            <div class="upload-area-large" id="dropAreaLarge">
                                <div class="empty-viewer-message">
                                    <h3>No Model Available</h3>
                                    <p>This project doesn't have any models uploaded yet.</p>
                                    <p>Please contact the project admin if you need access.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Model viewer when URN exists -->
                    <div class="project-model-viewer" id="forgeViewer"></div> 
                    <div id="viewables_dropdown" style="display: none;">
                        <select id="viewables"></select>
                    </div>
                <?php endif; ?>
                
                <!-- Model Action Buttons -->
                <div class="project-model-buttons">
                        <button class="btn">Download</button>
                </div>
                
                <!-- Model Metadata -->
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

            <!-- Version Timeline Section -->
            <div class="project-model-timeline">
                <div class="project-model-timeline-header">
                    <h3>Model Timeline</h3>
                </div>

                <div class="versions-list" id="commitDetails">
                    <div class="project-model-timeline-versions">
                        <div class="timeline-version">
                            <div class="version-no-issues">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#4CAF50" width="18px" height="18px">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                            </div>
                            <span class="commit-message">Initial project creation</span>
                            <span class="commit-info">
                                <span class="commit-date">V.1</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('include/footer.php'); ?>

    <!-- JavaScript Section -->
    <script src="https://developer.api.autodesk.com/modelderivative/v2/viewers/7.*/viewer3D.min.js"></script>
    <script>var accessToken = "<?php echo $access_token; ?>";</script>
    <script src="backend/Business_Logic/js/main.js"></script>
    <script src="js/share.js"></script>
    <script src="js/upload.js"></script>
    <script src="js/issues-dropdown.js"></script>
    <script src="js/file-list-dropdown.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($is_admin || $is_manager || $is_editor): ?>
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
            <?php endif; ?>
        });
    </script>
</body>
</html>