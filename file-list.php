<?php
require_once 'backend/Business_Logic/Function/config.php';
require_once 'backend/Business_Logic/Function/createBucket.php';
require_once 'backend/Business_Logic/Function/getAccessToken.php';
require_once 'backend/Business_Logic/Function/functions.php';
require_once 'backend/Business_Logic/Function/uploaddatabase.php';
require_once 'backend/Business_Logic/Function/upload-projectfile.php';
require_once 'backend/Business_Logic/Function/upload.php';
require_once 'db/connection.php';

try {
    $project_id = $_GET['project_id'] ?? null;
    if (!$project_id) {
        die("Project ID missing!");
    }

    // Fetch project details using MySQL PDO
    $sql = "SELECT * FROM Project WHERE project_id = :project_id";
    $stmt = $pdo->prepare($sql);

    // Bind the project_id to the prepared statement
    $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);

    // Execute the query and fetch the project details
    $stmt->execute();
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch project files
    $files = GetAllProjectFiles2($project_id);
    var_dump(json_encode($files), JSON_PRETTY_PRINT);


    // Close the database connection (not always necessary in PDO, but you can unset the connection object)
    $pdo = null;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}


function getPreviewIcon($fileName) {
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    switch (strtolower($fileExtension)) {
        case 'pdf':
            return 'images/pdf.png';
        case 'dwg':
            return 'icons/dwg-icon.png';
        case 'jpg':
        case 'jpeg':
        case 'png':
            return 'images/image.png';
        case 'obj':
            return 'images/obj.png';
        case 'doc':
        case 'docx':
            return 'images/word.png';
        default:
            return 'icons/default-icon.png';
    }
}

$access_token = getAccessToken($client_id, $client_secret);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File List</title>
    <link rel="stylesheet" href="style/file-list.css">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/viewproject.css">
    <link rel="stylesheet" href="style/upload-button.css">
    <link href="https://developer.api.autodesk.com/viewingservice/v1/viewers/style.css" rel="stylesheet">
    <script src="https://developer.api.autodesk.com/modelderivative/v2/viewers/7.*/viewer3D.min.js"></script>
    <script>
        const accessToken = "<?php echo $access_token; ?>";  // Echo the PHP token value into JS
    </script>
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
                    <li><a href="collaborators.php" class="nav-link">Collaborators</a></li>
                    <li><a href="issues.php" class="nav-link">Issues</a></li>
                    <li><a href="javascript:void(0);" id="uploadBtn" class="nav-link">Create a Commit</a></li>
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
            <?php if (!empty($files)): ?>
    <?php foreach ($files as $file): ?>
        <li class="file-item">
            <!-- Link to the file -->
            <a href="#" class="file-link" 
                data-file-name="<?= urlencode($file['file_name']) ?>" 
                data-file-type="<?= pathinfo($file['file_name'], PATHINFO_EXTENSION) ?>" 
                data-urn="<?= htmlspecialchars($file['object_id']) ?>" 
                data-object-key="<?= htmlspecialchars($file['object_key']) ?>" onclick="showFileDetails(event)">
                
                <!-- SVG Icon (you can adjust this based on file type) -->
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                
                <!-- File Name -->
                <span>
                    <?= htmlspecialchars($file['file_name']); ?>
                </span>
            </a>
        </li>
    <?php endforeach; ?>
<?php else: ?>
    <li>No files found.</li>
<?php endif; ?>


        </ul>
        </div>
    </div>
</div>

<div class="page-container1"> 
    

    <div id="imageContainer"></div>
    <div class="viewer-container">
        <div id="forgeViewer"></div>
        <div id="viewables_dropdown" style="display: none;">
            <select id="viewables"></select>
        </div>        
    </div>    
 </div>
 

 <?php if(!empty($files)): ?>
    <div class="project-model">
        <div class="project-model-buttons">
            <button class="btn">Share</button>
            <button class="btn">Download</button>
        </div>

        <div class="project-model-data">
            <!-- This section will be populated dynamically via JavaScript -->
            <h3>Model Details</h3>
            <ul>
                <li><strong>File Name:</strong> N/A</li>
                <li><strong>File Type:</strong> N/A</li>
                <li><strong>Created By:</strong> N/A</li>
            </ul>
        </div>

        <div class="project-model-timeline">
                <div class="project-model-timeline-header">
                    <h3>Model Timeline</h3>
                    <?php
                        $commits = require_once 'backend/Business_Logic/Function/Get_All_Commits.php';;
                        $totalCommits = is_array($commits) ? count($commits) : 0;
                    ?>
<span class="total-commits">Total Commits: <?php echo $totalCommits; ?></span>
                    <div class="filter-container">
                        <input type="date" id="filterDate" name="filterDate">
                        <button id="filterBeforeBtn" class="btn">Before</button>
                        <button id="filterAfterBtn" class="btn">After</button>
                    </div>
                </div>
                <div id="commitDetails">
                    <!-- Commit messages will be dynamically injected here -->
                </div>
            </div>
    </div>
<?php else: ?>
    <p>No files available for this project.</p>
<?php endif; ?>





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
                            <label for="commitMessage">Commit Message:</label>
                            <input type="text" id="commitMessage" name= "commitMessage" placeholder="Enter commit message" required>
                        </div>
                        <button type="submit" class="browse-btn">Upload</button>
                    </div>
                </div>
                
        </form> 



<!-- Share Modal -->
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

<script src="backend/Business_Logic/js/main.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.2/mammoth.browser.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>

<!-- <script src="js/share.js"></script> -->
<script>
    // Modal functionality for upload and share modals
    document.getElementById('menuBtn').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
    });
    
</script>

<script>
    function showFileDetails(event) {
    event.preventDefault();  // Prevents the default link action (like navigation)

    const fileLink = event.currentTarget;  // The clicked link
    const fileName = fileLink.getAttribute('data-file-name');
    const fileType = fileLink.getAttribute('data-file-type');
    const createdBy = fileLink.getAttribute('data-created-by');

    // Find the model section where the details will be displayed
    const projectModelData = document.querySelector('.project-model-data');
    
    // Update the project model with the file details
    projectModelData.innerHTML = `
        <h3>Model Details</h3>
        <ul>
            <li><strong>File Name:</strong> ${decodeURIComponent(fileName)}</li>
            <li><strong>File Type:</strong> ${fileType.toUpperCase()}</li>
            <li><strong>Created By:</strong> ${createdBy}</li>
        </ul>
    `;
}

</script>



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

                    // Show commit message field
                    document.getElementById('commitMessageContainer').style.display = 'block';
                    document.querySelector('#upload-form button[type="submit"]').style.display = 'block';
                    const event = new Event('change');
                    document.getElementById('file-upload').dispatchEvent(event);
                }
            }
        });
    </script>
<script>document.addEventListener("DOMContentLoaded", function () {
    const uploadBtn = document.getElementById("uploadBtn");
    const uploadModal = document.getElementById("uploadModal");
    const commitMessageContainer = document.getElementById("commitMessageContainer");
    const closeModal = document.querySelector(".close");

    if (uploadBtn) {
        uploadBtn.addEventListener("click", function () {
            uploadModal.style.display = "block";  // Show the upload modal
            commitMessageContainer.style.display = "block";  // Ensure commit message input is visible
        });
    }

    if (closeModal) {
        closeModal.addEventListener("click", function () {
            uploadModal.style.display = "none"; // Close the modal when the close button is clicked
        });
    }

    window.addEventListener("click", function (event) {
        if (event.target === uploadModal) {
            uploadModal.style.display = "none"; // Close modal if clicked outside
        }
    });
});
</script>


<script>
    function showCommitMessages(project_id) {
    // Fetch commit messages for the given project_id
    fetch(`/backend/Business_Logic/Function/Fetch_Commits.php?project_id=${project_id}`)
        .then(response => response.json())  // Parse the JSON response
        .then(data => {
            // Clear any existing commit details
            const commitDetails = document.getElementById('commitDetails');
            commitDetails.innerHTML = '';  // Clear previous commit messages

            // Check if there are commits
            if (data.length > 0) {
                // Loop through each commit and display it
                data.forEach(commit => {
                    const commitDiv = document.createElement('div');
                    commitDiv.classList.add('commit-item');
                    commitDiv.innerHTML = `<p>${commit.message}</p>`;  // Assuming the column name is 'message'
                    commitDetails.appendChild(commitDiv);
                });
            } else {
                commitDetails.innerHTML = '<p>No commits found for this project.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

</script>

<script src="js/issues-dropdown.js"></script>
    <script src="js/file-list-dropdown.js"></script>
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


</body>


</html>
