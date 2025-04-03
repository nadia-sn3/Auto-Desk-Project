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
        $sql = "SELECT * FROM Project WHERE project_id = :project_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->execute();
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        $files = GetAllProjectFiles2($project_id);
        $pdo = null;
        $object_key = $_GET['object_key'] ?? null;
        
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
        <link rel="stylesheet" href="style/version+issues.css">
        <link href="https://developer.api.autodesk.com/viewingservice/v1/viewers/style.css" rel="stylesheet">
        <script src="https://developer.api.autodesk.com/modelderivative/v2/viewers/7.*/viewer3D.min.js"></script>
        <script>
            const accessToken = "<?php echo $access_token; ?>";  
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
                    <li><a href="collaborators.php?project_id=<?php echo $_GET['project_id']; ?>" class="nav-link">Collaborators</a></li>
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
                                    <a href="#" class="file-link" 
                                        data-file-name="<?= urlencode($file['file_name']) ?>" 
                                        data-file-type="<?= pathinfo($file['file_name'], PATHINFO_EXTENSION) ?>" 
                                        data-urn="<?= htmlspecialchars($file['object_id']) ?>" 
                                        data-object-key="<?= htmlspecialchars($file['object_key']) ?>" onclick="showFileDetails(event)">
                                        
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                            <polyline points="14 2 14 8 20 8"/>
                                        </svg>
                                        
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

                <div class="viewer-container">
                    <div id="forgeViewer"></div>
                    <div id="viewables_dropdown" style="display: none;">
                        <select id="viewables"></select>
                    </div>   
                    <div id="imageContainer"></div>     
                </div>    

                <?php if(!empty($files)): ?>
                <div class="project-model">
                    <div class="project-model-buttons">
                        <button class="btn">Share</button>
                        <button class="btn">Download</button>
                    </div>

                    <div class="project-model-data">
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
                            <div class="report-filters">
                                <button class="btn active" data-status="all">All Issues</button>
                                <button class="btn" data-status="open">Open</button>
                                <button class="btn" data-status="in_progress">In Progress</button>
                                <button class="btn" data-status="resolved">Resolved</button>
                            </div>
                        </div>

                        <div id="rollbackModal" class="modal">
                            <div class="modal-content">
                                <span class="close">&times;</span>
                                <h2>Rollback to this version?</h2>
                                <form id="rollbackForm">
                                    <div class="form-group">
                                        <label for="rollbackComment">Comment:</label>
                                        <textarea id="rollbackComment" name="rollbackComment" placeholder="Explain why you're rolling back..." required></textarea>
                                    </div>
                                    <button type="submit" class="submit-btn">Confirm Rollback</button>
                                </form>
                            </div>
                        </div>

                        <div class="versions-list" id="commitDetails">
                            <!-- Commits will be dynamically inserted here -->
                        </div>
                    </div>
                </div>

                <div id="issueModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Raise New Issue</h2>
                        <form id="issueForm">
                            <input type="hidden" id="versionId">
                            <div class="form-group">
                                <label for="issueFile">File:</label>
                                <input type="text" id="issueFile" required>
                            </div>
                            <div class="form-group">
                                <label for="issueDescription">Issue Description:</label>
                                <textarea id="issueDescription" required></textarea>
                            </div>
                            <button type="submit" class="submit-btn">Submit Issue</button>
                        </form>
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
            </div>
        </div>
                    <script src="backend/Business_Logic/js/main.js"></script>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.2/mammoth.browser.min.js"></script>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>


                    <!-- <script src="js/share.js"></script> NEEDS IMPLEMENTING -->
                     
                    <script>
                        document.getElementById('menuBtn').addEventListener('click', function() {
                                const sidebar = document.getElementById('sidebar');
                                sidebar.classList.toggle('active');
                        });
                        
                    </script>

                    <script>
                        function showFileDetails(event) {
                        event.preventDefault(); 

                        const fileLink = event.currentTarget;  
                        const fileName = fileLink.getAttribute('data-file-name');
                        const fileType = fileLink.getAttribute('data-file-type');
                        const createdBy = fileLink.getAttribute('data-created-by');
                        const projectModelData = document.querySelector('.project-model-data');
                        
                    
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
                                    document.getElementById('commitMessageContainer').style.display = 'block';
                                    document.querySelector('#upload-form button[type="submit"]').style.display = 'block';
                                    const event = new Event('change');
                                    document.getElementById('file-upload').dispatchEvent(event);
                                }
                            }
                        });
                    </script>
                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                        const uploadBtn = document.getElementById("uploadBtn");
                        const uploadModal = document.getElementById("uploadModal");
                        const commitMessageContainer = document.getElementById("commitMessageContainer");
                        const closeModal = document.querySelector(".close");

                        if (uploadBtn) {
                            uploadBtn.addEventListener("click", function () {
                                uploadModal.style.display = "block"; 
                                commitMessageContainer.style.display = "block";  
                            });
                        }

                        if (closeModal) {
                            closeModal.addEventListener("click", function () {
                                uploadModal.style.display = "none"; 
                            });
                        }

                        window.addEventListener("click", function (event) {
                            if (event.target === uploadModal) {
                                uploadModal.style.display = "none"; 
                            }
                        });
                        });
                    </script>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const project_id = <?php echo json_encode($project_id); ?>;
                            if (project_id) {
                                showCommitMessages(project_id);
                            } else {
                                console.error('Project ID is missing');
                            }

                          
                            document.addEventListener('click', function(e) {
                                if (e.target.classList.contains('version-warning-indicator')) {
                                    e.stopPropagation();
                                    const dropdownId = e.target.getAttribute('data-issues');
                                    const dropdown = document.getElementById(dropdownId);
                                    
                                    // Close all other dropdowns
                                    document.querySelectorAll('.issues-dropdown-content').forEach(d => {
                                        if (d.id !== dropdownId) d.style.display = 'none';
                                    });
                                    
                                    // Toggle current dropdown
                                    if (dropdown.style.display === 'block') {
                                        dropdown.style.display = 'none';
                                    } else {
                                        dropdown.style.display = 'block';
                                    }
                                } else {
                                    document.querySelectorAll('.issues-dropdown-content').forEach(d => {
                                        d.style.display = 'none';
                                    });
                                }
                            });
                            
                            // Filter buttons
                            document.querySelectorAll('.report-filters .btn').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    const status = this.getAttribute('data-status');
                                    
                                    document.querySelectorAll('.report-filters .btn').forEach(b => {
                                        b.classList.remove('active');
                                    });
                                    this.classList.add('active');
                                    
                                    document.querySelectorAll('.issues-dropdown-content tr').forEach(row => {
                                        if (status === 'all') {
                                            row.style.display = '';
                                        } else if (row.classList.contains(status)) {
                                            row.style.display = '';
                                        } else {
                                            row.style.display = 'none';
                                        }
                                    });
                                });
                            });
                            
                            // Issue status change buttons
                            document.addEventListener('click', function(e) {
                                if (e.target.classList.contains('change-status')) {
                                    const issueId = e.target.getAttribute('data-issue');
                                    const newStatus = e.target.getAttribute('data-status');
                                    
                                    console.log(`Changing issue ${issueId} to status ${newStatus}`);
                                    
                                    const row = e.target.closest('tr');
                                    row.className = newStatus;
                                    
                                    const badge = row.querySelector('.status-badge');
                                    badge.className = 'status-badge';
                                    badge.textContent = newStatus.replace('_', ' ');
                                    
                                    if (newStatus === 'open') {
                                        badge.classList.add('pending');
                                    } else if (newStatus === 'in_progress') {
                                        badge.classList.add('in-progress');
                                    } else if (newStatus === 'resolved') {
                                        badge.classList.add('resolved');
                                    }
                                    
                                    if (newStatus === 'open') {
                                        e.target.textContent = 'Mark In Progress';
                                        e.target.setAttribute('data-status', 'in_progress');
                                    } else if (newStatus === 'in_progress') {
                                        e.target.textContent = 'Mark Resolved';
                                        e.target.setAttribute('data-status', 'resolved');
                                    } else if (newStatus === 'resolved') {
                                        e.target.textContent = 'Reopen';
                                        e.target.setAttribute('data-status', 'open');
                                    }
                                }
                            });
                            
                            // Modal handling
                            const issueModal = document.getElementById("issueModal");
                            const raiseIssueBtns = document.querySelectorAll(".raise-issue-btn");
                            const closeBtns = document.querySelectorAll(".close");
                            const issueForm = document.getElementById("issueForm");
                            const versionIdInput = document.getElementById("versionId");
                            
                            // document.addEventListener('click', function(e) {
                            //     if (e.target.classList.contains('raise-issue-btn')) {
                            //         e.stopPropagation();
                            //         const versionId = e.target.getAttribute('data-version');
                            //         const projectId = e.target.getAttribute('data-project-id');
                            //         // issueModal.style.display = "block";
                            //     }
                            //     console.log(`Commit Version ID: ${versionId}`);
                            //     console.log(`Project ID: ${projectId}`);

                            //     fetchProjectFile(projectId, versionId);
                            //     const issueModal = document.getElementById("issueModal");
                            //     issueModal.style.display = "block";

                            //     // if (e.target.classList.contains('close')) {
                            //     //     issueModal.style.display = "none";
                            //     // }
                                
                            //     // if (e.target === issueModal) {
                            //     //     issueModal.style.display = "none";
                            //     // }
                            // });

                            
                        document.addEventListener('click', function (e) {
                        if (e.target.classList.contains('raise-issue-btn')) {
                            e.stopPropagation();
                            
                            const versionId = e.target.getAttribute('data-version');
                            const projectId = e.target.getAttribute('data-project-id');

                            console.log(`Commit Version ID: ${versionId}`);
                            console.log(`Project ID: ${projectId}`);

                            document.getElementById("versionId").value = versionId;
                            document.getElementById("issueForm").setAttribute("data-project-id", projectId);

                            const issueModal = document.getElementById("issueModal");
                            issueModal.style.display = "block";
                        }
                    });

                    document.getElementById("issueForm").addEventListener("submit", function (e) {
                        e.preventDefault();

                        const versionId = document.getElementById("versionId").value;
                        const projectId = document.getElementById("issueForm").getAttribute("data-project-id");
                        const file = document.getElementById("issueFile").value;
                        const description = document.getElementById("issueDescription").value;

                        console.log(`Submitting issue for Version: ${versionId}, File: ${file}, Description: ${description}`);

                        fetch("http://localhost/Auto-Desk-Project/backend/Business_Logic/Function/get_projectfile.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: `version_id=${encodeURIComponent(versionId)}&project_id=${encodeURIComponent(projectId)}&file=${encodeURIComponent(file)}&description=${encodeURIComponent(description)}`
                    })
                    .then(response => {
                        return response.text();  
                    })
                    .then(responseText => {
                        console.log('Raw Response:', responseText); 

                    
                        try {
                            const data = JSON.parse(responseText); 
                            if (data.success) {
                                alert("Issue submitted successfully!");
                                // location.reload(); 
                                window.location.href = "version.php?project_id=" + projectId;
                            } else {
                                alert("Failed to submit issue: " + data.message);
                            }
                        } catch (error) {
                            console.error("Error parsing JSON:", error);
                        }
                    })
                    .catch(error => console.error("Error submitting issue:", error));
                        document.getElementById("issueModal").style.display = "none";
                        document.getElementById("issueForm").reset();
                    });



                            
                            // Rollback functionality
                            window.showRollbackModal = function(commit_id, project_id) {
                                const rollbackModal = document.getElementById("rollbackModal");
                                const rollbackForm = document.getElementById("rollbackForm");
                                
                                rollbackModal.style.display = "block";
                                
                                rollbackForm.onsubmit = function(e) {
                                    e.preventDefault();
                                    const comment = document.getElementById("rollbackComment").value;
                                    
                                    if (!commit_id || !project_id) {
                                        console.error('Invalid commit_id or project_id');
                                        alert('Invalid commit or project ID.');
                                        return;
                                    }
                                    
                                    const url = `/Auto-desk-project/backend/Business_Logic/Function/Rollback.php?commit_id=${commit_id}&project_id=${project_id}`;
                                    console.log("Rollback URL:", url);

                                    fetch(url)
                                        .then(response => {
                                            console.log('Network response:', response);
                                            if (!response.ok) {
                                                alert('Network response was not ok');
                                                return;
                                            }
                                            return response.json();  
                                        })
                                        .then(data => {  
                                            console.log('Parsed Data:', data);

                                            if (data.status === 'success') {
                                                alert('Rollback successful!');
                                                rollbackModal.style.display = "none";
                                                showCommitMessages(project_id); // Refresh the commit list
                                            } else {
                                                alert('Rollback failed: ' + (data.error || 'Unknown error.'));
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error:', error);
                                            alert('An error occurred while rolling back the commit.');
                                        });
                                };
                            }
                            
                            // Function to show commit messages with the new UI I made
                            function showCommitMessages(project_id) {
                                fetch(`/Auto-desk-project/backend/Business_Logic/Function/Get_All_Commits.php?project_id=${project_id}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.error) {
                                            alert(data.error);
                                            return;
                                        }

                                        const commitDetails = document.getElementById('commitDetails');
                                        commitDetails.innerHTML = '';
                                        
                                        if (Array.isArray(data) && data.length > 0) {
                                            // Sort commits by date (newest first)
                                            const sortedCommits = [...data].sort((a, b) => 
                                                new Date(b.commit_date) - new Date(a.commit_date)
                                            );
                                            
                                            sortedCommits.forEach((commit, index) => {
                                                const versionId = `version-${commit.commit_id}`;
                                                const hasIssues = commit.issues && commit.issues.length > 0;
                                                const versionNumber = sortedCommits.length - index; 
                                                
                                                const commitElement = document.createElement('div');
                                                commitElement.classList.add('project-model-timeline-versions');
                                                
                                                commitElement.innerHTML = `
                                                    <div class="timeline-version">
                                                        ${hasIssues ? 
                                                            `<div class="version-warning-indicator" data-issues="${versionId}">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ff6b6b" width="18px" height="18px">
                                                                    <path d="M12 2L1 21h22L12 2zm0 3.5L18.5 19h-13L12 5.5z"/>
                                                                    <path d="M12 16c.8 0 1.5-.7 1.5-1.5S12.8 13 12 13s-1.5.7-1.5 1.5.7 1.5 1.5 1.5zm-1-5h2v-4h-2v4z"/>
                                                                </svg>
                                                            </div>` : 
                                                            `<div class="version-no-issues">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#4CAF50" width="18px" height="18px">
                                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                                                </svg>
                                                            </div>`
                                                        }
                                                        <span class="commit-message">${commit.commit_message}</span>
                                                        <span class="commit-info">
                                                            <span class="username">${commit.username || 'Unknown'}</span>
                                                            <span class="commit-date">V.${versionNumber}</span>
                                                            <span class="commit-date">${new Date(commit.commit_date).toLocaleDateString()}</span>
                                                        </span>
                                                        <button class="rollback-btn" onclick="showRollbackModal(${commit.commit_id}, ${project_id})">Rollback</button>
                                                        <button class="raise-issue-btn" data-version="${commit.commit_id}" data-project-id="${project_id}">Raise Issue</button>
                                                    </div>
                                                    
                                                    ${hasIssues ? `
                                                    <div class="issues-dropdown-content" id="${versionId}">
                                                        <table>
                                                            <thead>
                                                                <tr>
                                                                    <th>File</th>
                                                                    <th>Description</th>
                                                                    <th>Status</th>
                                                                    <th>Raised By</th>
                                                                    <th>Date</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                ${commit.issues.map(issue => `
                                                                <tr class="${issue.status}">
                                                                    <td>${issue.file}</td>
                                                                    <td>${issue.description}</td>
                                                                    <td><span class="status-badge ${issue.status}">${issue.status.replace('_', ' ')}</span></td>
                                                                    <td>${issue.raised_by}</td>
                                                                    <td>${new Date(issue.date).toLocaleDateString()}</td>
                                                                    <td class="actions">
                                                                        <button class="btn small change-status" data-issue="i${issue.id}" data-status="${issue.status === 'open' ? 'in_progress' : issue.status === 'in_progress' ? 'resolved' : 'open'}">
                                                                            ${issue.status === 'open' ? 'Mark In Progress' : issue.status === 'in_progress' ? 'Mark Resolved' : 'Reopen'}
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                                `).join('')}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    ` : ''}
                                                `;
                                                
                                                commitDetails.appendChild(commitElement);
                                            });
                                        } else {
                                            commitDetails.innerHTML = '<p>No commits found for this project.</p>';
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error fetching commits:', error);
                                        alert('An error occurred while fetching commits.');
                                    });
                            }
                        });
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