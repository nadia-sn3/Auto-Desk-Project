document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('collaboratorModal');
    const openModalBtn = document.getElementById('add-collaborator-btn');
    const closeModalBtn = modal.querySelector('.close-btn');
    
    openModalBtn.addEventListener('click', function () {
        modal.style.display = 'flex';  
    });
    
    closeModalBtn.addEventListener('click', function () {
        modal.style.display = 'none';  
    });

    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';  
        }
    });

    const collaboratorForm = document.getElementById('collaborator-form');
    const submitBtn = collaboratorForm.querySelector('.submit-btn');
    const collaboratorUsername = collaboratorForm.querySelector('#collaborator-username');
    const collaboratorEmail = collaboratorForm.querySelector('#collaborator-email');
    const collaboratorRole = collaboratorForm.querySelector('#collaborator-role');
    
    function checkFormValidity() {
        const usernameFilled = collaboratorUsername.value.trim() !== '';
        const emailFilled = collaboratorEmail.value.trim() !== '';
        const roleSelected = collaboratorRole.value.trim() !== '';

        if (usernameFilled && emailFilled && roleSelected) {
            submitBtn.classList.add('enabled'); 
            submitBtn.disabled = false;  
        } else {
            submitBtn.classList.remove('enabled');  
            submitBtn.disabled = true;  
        }
    }

    collaboratorUsername.addEventListener('input', checkFormValidity);
    collaboratorEmail.addEventListener('input', checkFormValidity);
    collaboratorRole.addEventListener('change', checkFormValidity);

    checkFormValidity();

    collaboratorForm.addEventListener('submit', function (e) {
        e.preventDefault();
        console.log('Collaborator added!');
    });
});
