document.addEventListener('DOMContentLoaded', function() {
    const dropdownButtons = document.querySelectorAll('.issues-dropdown-btn');

    dropdownButtons.forEach(button => {
        button.addEventListener('click', function() {
            const dropdownContent = this.nextElementSibling;
            dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
        });
    });

    window.addEventListener('click', function(event) {
        if (!event.target.matches('.issues-dropdown-btn')) {
            const dropdowns = document.querySelectorAll('.issues-dropdown-content');
            dropdowns.forEach(dropdown => {
                dropdown.style.display = 'none';
            });
        }
    });
});