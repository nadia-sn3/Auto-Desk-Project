function toggleInfo(button) {
    const versionContainer = button.closest('.timeline-version');
    const extraInfo = versionContainer.nextElementSibling; 
    
    if (!extraInfo || !extraInfo.classList.contains('issues-dropdown-content')) {
        return;
    }

    if (extraInfo.style.display === 'none' || extraInfo.style.display === '') {
        document.querySelectorAll('.issues-dropdown-content').forEach(dropdown => {
            if (dropdown !== extraInfo) {
                dropdown.style.display = 'none';
                const allButtons = document.querySelectorAll('.toggle-info-btn');
                allButtons.forEach(btn => {
                    if (btn !== button) {
                        btn.textContent = '▶';
                    }
                });
            }
        });
        
        extraInfo.style.display = 'block';
        button.textContent = '▼';
    } else {
        extraInfo.style.display = 'none';
        button.textContent = '▶';
    }
}