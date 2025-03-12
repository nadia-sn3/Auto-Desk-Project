

<link rel="stylesheet" href="style/preview.css">

<div class="model-preview-card">
    <div class="model-preview-thumbnail">
        <img src="images\box.png" alt="Model Thumbnail">
    </div>
    <div class="model-preview-info">
        <h3>Model Name</h3>
        <p>Description of the model.</p>
        <div class="model-preview-actions">
            <a href="view-asset-model.php?urn=<?= urlencode($model['urn']) ?>" class="btn-view">View</a>
            <a href="#" class="btn-download">Download</a>
        </div>
    </div>
</div>