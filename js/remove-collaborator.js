document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".remove-btn").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.getAttribute("data-user-id");
            const projectId = new URLSearchParams(window.location.search).get("project_id");

            if (!confirm("Are you sure you want to remove this user from the project?")) return;

            fetch("backend/remove_collaborator.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `project_id=${projectId}&user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("User removed successfully!");
                    location.reload();
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(error => alert("Request failed!"));
        });
    });
});
