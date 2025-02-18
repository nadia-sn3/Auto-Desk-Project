function toggleInfo(button) {
    const versionContainer = button.closest('.timeline-version-');
    const extraInfo = versionContainer.querySelector('.extra-info');
    const arrow = button;  
    
    if (extraInfo.style.display === 'none' || extraInfo.style.display === '') {
        extraInfo.style.display = 'block';
        arrow.textContent = '▼';           
    } else {
        extraInfo.style.display = 'none';  
        arrow.textContent = '▶';           
    }
}
