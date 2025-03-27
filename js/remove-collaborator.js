document.addEventListener('DOMContentLoaded', function() {
    // Get the project ID from the hidden input in the form
    const projectId = document.querySelector('input[name="project_id"]').value;
    
    // Add event listeners to all remove buttons
    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const row = this.closest('tr');
            const userId = row.dataset.userId;

            if (confirm('Are you sure you want to remove this collaborator?')) {
                removeCollaborator(projectId, userId, row);
            }
        });
    });
});

function removeCollaborator(projectId, userId, rowElement) {
    const formData = new FormData();
    formData.append('project_id', projectId);
    formData.append('user_id', userId);
    
    fetch('backend/remove_collaborator.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            // Remove the row from the table
            rowElement.remove();
            
            // Show success message
            alert(data.message);
            
            // If no collaborators left, show empty message
            const tbody = document.querySelector('tbody');
            if (tbody.querySelectorAll('tr').length === 0) {
                tbody.innerHTML = '<tr><td colspan="5">No collaborators found for this project.</td></tr>';
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Request failed: ' + error.message);
    });
}