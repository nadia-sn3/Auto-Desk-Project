document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addMemberModal');
    const openModalBtn = document.querySelector('.org-actions .btn-primary');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    const addMemberForm = document.getElementById('addMemberForm');
    const setPasswordCheckbox = document.getElementById('set_custom_password');
    const passwordField = document.querySelector('.password-field');
    
    // Toggle password field visibility
    if (setPasswordCheckbox && passwordField) {
        setPasswordCheckbox.addEventListener('change', function() {
            passwordField.style.display = this.checked ? 'block' : 'none';
        });
    }
    
    // Open modal
    if (openModalBtn && modal) {
        openModalBtn.addEventListener('click', function() {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    }
    
    // Close modal
    if (closeModalBtns) {
        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                closeModal();
            });
        });
    }
    
    // Close when clicking outside modal
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
    
    // Form validation
    if (addMemberForm) {
        addMemberForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const firstName = document.getElementById('first_name').value.trim();
            const lastName = document.getElementById('last_name').value.trim();
            const email = document.getElementById('email').value.trim();
            
            // Clear previous alerts
            const existingAlerts = document.querySelectorAll('.alert');
            existingAlerts.forEach(alert => {
                if (!alert.classList.contains('alert-success') && !alert.classList.contains('alert-error')) {
                    alert.remove();
                }
            });
            
            // Validate fields
            if (!firstName || !lastName || !email) {
                showAlert('Please fill in all fields', 'error');
                return;
            }
            
            if (!validateEmail(email)) {
                showAlert('Please enter a valid email address', 'error');
                return;
            }
            
            // Submit the form if valid
            this.submit();
        });
    }
    
    function closeModal() {
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Clear form and temporary alerts
            if (addMemberForm) {
                addMemberForm.reset();
            }
            
            // Hide password field if shown
            if (setPasswordCheckbox && passwordField) {
                setPasswordCheckbox.checked = false;
                passwordField.style.display = 'none';
            }
            
            // Remove any temporary alerts (keep success/error messages)
            const alerts = document.querySelectorAll('.alert:not(.alert-success):not(.alert-error)');
            alerts.forEach(alert => alert.remove());
        }
    }
    
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        
        const modalBody = document.querySelector('.modal-body');
        if (modalBody) {
            // Insert after any existing alerts
            const existingAlerts = modalBody.querySelectorAll('.alert');
            if (existingAlerts.length > 0) {
                existingAlerts[existingAlerts.length - 1].insertAdjacentElement('afterend', alertDiv);
            } else {
                modalBody.insertBefore(alertDiv, modalBody.firstChild);
            }
        }
    }
    
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
});