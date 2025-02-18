document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.querySelector(".sidebar");
    const toggleButton = document.createElement("button");

    toggleButton.innerText = "☰";
    toggleButton.classList.add("sidebar-toggle");

    toggleButton.addEventListener("click", function () {
        sidebar.classList.toggle("open");
    });

    function updateToggleButtonVisibility() {
        if (window.innerWidth <= 768) {
            if (!document.body.contains(toggleButton)) {
                document.body.appendChild(toggleButton);
            }
        } else {
            if (document.body.contains(toggleButton)) {
                document.body.removeChild(toggleButton);
            }
        }
    }
    updateToggleButtonVisibility();

    window.addEventListener('resize', updateToggleButtonVisibility);
});
