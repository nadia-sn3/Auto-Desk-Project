document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const row = this.closest('tr');
            const userId = row.getAttribute('data-user-id');
            const userName = row.querySelector('td:nth-child(2)').textContent;
            const projectId = new URLSearchParams(window.location.search).get('project_id');
            
            const isConfirmed = confirm(`Are you sure you want to remove ${userName} from this project?`);
            
            if (isConfirmed) {
                this.disabled = true;
                this.textContent = 'Removing...';
                
                removeCollaborator(userId, projectId, row, this);
            }
        });
    });
});

function removeCollaborator(userId, projectId, row, button) {
    fetch('backend/remove_collaborator.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `user_id=${encodeURIComponent(userId)}&project_id=${encodeURIComponent(projectId)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            row.remove();
            
            if (document.querySelectorAll('tbody tr[data-user-id]').length === 0) {
                const tbody = document.querySelector('tbody');
                tbody.innerHTML = '<tr><td colspan="5">No collaborators found for this project.</td></tr>';
            }
            
            showAlert('success', 'Collaborator removed successfully!');
        } else {
            throw new Error(data.message || 'Failed to remove collaborator');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', `Error removing collaborator: ${error.message}`);
        
        if (button) {
            button.disabled = false;
            button.textContent = 'Remove';
        }
    });
}

function showAlert(type, message) {
    const alert = document.createElement('div');
    alert.className = `alert ${type}`;
    alert.textContent = message;
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}