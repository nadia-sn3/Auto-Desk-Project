document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('collaboratorModal');
    const openModalBtn = document.getElementById('add-collaborator-btn');
    const closeModalBtn = modal.querySelector('.close-btn');
    const form = document.getElementById('collaborator-form');
    
    // Open modal
    openModalBtn.addEventListener('click', function () {
        modal.style.display = 'flex';  
    });
    
    // Close modal
    closeModalBtn.addEventListener('click', function () {
        modal.style.display = 'none';  
    });

    // Close when clicking outside
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';  
        }
    });

    // Form validation
    const submitBtn = form.querySelector('.submit-btn');
    const collaboratorUsername = form.querySelector('#collaborator-username');
    const collaboratorEmail = form.querySelector('#collaborator-email');
    const collaboratorRole = form.querySelector('#collaborator-role');
    
    function checkFormValidity() {
        const usernameFilled = collaboratorUsername.value.trim() !== '';
        const emailFilled = collaboratorEmail.value.trim() !== '';
        const roleSelected = collaboratorRole.value.trim() !== '';

        // Enable if either username or email is filled and role is selected
        if ((usernameFilled || emailFilled) && roleSelected) {
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

    // Form submission with AJAX
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Adding...';
        
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                return response.json();
            }
        })
        .then(data => {
            if (data && data.error) {
                alert(data.error);
            }
            submitBtn.disabled = false;
            submitBtn.textContent = 'Invite';
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.disabled = false;
            submitBtn.textContent = 'Invite';
        });
    });
});
document.getElementById("collaborator-form").addEventListener("submit", function(event) {
    event.preventDefault(); // Prevent default form submission
    
    var formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    }).then(response => response.json())
      .then(data => {
          if (data.status === 'success') {
              // If the collaborator is added successfully, reload the page
              window.location.href = '/collaborators.php?project_id=' + data.project_id + '&success=1';
          } else {
              // Show error message if any
              alert('Error: ' + data.message);
          }
      })
      .catch(error => {
          alert('Request failed: ' + error.message);
      });
});
