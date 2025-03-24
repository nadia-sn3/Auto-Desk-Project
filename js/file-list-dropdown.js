document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.querySelector('.file-dropdown');
    const dropdownHeader = dropdown.querySelector('.dropdown-header');
    
    dropdownHeader.addEventListener('click', () => {
        dropdown.classList.toggle('active');
    });
    
    document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });
    
    const fileItems = document.querySelectorAll('.file-item');
    fileItems.forEach(item => {
        item.addEventListener('click', function() {
            fileItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
