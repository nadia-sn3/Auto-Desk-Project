document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('rollbackModal');
    const closeBtn = modal.querySelector('.close');
    const rollbackForm = document.getElementById('rollbackForm');
    let currentVersion;

    function openModal(version) {
        currentVersion = version;
        modal.style.display = 'block';
    }

    function closeModal() {
        modal.style.display = 'none';
        currentVersion = null;
    }

    document.querySelectorAll('.rollback-btn').forEach(button => {
        button.addEventListener('click', function () {
            const version = this.closest('.timeline-version');
            openModal(version);
        });
    });

    closeBtn.addEventListener('click', closeModal);

    rollbackForm.addEventListener('submit', function (e) {
        e.preventDefault(); 
        const comment = document.getElementById('rollbackComment').value;
        console.log('Rolling back to version:', currentVersion);
        console.log('Rollback comment:', comment);

        closeModal();

        rollbackForm.reset();
    });

    window.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeModal();
        }
    });
});