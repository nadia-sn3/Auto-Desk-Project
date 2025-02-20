document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('rollbackModal');
    const closeBtn = modal.querySelector('.close');
    const rollbackForm = document.getElementById('rollbackForm');
    const rollbackComment = document.getElementById('rollbackComment');
    const submitButton = rollbackForm.querySelector('.submit-btn');
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
        const comment = rollbackComment.value;
        console.log('Rolling back to version:', currentVersion);
        console.log('Rollback comment:', comment);

        closeModal();
        rollbackForm.reset();
        submitButton.disabled = true;
        submitButton.classList.remove('active');
    });

    window.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    rollbackComment.addEventListener('input', function () {
        if (rollbackComment.value.trim() !== "") {
            submitButton.disabled = false;
            submitButton.classList.add('active'); 
            submitButton.disabled = true;
            submitButton.classList.remove('active'); 
        }
    });
});
