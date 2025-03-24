document.addEventListener('DOMContentLoaded', function() {

    const modal = document.getElementById('addMemberModal');
    const openModalBtn = document.querySelector('.org-actions .btn-primary');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    const addMemberForm = document.getElementById('addMemberForm');
    
    if (openModalBtn) {
        openModalBtn.addEventListener('click', function() {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    }
    
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            closeModal();
        });
    });
    
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
    

    if (addMemberForm) {
        addMemberForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const firstName = document.getElementById('first_name').value.trim();
            const lastName = document.getElementById('last_name').value.trim();
            const email = document.getElementById('email').value.trim();
            
            if (!firstName || !lastName || !email) {
                showAlert('Please fill in all fields', 'error');
                return;
            }
            
            if (!validateEmail(email)) {
                showAlert('Please enter a valid email address', 'error');
                return;
            }
            
            // Submit the form
            this.submit();
        });
    }
    
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => alert.remove());
        
        if (addMemberForm) {
            addMemberForm.reset();
        }
    }
    
    function showAlert(message, type) {

        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        
        const modalBody = document.querySelector('.modal-body');
        modalBody.insertBefore(alertDiv, modalBody.firstChild);
    }
    
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
});