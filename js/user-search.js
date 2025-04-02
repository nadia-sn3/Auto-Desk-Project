document.addEventListener('DOMContentLoaded', function() {
    const usernameInput = document.getElementById('collaborator-username');
    const usernameSuggestions = document.getElementById('username-suggestions');
    const emailInput = document.getElementById('collaborator-email');
    const emailSuggestions = document.getElementById('email-suggestions');

    // Search users by username
    usernameInput.addEventListener('input', function() {
        const query = this.value.trim();
        if (query.length < 2) {
            usernameSuggestions.innerHTML = '';
            return;
        }

        fetch(`/backend/search_users.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(users => {
                usernameSuggestions.innerHTML = '';
                users.forEach(user => {
                    const div = document.createElement('div');
                    div.textContent = `${user.first_name} ${user.last_name} (${user.email})`;
                    div.addEventListener('click', function() {
                        usernameInput.value = user.email;
                        usernameSuggestions.innerHTML = '';
                    });
                    usernameSuggestions.appendChild(div);
                });
            })
            .catch(error => console.error('Error:', error));
    });

    // Search users by email
    emailInput.addEventListener('input', function() {
        const query = this.value.trim();
        if (query.length < 2) {
            emailSuggestions.innerHTML = '';
            return;
        }

        fetch(`/backend/search_users.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(users => {
                emailSuggestions.innerHTML = '';
                users.forEach(user => {
                    const div = document.createElement('div');
                    div.textContent = `${user.first_name} ${user.last_name} (${user.email})`;
                    div.addEventListener('click', function() {
                        emailInput.value = user.email;
                        emailSuggestions.innerHTML = '';
                    });
                    emailSuggestions.appendChild(div);
                });
            })
            .catch(error => console.error('Error:', error));
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target !== usernameInput) {
            usernameSuggestions.innerHTML = '';
        }
        if (e.target !== emailInput) {
            emailSuggestions.innerHTML = '';
        }
    });
});