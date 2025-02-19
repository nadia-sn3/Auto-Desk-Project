document.getElementById('filterBeforeBtn').addEventListener('click', function() {
    filterVersions('before');
});

document.getElementById('filterAfterBtn').addEventListener('click', function() {
    filterVersions('after');
});

function filterVersions(filterType) {
    const filterDate = new Date(document.getElementById('filterDate').value);
    const versions = document.querySelectorAll('.timeline-version');

    versions.forEach(version => {
        const versionDate = new Date(version.querySelector('.commit-date').textContent);
        if (filterType === 'before' && versionDate < filterDate) {
            version.style.display = 'block';
        } else if (filterType === 'after' && versionDate > filterDate) {
            version.style.display = 'block';
        } else {
            version.style.display = 'none';
        }
    });
}