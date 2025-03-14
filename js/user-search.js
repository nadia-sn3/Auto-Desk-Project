document.addEventListener('DOMContentLoaded', function () {
    const usernameInput = document.getElementById('collaborator-username');
    const emailInput = document.getElementById('collaborator-email');
    const usernameSuggestions = document.getElementById('username-suggestions');
    const emailSuggestions = document.getElementById('email-suggestions');

    function fetchSuggestions(searchTerm, type) {
        return fetch(`fetch_users.php?term=${searchTerm}`)
            .then(response => response.json())
            .then(users => {
                return users.filter(user => user[type].toLowerCase().includes(searchTerm.toLowerCase()));
            })
            .catch(error => {
                console.error('Error fetching suggestions:', error);
                return [];
            });
    }

    function displaySuggestions(suggestions, container) {
        container.innerHTML = '';
        suggestions.forEach(suggestion => {
            const div = document.createElement('div');
            div.textContent = suggestion.username || suggestion.email;
            div.addEventListener('click', () => {
                if (suggestion.username) {
                    usernameInput.value = suggestion.username;
                } else {
                    emailInput.value = suggestion.email;
                }
                container.style.display = 'none';
            });
            container.appendChild(div);
        });
        container.style.display = suggestions.length ? 'block' : 'none';
    }

    usernameInput.addEventListener('input', function () {
        const input = usernameInput.value.trim();
        if (input.length > 0) {
            fetchSuggestions(input, 'username').then(suggestions => {
                displaySuggestions(suggestions, usernameSuggestions);
            });
        } else {
            usernameSuggestions.style.display = 'none';
        }
    });

    emailInput.addEventListener('input', function () {
        const input = emailInput.value.trim();
        if (input.length > 0) {
            fetchSuggestions(input, 'email').then(suggestions => {
                displaySuggestions(suggestions, emailSuggestions);
            });
        } else {
            emailSuggestions.style.display = 'none';
        }
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function (event) {
        if (!usernameInput.contains(event.target)) {
            usernameSuggestions.style.display = 'none';
        }
        if (!emailInput.contains(event.target)) {
            emailSuggestions.style.display = 'none';
        }
    });
});