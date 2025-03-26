document.addEventListener('DOMContentLoaded', function() {
    const adminSearch = document.getElementById('adminSearch');
    if (adminSearch) {
        adminSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#adminTableBody tr');
            
            rows.forEach(row => {
                const name = row.querySelector('.user-name')?.textContent.toLowerCase();
                const email = row.querySelector('.user-email')?.textContent.toLowerCase();
                
                if ((name && name.includes(searchTerm)) || (email && email.includes(searchTerm))) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    const regularSearch = document.getElementById('regularSearch');
    if (regularSearch) {
        regularSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#regularTableBody tr');
            
            rows.forEach(row => {
                const name = row.querySelector('.user-name')?.textContent.toLowerCase();
                const email = row.querySelector('.user-email')?.textContent.toLowerCase();
                
                if ((name && name.includes(searchTerm)) || (email && email.includes(searchTerm))) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});