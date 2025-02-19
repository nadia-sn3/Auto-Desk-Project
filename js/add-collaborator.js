document.addEventListener("DOMContentLoaded", () => {
    const addCollaboratorBtn = document.getElementById("add-collaborator-btn");
    const modal = document.getElementById("collaboratorModal"); 
    const closeBtn = modal.querySelector(".close-btn"); 
    const form = document.getElementById("collaborator-form"); 
    const roleSelect = document.getElementById("collaborator-role");
    const accessDurationGroup = document.getElementById("collaborator-access-duration-group"); 
    const fileAccessGroup = document.getElementById("collaborator-file-access-group"); 

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
        const username = document.getElementById("collaborator-username").value;
        const email = document.getElementById("collaborator-email").value;
        const role = document.getElementById("collaborator-role").value;
        const accessDuration = document.getElementById("collaborator-access-duration").value; 
        const fileAccess = document.getElementById("collaborator-file-access").value; 

        console.log(`Inviting collaborator: Username - ${username}, Email - ${email}, Role - ${role}, Access Duration - ${accessDuration}, File Access - ${fileAccess}`);

        modal.style.display = "none"; 
    });
});