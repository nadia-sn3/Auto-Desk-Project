document.addEventListener('DOMContentLoaded', function () {
    const usernameInput = document.getElementById('collaborator-username');
    const emailInput = document.getElementById('collaborator-email');
    const usernameSuggestions = document.getElementById('username-suggestions');
    const emailSuggestions = document.getElementById('email-suggestions');

    const users = [
        { username: 'alice', email: 'alice@example.com' },
        { username: 'bob', email: 'bob@example.com' },
        { username: 'charlie', email: 'charlie@example.com' },
    ];

    function filterSuggestions(input, type) {
        return users.filter(user => user[type].toLowerCase().startsWith(input.toLowerCase()));
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
        const suggestions = filterSuggestions(input, 'username');
        displaySuggestions(suggestions, usernameSuggestions);
    });

    emailInput.addEventListener('input', function () {
        const input = emailInput.value.trim();
        const suggestions = filterSuggestions(input, 'email');
        displaySuggestions(suggestions, emailSuggestions);
    });

    document.addEventListener('click', function (event) {
        if (!usernameInput.contains(event.target)) {
            usernameSuggestions.style.display = 'none';
        }
        if (!emailInput.contains(event.target)) {
            emailSuggestions.style.display = 'none';
        }
    });
});