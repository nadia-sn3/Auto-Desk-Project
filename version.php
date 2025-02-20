<div class="project-model-timeline-versions">
    <div class="timeline-version">
        <span class="commit-message">Changes to models</span>
        <span class="commit-info">
            <span class="username">User</span>
            <span class="commit-date">yesterday</span>
        </span>
        <button class="rollback-btn">Rollback</button>
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
<script src="js/rollback.js"></script>

<link rel="stylesheet" href="style/version.css">