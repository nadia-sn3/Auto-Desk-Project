<link rel="stylesheet" href="style/preview.css">

<div class="preview-projects">
    <?php 
    foreach ($projects as $project) {
        $projectId = $project['project_id'];
        $projectName = $project['project_name'];
        $projectDescription = $project['description'];
        $thumbnailPath = !empty($project['thumbnail_path']) ? $project['thumbnail_path'] : "default-thumbnail.png"; 

        $hasFiles = false;
        $fileCheckQuery = "SELECT COUNT(*) as file_count FROM Project_File WHERE project_id = ?";
        $stmt = $conn->prepare($fileCheckQuery);
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $hasFiles = $row['file_count'] > 0;
        }
        $stmt->close();

        $viewLink = $hasFiles ? "file-list.php?project_id=" . $projectId : "view-project.php?project_id=" . $projectId;
        ?>
        <div class="model-preview-card">
            <div class="model-preview-thumbnail">
                <img src="<?php echo htmlspecialchars($thumbnailPath); ?>" alt="Model Thumbnail">
            </div>
            <div class="model-preview-info">
                <h3><?php echo htmlspecialchars($projectName); ?></h3>
                <p><?php echo htmlspecialchars($projectDescription); ?></p>
                <div class="model-preview-actions">
                    <a href="<?php echo $viewLink; ?>" class="btn-view">View</a>
                    <a href="#" class="btn-download">Download</a>
                </div>
            </div>
        </div>
        <?php 
    }
    ?>
</div>