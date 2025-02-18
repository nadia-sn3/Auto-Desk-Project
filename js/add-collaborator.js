document.addEventListener("DOMContentLoaded", () => {
    const addCollaboratorBtn = document.getElementById("add-collaborator-btn");
    const modal = document.getElementById("add-collaborator-modal");
    const closeBtn = document.querySelector(".close-btn");
    const form = document.getElementById("add-collaborator-form");

    // Show modal when "Add Collaborator" button is clicked
    addCollaboratorBtn.addEventListener("click", () => {
        modal.style.display = "flex"; // Change to 'flex' to center the modal properly
    });

    // Hide modal when the close button is clicked
    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });

    // Close the modal if the user clicks outside the modal content
    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    // Handle form submission (for demonstration purposes, logs data to the console)
    form.addEventListener("submit", (event) => {
        event.preventDefault(); // Prevent the form from submitting
        const username = document.getElementById("username").value;
        const email = document.getElementById("email").value;

        console.log(`Inviting collaborator: Username - ${username}, Email - ${email}`);

        // Close the modal after submitting the form
        modal.style.display = "none";
    });
});
