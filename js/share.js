document.addEventListener("DOMContentLoaded", () => {
    const shareBtn = document.querySelector(".project-model-buttons .btn:first-child");
    const shareModal = document.getElementById("shareModal");
    const closeShareBtn = document.querySelector("#shareModal .close-btn");
    const shareForm = document.getElementById("share-form");

    shareBtn.addEventListener("click", () => {
        shareModal.style.display = "flex";
    });

    closeShareBtn.addEventListener("click", () => {
        shareModal.style.display = "none";
    });

    window.addEventListener("click", (event) => {
        if (event.target === shareModal) {
            shareModal.style.display = "none";
        }
    });

    shareForm.addEventListener("submit", (event) => {
        event.preventDefault();
        const username = document.getElementById("share-username").value;
        const email = document.getElementById("share-email").value;
        const role = document.getElementById("share-role").value;
        const duration = document.getElementById("share-duration").value;

        console.log(`Sharing project: Username - ${username}, Email - ${email}, Role - ${role}, Duration - ${duration} days`);

        shareModal.style.display = "none";
    });
});