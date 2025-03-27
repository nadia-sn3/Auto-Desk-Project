<link rel="stylesheet" href="style/preview.css">

<div class="preview-projects">
    <?php 
    foreach ($projects as $project) {
        $projectId = $project['project_id'];
        $projectName = $project['project_name'];
        $projectDescription = $project['description'];
        $thumbnailPath = !empty($project['thumbnail_path']) ? $project['thumbnail_path'] : "default-thumbnail.png"; // Use default if empty

        ?>
        <div class="model-preview-card">
            <div class="model-preview-thumbnail">
                <img src="<?php echo htmlspecialchars($thumbnailPath); ?>" alt="Model Thumbnail">
            </div>
            <div class="model-preview-info">
                <h3><?php echo htmlspecialchars($projectName); ?></h3>
                <p><?php echo htmlspecialchars($projectDescription); ?></p>
                <div class="model-preview-actions">
                    <a href="file-list.php?project_id=<?php echo $projectId ?>" class="btn-view">View</a>
                    <a href="#" class="btn-download">Download</a>
                </div>
            </div>
        </div>
        <?php 
    }
    ?>
</div>
