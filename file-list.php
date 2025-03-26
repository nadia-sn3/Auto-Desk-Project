<?php
require_once 'backend/Business_Logic/Function/config.php';
require_once 'backend/Business_Logic/Function/createBucket.php';
require_once 'backend/Business_Logic/Function/getAccessToken.php';
require_once 'backend/Business_Logic/Function/functions.php';
require_once 'db/connection.php';


try {
    $project_id = isset($_GET['project_id']) ? (int) $_GET['project_id'] : 0;

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT * FROM project_files WHERE project_id = :project_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->execute();
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
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

<div class="page-container1">
    <!-- Sidebar -->
     <!-- Mobile Menu Button -->
    <div class="menu-btn" id="menuBtn">
        &#9776; 
    </div>
    <aside class="sidebar1" id="sidebar">
        <h3>Project Files</h3>
        <ul class="file-list1">
            <?php if (!empty($files)): ?>
                <?php foreach ($files as $file): ?>
                    <li class="file-item1">
                        <a href="#" class="file-link" data-file-name="<?= urlencode($file['file_name']) ?>" data-file-type="<?= pathinfo($file['file_name'], PATHINFO_EXTENSION) ?>" data-urn="<?= htmlspecialchars($file['urn']) ?>">
                            <img src="<?= getPreviewIcon($file['file_name']) ?>" alt="File Icon" class="preview-icon" />
                            <?= htmlspecialchars($file['file_name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No files found.</li>
            <?php endif; ?>
        </ul>
    </aside>

    <div id="imageContainer"></div>
    <!-- Main content area -->
    <div class="viewer-container">
        <div id="forgeViewer"></div>
        <div id="viewables_dropdown" style="display: none;">
            <select id="viewables"></select>
        </div>        
    </div>    
</div>

<div class="project-model">
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


<!-- Upload Modal -->
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
<!-- <script src="js/share.js"></script> -->
<script>
    // Modal functionality for upload and share modals
    document.getElementById('menuBtn').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
    });
    
</script>
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
