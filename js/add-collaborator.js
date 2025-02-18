document.addEventListener("DOMContentLoaded", () => {
    const addCollaboratorBtn = document.getElementById("add-collaborator-btn");
    const modal = document.getElementById("add-collaborator-modal");
    const closeBtn = document.querySelector(".close-btn");
    const form = document.getElementById("add-collaborator-form");

    addCollaboratorBtn.addEventListener("click", () => {
        modal.style.display = "block";
    });

    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });

    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    form.addEventListener("submit", (event) => {
        event.preventDefault();
        const username = document.getElementById("username").value;
        const email = document.getElementById("email").value;

        console.log(`Inviting collaborator: Username - ${username}, Email - ${email}`);

        modal.style.display = "none";
    });
});
