<link rel="stylesheet" href="style/version+issues.css">


<div class="container">
    
    <div class="report-filters">
        <button class="btn active" data-status="all">All Issues</button>
        <button class="btn" data-status="open">Open</button>
        <button class="btn" data-status="in_progress">In Progress</button>
        <button class="btn" data-status="resolved">Resolved</button>
    </div>
    
    <div class="versions-list">
        <div class="project-model-timeline-versions">
            <div class="timeline-version">
                <div class="version-warning-indicator" data-issues="version1">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ff6b6b" width="18px" height="18px">
                        <path d="M12 2L1 21h22L12 2zm0 3.5L18.5 19h-13L12 5.5z"/>
                        <path d="M12 16c.8 0 1.5-.7 1.5-1.5S12.8 13 12 13s-1.5.7-1.5 1.5.7 1.5 1.5 1.5zm-1-5h2v-4h-2v4z"/>
                    </svg>
                </div>
                <span class="commit-message">Changes to models</span>
                <span class="commit-info">
                    <span class="username">User</span>
                    <span class="commit-date">V.03</span>

                    <span class="commit-date">yesterday</span>
                </span>
                <button class="raise-issue-btn" data-version="v1">Raise Issue</button>
            </div>
            
            <div class="issues-dropdown-content" id="version1">
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
                        <tr class="open">
                            <td>models/main.obj</td>
                            <td>Texture missing on the main model</td>
                            <td><span class="status-badge pending">Open</span></td>
                            <td>user1</td>
                            <td>2023-06-15</td>
                            <td class="actions">
                                <button class="btn small change-status" data-issue="i1" data-status="in_progress">Mark In Progress</button>
                            </td>
                        </tr>
                        <tr class="in_progress">
                            <td>models/secondary.fbx</td>
                            <td>Geometry errors in secondary model</td>
                            <td><span class="status-badge in-progress">In Progress</span></td>
                            <td>user2</td>
                            <td>2023-06-14</td>
                            <td class="actions">
                                <button class="btn small change-status" data-issue="i2" data-status="resolved">Mark Resolved</button>
                            </td>
                        </tr>
                        <tr class="resolved">
                            <td>textures/main.png</td>
                            <td>Texture resolution too low</td>
                            <td><span class="status-badge resolved">Resolved</span></td>
                            <td>user3</td>
                            <td>2023-06-10</td>
                            <td class="actions">
                                <button class="btn small change-status" data-issue="i3" data-status="open">Reopen</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="project-model-timeline-versions">
            <div class="timeline-version">
                <div class="version-warning-indicator" data-issues="version2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ff6b6b" width="18px" height="18px">
                        <path d="M12 2L1 21h22L12 2zm0 3.5L18.5 19h-13L12 5.5z"/>
                        <path d="M12 16c.8 0 1.5-.7 1.5-1.5S12.8 13 12 13s-1.5.7-1.5 1.5.7 1.5 1.5 1.5zm-1-5h2v-4h-2v4z"/>
                    </svg>
                </div>
                <span class="commit-message">Small Change</span>
                <span class="commit-info">
                    <span class="username">test3</span>
                    <span class="commit-date">V.02</span>

                    <span class="commit-date">2 days ago</span>
                </span>
                <button class="rollback-btn">Rollback</button>
                <button class="raise-issue-btn" data-version="v2">Raise Issue</button>
            </div>
            
            <div class="issues-dropdown-content" id="version2">
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
                        <tr class="open">
                            <td>scripts/main.js</td>
                            <td>Script errors on load</td>
                            <td><span class="status-badge pending">Open</span></td>
                            <td>user4</td>
                            <td>2023-06-16</td>
                            <td class="actions">
                                <button class="btn small change-status" data-issue="i4" data-status="in_progress">Mark In Progress</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="project-model-timeline-versions">
            <div class="timeline-version">
                <div class="version-no-issues">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#4CAF50" width="18px" height="18px">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <span class="commit-message">First Commit</span>
                <span class="commit-info">
                    <span class="username">test2</span>
                    <span class="commit-date">V.01</span>

                    <span class="commit-date">3 days ago</span>
                </span>
                <button class="rollback-btn">Rollback</button>
                <button class="raise-issue-btn" data-version="v3">Raise Issue</button>
            </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle issues dropdown
    document.querySelectorAll('.version-warning-indicator').forEach(indicator => {
        indicator.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdownId = this.getAttribute('data-issues');
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
        });
    });
    
    document.addEventListener('click', function() {
        document.querySelectorAll('.issues-dropdown-content').forEach(d => {
            d.style.display = 'none';
        });
    });
    
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
    
    document.querySelectorAll('.change-status').forEach(btn => {
        btn.addEventListener('click', function() {
            const issueId = this.getAttribute('data-issue');
            const newStatus = this.getAttribute('data-status');
            
            console.log(`Changing issue ${issueId} to status ${newStatus}`);
            
            const row = this.closest('tr');
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
                this.textContent = 'Mark In Progress';
                this.setAttribute('data-status', 'in_progress');
            } else if (newStatus === 'in_progress') {
                this.textContent = 'Mark Resolved';
                this.setAttribute('data-status', 'resolved');
            } else if (newStatus === 'resolved') {
                this.textContent = 'Reopen';
                this.setAttribute('data-status', 'open');
            }
        });
    });
    
    const modal = document.getElementById("issueModal");
    const raiseIssueBtns = document.querySelectorAll(".raise-issue-btn");
    const closeBtn = document.querySelector(".close");
    const issueForm = document.getElementById("issueForm");
    const versionIdInput = document.getElementById("versionId");
    
    raiseIssueBtns.forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.stopPropagation();
            versionIdInput.value = this.getAttribute('data-version');
            modal.style.display = "block";
        });
    });
    
    closeBtn.addEventListener("click", function() {
        modal.style.display = "none";
    });
    
    window.addEventListener("click", function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    });
    
    issueForm.addEventListener("submit", function(e) {
        e.preventDefault();
        const versionId = versionIdInput.value;
        const file = document.getElementById("issueFile").value;
        const description = document.getElementById("issueDescription").value;
        
        console.log(`New issue for version ${versionId}: ${file} - ${description}`);
        
        alert("Issue submitted successfully!");
        modal.style.display = "none";
        issueForm.reset();
    });
});
</script>

<style>

</style>