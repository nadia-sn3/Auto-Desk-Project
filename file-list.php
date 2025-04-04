<?php
require_once 'backend/Business_Logic/Function/config.php';
require_once 'backend/Business_Logic/Function/createBucket.php';
require_once 'backend/Business_Logic/Function/getAccessToken.php';
require_once 'backend/Business_Logic/Function/functions.php';
require_once 'backend/Business_Logic/Function/uploaddatabase.php';
require_once 'backend/Business_Logic/Function/upload-projectfile.php';
require_once 'backend/Business_Logic/Function/upload.php';
require_once 'db/connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    
    $current_user_id = $_SESSION['user_id'] ?? null;
    $user_role = null;
    $user_permissions = [];
    
    if ($current_user_id) {
        $sql = "SELECT pr.role_name, pr.permissions 
                FROM project_members pm
                JOIN project_roles pr ON pm.project_role_id = pr.project_role_id
                WHERE pm.project_id = :project_id AND pm.user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $current_user_id, PDO::PARAM_INT);
        $stmt->execute();
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($role) {
            $user_role = $role['role_name'];
            $user_permissions = json_decode($role['permissions'], true) ?? [];
        }
    }
    
    $pdo = null;
    $object_key = $_GET['object_key'] ?? null;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$show_collaborators = true; 
$show_commit_button = in_array($user_role, ['Project Admin', 'Project Manager', 'Project Editor']);
$show_manage_project = in_array($user_role, ['Project Admin']);
$show_rollback = in_array($user_role, ['Project Admin', 'Project Manager']);
$show_raise_issue = true;

function getPreviewIcon($fileName) {
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    switch (strtolower($fileExtension)) {
        case 'pdf': return 'images/pdf.png';
        case 'dwg': return 'icons/dwg-icon.png';
        case 'jpg': case 'jpeg': case 'png': return 'images/image.png';
        case 'obj': return 'images/obj.png';
        case 'doc': case 'docx': return 'images/word.png';
        default: return 'icons/default-icon.png';
    }
}

$access_token = getAccessToken($client_id, $client_secret);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project</title>
    
    <link rel="stylesheet" href="style/viewproject.css">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/upload-button.css">
    <link rel="stylesheet" href="style/version+issues.css">
    <link href="https://developer.api.autodesk.com/viewingservice/v1/viewers/style.css" rel="stylesheet">
    
    <script src="https://developer.api.autodesk.com/modelderivative/v2/viewers/7.*/viewer3D.min.js"></script>
    <script>
        const accessToken = "<?php echo $access_token; ?>";
        const userRole = "<?php echo $user_role; ?>";
        const projectId = <?php echo json_encode($project_id); ?>;
    </script>
</head>
<body>
    <?php include('include/header.php'); ?>

    <div class="page-container">
        <div class="project-container">

        <div class="project-header">
                <div class="project-title">
                    <h2><?php echo htmlspecialchars($project['project_name']); ?></h2>
                    <p><?php echo htmlspecialchars($project['description']); ?></p>
                </div>
            </div>

            <nav class="project-nav-bar">
                <ul>
                    <?php if ($show_collaborators): ?>
                        <li><a href="collaborators.php?project_id=<?php echo $project_id; ?>" class="nav-link">Collaborators</a></li>    
                    <?php endif; ?>
                    
                    <?php if ($show_commit_button): ?>
                        <li><a href="javascript:void(0);" id="uploadBtn" class="nav-link">Create a Commit</a></li>
                    <?php endif; ?>
                    
                    <?php if ($show_manage_project): ?>
                        <li>
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="nav-link dropdown-toggle">Manage Project</a>
                                <div class="dropdown-content">
                                    <a href="#" id="deleteProject">Delete Project</a>
                                </div>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <!-- FILE DROPDOWN SECTION -->
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
                                        
                                        <span><?= htmlspecialchars($file['file_name']); ?></span>
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

            <!-- MAIN VIEWER SECTION -->
            <div class="viewer-container">
                <div id="forgeViewer"></div>
                <div id="viewables_dropdown" style="display: none;">
                    <select id="viewables"></select>
                </div>   
                <div id="imageContainer"></div>     
            </div>  

            <!-- PROJECT MODEL SECTION -->
            <?php if(!empty($files)): ?>
                <div class="project-model">
                    <!-- Model Action Buttons -->
                    <div class="project-model-buttons">
                        <button class="btn">Download</button>
                    </div>

                    <!-- Model Metadata -->
                    <div class="project-model-data">
                        <h3>Model Details</h3>
                        <ul>
                            <li><strong>File Name:</strong> N/A</li>
                            <li><strong>File Type:</strong> N/A</li>
                            <li><strong>Created By:</strong> N/A</li>
                        </ul>
                    </div>

                    <!-- Version Timeline -->
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

                        <!-- Rollback Modal -->
                        <div id="rollbackModal" class="modal">
                            <div class="modal-content">
                                <span class="close close-rollback">&times;</span>
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

                        <!-- Commit List -->
                        <div class="versions-list" id="commitDetails">
                            <!-- Commits are dynamically inserted here -->
                        </div>
                    </div>
                </div>

               <!-- ISSUE MODAL -->
                <div id="issueModal" class="modal">
                    <div class="modal-content">
                        <span class="close close-issue">&times;</span>
                        <h2>Raise New Issue</h2>
                        <form id="issueForm">
                            <input type="hidden" id="issueVersionId" name="version_id">
                            <input type="hidden" id="issueProjectId" name="project_id" value="<?php echo $project_id; ?>">
                            <div class="form-group">
                                <label for="issueFileSelect">Select File:</label>
                                <select id="issueFileSelect" name="file_name" required>
                                    <option value="">-- Select a file --</option>
                                    <?php foreach ($files as $file): ?>
                                        <option value="<?= htmlspecialchars($file['file_name']) ?>">
                                            <?= htmlspecialchars($file['file_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="issueDescription">Issue Description:</label>
                                <textarea id="issueDescription" name="description" required></textarea>
                            </div>
                            <button type="submit" class="submit-btn">Submit Issue</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <p>No files available for this project.</p>
            <?php endif; ?>

            <!-- UPLOAD MODAL -->
            <form method="POST" enctype="multipart/form-data" id="upload-form"> 
                <div id="uploadModal" class="modal">
                    <div class="modal-content">
                        <span class="close close-upload">&times;</span>
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
                            <input type="text" id="commitMessage" name="commitMessage" placeholder="Enter commit message" required>
                        </div>
                        <button type="submit" class="browse-btn">Upload</button>
                    </div>
                </div>
            </form> 
        </div>
    </div>

    <script src="backend/Business_Logic/js/main.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.2/mammoth.browser.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script src="js/file-list-dropdown.js"></script>

    <script>
    // MODAL HANDLING
    document.addEventListener("DOMContentLoaded", function () {
        const uploadModal = document.getElementById("uploadModal");
        const issueModal = document.getElementById("issueModal");
        const rollbackModal = document.getElementById("rollbackModal");
        
        const closeUpload = document.querySelector(".close-upload");
        const closeIssue = document.querySelector(".close-issue");
        const closeRollback = document.querySelector(".close-rollback");
        
        const uploadBtn = document.getElementById("uploadBtn");
        if (uploadBtn) {
            uploadBtn.addEventListener("click", function () {
                uploadModal.style.display = "block";
                document.getElementById("commitMessageContainer").style.display = "block";
            });
        }
        
        if (closeUpload) {
            closeUpload.addEventListener("click", function () {
                uploadModal.style.display = "none";
            });
        }
        
        if (closeIssue) {
            closeIssue.addEventListener("click", function () {
                issueModal.style.display = "none";
            });
        }
        
        if (closeRollback) {
            closeRollback.addEventListener("click", function () {
                rollbackModal.style.display = "none";
            });
        }
        
        window.addEventListener("click", function (event) {
            if (event.target === uploadModal) {
                uploadModal.style.display = "none";
            }
            if (event.target === issueModal) {
                issueModal.style.display = "none";
            }
            if (event.target === rollbackModal) {
                rollbackModal.style.display = "none";
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                uploadModal.style.display = 'none';
                issueModal.style.display = 'none';
                rollbackModal.style.display = 'none';
            }
        });
    });

    // FILE DETAILS DISPLAY
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

    // PERMISSION CONTROL
    document.addEventListener('DOMContentLoaded', function() {
        if (userRole !== 'Project Admin' && userRole !== 'Project Manager') {
            document.querySelectorAll('.rollback-btn').forEach(btn => {
                btn.style.display = 'none';
            });
        }
        if (projectId) showCommitMessages(projectId);
    });

    // Function to show commit messages with issues
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
                    const sortedCommits = [...data].sort((a, b) => 
                        new Date(b.commit_date) - new Date(a.commit_date)
                    );
                    
                    sortedCommits.forEach((commit, index) => {
                        const versionId = `version-${commit.commit_id}`;
                        const hasIssues = commit.issues && commit.issues.length > 0;
                        const versionNumber = sortedCommits.length - index; 
                        const isLatestCommit = index === 0;
                        
                        const commitElement = document.createElement('div');
                        commitElement.classList.add('project-model-timeline-versions');
                        
                        commitElement.innerHTML = `
                            <div class="timeline-version">
                                ${hasIssues ? 
                                    `<div class="version-warning-indicator">
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
                                    <span class="commit-date">V.${versionNumber}</span>
                                    <span class="commit-date">${new Date(commit.commit_date).toLocaleDateString()}</span>
                                </span>
                                ${(userRole === 'Project Admin' || userRole === 'Project Manager') && !isLatestCommit ? 
                                    `<button class="rollback-btn" onclick="showRollbackModal(${commit.commit_id}, ${project_id})">Rollback</button>` : ''}
                                <button class="raise-issue-btn" data-version="${commit.commit_id}">Raise Issue</button>
                            </div>
                            
                            ${hasIssues ? `
                            <div class="issues-container">
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
                                                <button class="btn small change-status" data-issue="${issue.id}" data-status="${issue.status === 'open' ? 'in_progress' : issue.status === 'in_progress' ? 'resolved' : 'open'}">
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
                    
                    initIssueStatusChange();
                    
                    document.querySelectorAll('.raise-issue-btn').forEach(btn => {
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            const versionId = this.getAttribute('data-version');
                            document.getElementById('issueVersionId').value = versionId;
                            document.getElementById('issueModal').style.display = 'block';
                        });
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

    function initIssueStatusChange() {
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('change-status')) {
                const issueId = e.target.getAttribute('data-issue');
                const newStatus = e.target.getAttribute('data-status');
                
                const row = e.target.closest('tr');
                row.className = newStatus;
                
                const badge = row.querySelector('.status-badge');
                badge.className = 'status-badge ' + newStatus;
                badge.textContent = newStatus.replace('_', ' ');
                
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
                
                updateIssueStatus(issueId, newStatus);
            }
        });

        document.querySelectorAll('.report-filters .btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const status = this.getAttribute('data-status');
                
                document.querySelectorAll('.report-filters .btn').forEach(b => {
                    b.classList.remove('active');
                });
                this.classList.add('active');
                
                document.querySelectorAll('.issues-container').forEach(container => {
                    const rows = container.querySelectorAll('tr');
                    rows.forEach(row => {
                        if (row.tagName === 'THEAD') return;
                        
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
        });
    }

    function updateIssueStatus(issueId, newStatus) {
        fetch('/Auto-desk-project/backend/Business_Logic/Function/update_issue_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                issue_id: issueId,
                new_status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Failed to update issue status:', data.message);
            }
        })
        .catch(error => {
            console.error('Error updating issue status:', error);
        });
    }

    document.getElementById('issueForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const selectedFile = document.getElementById('issueFileSelect').value;
        if (!selectedFile) {
            alert('Please select a file');
            return;
        }
        
        const formData = new FormData(this);
        formData.append('raised_by', '<?php echo $_SESSION["user_id"] ?? ""; ?>');
        
        fetch('/Auto-desk-project/backend/Business_Logic/Function/save_issue.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                alert('Issue submitted successfully!');
                document.getElementById('issueModal').style.display = 'none';
                document.getElementById('issueForm').reset();
                showCommitMessages(projectId);
            } else {
                throw new Error(data.message || 'Failed to submit issue');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error submitting issue: ' + error.message);
        });
    });

    // FILE UPLOAD HANDLING
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

    // DELETE PROJECT
    document.addEventListener('DOMContentLoaded', function() {
        const deleteProjectBtn = document.getElementById('deleteProject');
        
        if (deleteProjectBtn) {
            deleteProjectBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (!projectId || isNaN(projectId)) {
                    alert('Invalid project ID');
                    return;
                }
                
                if (confirm('Are you sure you want to permanently delete this project? This cannot be undone.')) {
                    const originalText = deleteProjectBtn.textContent;
                    deleteProjectBtn.disabled = true;
                    deleteProjectBtn.textContent = 'Deleting...';
                    
                    fetch(`/Auto-desk-project/backend/Business_Logic/Function/deleteProject.php?project_id=${encodeURIComponent(projectId)}`)
                    .then(response => {
                        if (!response.ok) return response.json().then(err => { throw err; });
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Project deleted successfully!');
                            window.location.href = 'project-home.php';
                        } else {
                            throw new Error(data.message || 'Failed to delete project');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error: ' + error.message);
                    })
                    .finally(() => {
                        deleteProjectBtn.disabled = false;
                        deleteProjectBtn.textContent = originalText;
                    });
                }
            });
        }

        if (projectId) showCommitMessages(projectId);
    });

    // ROLLBACK FUNCTIONALITY
    window.showRollbackModal = function(commit_id, project_id) {
        const rollbackModal = document.getElementById("rollbackModal");
        const rollbackForm = document.getElementById("rollbackForm");
        
        document.getElementById("rollbackComment").value = '';
        
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
                        showCommitMessages(project_id); 
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
    </script>

    <?php include('include/footer.php'); ?>
</body>
</html>