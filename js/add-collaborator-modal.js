document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('collaboratorModal');
    const openModalBtn = document.getElementById('add-collaborator-btn');
    const closeModalBtn = modal.querySelector('.close-btn');
    const form = document.getElementById('collaborator-form');
    
    function closeModalAndRefresh() {
        modal.style.display = 'none';
        window.location.reload();
    }
    
    openModalBtn.addEventListener('click', function() {
        modal.style.display = 'flex';
    });
    
    closeModalBtn.addEventListener('click', closeModalAndRefresh);

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModalAndRefresh();
        }
    });

    const submitBtn = form.querySelector('.submit-btn');
    const collaboratorEmail = form.querySelector('#collaborator-email');
    const collaboratorRole = form.querySelector('#collaborator-role');
    
    function checkFormValidity() {
        const emailFilled = collaboratorEmail.value.trim() !== '';
        const roleSelected = collaboratorRole.value.trim() !== '';

        if (emailFilled && roleSelected) {
            submitBtn.classList.add('enabled');
            submitBtn.disabled = false;
        } else {
            submitBtn.classList.remove('enabled');
            submitBtn.disabled = true;
        }
    }

    collaboratorEmail.addEventListener('input', checkFormValidity);
    collaboratorRole.addEventListener('change', checkFormValidity);

    checkFormValidity();

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            closeModalAndRefresh();
            return response.json(); 
        })
        .catch(error => {
            closeModalAndRefresh();
        });
    });
});