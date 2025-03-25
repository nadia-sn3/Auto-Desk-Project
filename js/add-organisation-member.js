document.addEventListener('DOMContentLoaded', function() {

    const modal = document.getElementById('addMemberModal');
    const openModalButtons = document.querySelectorAll('[data-target="addMemberModal"]');
    const closeModalButtons = document.querySelectorAll('.close-modal');
    
    function toggleModal() {
        modal.classList.toggle('show');
        document.body.classList.toggle('modal-open');
    }
    
    openModalButtons.forEach(button => {
        button.addEventListener('click', toggleModal);
    });
    
    closeModalButtons.forEach(button => {
        button.addEventListener('click', toggleModal);
    });
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            toggleModal();
        }
    });
    
    document.getElementById('set_custom_password').addEventListener('change', function() {
        const passwordField = document.querySelector('.password-field');
        passwordField.style.display = this.checked ? 'block' : 'none';
        if (!this.checked) {
            document.getElementById('password').value = '';
        }
    });
    
    document.getElementById('addMemberForm').addEventListener('submit', function(e) {
        const passwordField = document.getElementById('password');
        const customPasswordChecked = document.getElementById('set_custom_password').checked;
        
        if (customPasswordChecked && passwordField.value.length > 0 && passwordField.value.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters');
            passwordField.focus();
        }
    });
});