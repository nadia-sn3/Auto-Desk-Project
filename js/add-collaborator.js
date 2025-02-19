document.addEventListener("DOMContentLoaded", () => {
    const addCollaboratorBtn = document.getElementById("add-collaborator-btn");
    const modal = document.getElementById("add-collaborator-modal");
    const closeBtn = document.querySelector(".close-btn");
    const form = document.getElementById("add-collaborator-form");
    const roleSelect = document.getElementById("role");
    const accessDurationGroup = document.getElementById("access-duration-group");
    const fileAccessGroup = document.getElementById("file-access-group");

    addCollaboratorBtn.addEventListener("click", () => {
        modal.style.display = "flex"; 
    });

    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });

    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    roleSelect.addEventListener("change", (event) => {
        if (event.target.value === "viewer" || event.target.value === "contractor") {
            accessDurationGroup.style.display = "block";
            fileAccessGroup.style.display = "block";
        } else {
            accessDurationGroup.style.display = "none";
            fileAccessGroup.style.display = "none";
        }
    });

    form.addEventListener("submit", (event) => {
        event.preventDefault(); 
        const username = document.getElementById("username").value;
        const email = document.getElementById("email").value;
        const role = document.getElementById("role").value;
        const accessDuration = document.getElementById("access-duration").value;
        const fileAccess = document.getElementById("file-access").value;

        console.log(`Inviting collaborator: Username - ${username}, Email - ${email}, Role - ${role}, Access Duration - ${accessDuration}, File Access - ${fileAccess}`);

        modal.style.display = "none";
    });
});