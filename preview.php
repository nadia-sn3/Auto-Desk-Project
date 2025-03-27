<link rel="stylesheet" href="style/preview.css">

<div class="model-preview-card">
    <div class="model-preview-thumbnail">
        <img src="https://via.placeholder.com/400x200" alt="Model Thumbnail">
    </div>
    <div class="model-preview-info">
        <h3><?= htmlspecialchars($project_name) ?></h3>
        <p><?= htmlspecialchars($description) ?></p>
        <div class="model-preview-actions">
            <a href="view-project.php?project_id=<?= $project_id ?>" class="btn-view">View</a>
            <a href="#" class="btn-download">Download</a>
        </div>
    </div>
</div>